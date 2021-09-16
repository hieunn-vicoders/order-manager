<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Api\Admin;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use VCComponent\Laravel\Mail\Entities\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use VCComponent\Laravel\Export\Services\Export\Export;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Entities\OrderMail;
use VCComponent\Laravel\Order\Entities\OrderProductAttribute;
use VCComponent\Laravel\Order\Events\AddAttributesEvent;
use VCComponent\Laravel\Order\Mail\MailNotify;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\Order\Transformers\OrderTransformer;
use VCComponent\Laravel\Order\Validators\OrderValidator;
use VCComponent\Laravel\Product\Entities\Product;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;
use VCComponent\Laravel\Vicoders\Core\Exceptions\PermissionDeniedException;

class OrderController extends ApiController
{
    protected $repositoryOrder;
    protected $validatorOrder;

    public function __construct(OrderRepository $repositoryOrder, OrderValidator $validatorOrder)
    {
        $this->repositoryOrder = $repositoryOrder;
        $this->entity          = $repositoryOrder->getEntity();
        $this->validatorOrder  = $validatorOrder;
        $this->transformer     = OrderTransformer::class;

        if (!empty(config('order.auth_middleware.admin'))) {
            $user = $this->getAuthenticatedUser();
            if (Gate::forUser($user)->denies('manage', $this->entity)) {
                throw new PermissionDeniedException();
            }
        }
    }

    public function export(Request $request)
    {
        $this->validatorOrder->isValid($request, 'RULE_EXPORT');

        $data   = $request->all();
        $orders = $this->getReportOrders($request);

        $args = [
            'data'      => $orders,
            'label'     => $request->label ? $data['label'] : 'Orders',
            'extension' => $request->extension ? $data['extension'] : 'Xlsx',
        ];

        $export = new Export($args);
        $url    = $export->export();

        if (config('order.test_mode')) {
            return $this->response->array(['data' => $orders]);
        } else{
             return $this->response->array(['url' => $url]);
        }

    }

    private function getReportOrders(Request $request)
    {
        $fields = [
            'orders.phone_number as `Số điện thoại`',
            'orders.email as `Email`',
            'orders.username as `Tên khách hàng`',
            'orders.address as `Địa chỉ chi tiết`',
            'orders.province as `Thành phố`',
            'orders.district as `Quận/Huyện`',
            'orders.address as `Địa chỉ chi tiết`',
            'orders.total as `Tổng giá trị đơn hàng`',
            'orders.order_note as `Ghi chú`',
            'users.username as `Người tạo`',

        ];
        $fields = implode(', ', $fields);

        $query = $this->entity;
        $query = $query->select(DB::raw($fields));
        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['status'], $request);
        if ($request->has('status')) {
            $request->validate([
                'status' => 'required|regex:/^(\d+\,?)*$/',
            ]);
            $status = explode(',', $request->get('status'));
            $query  = $query->whereIn('status_id', $status);
        }

        $query = $query->leftJoin('users', function ($join) {
            $join->on('orders.user_id', '=', 'users.id');
        });

        $products = $query->get()->toArray();

        return $products;
    }

    public function index(Request $request)
    {
        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['phone_number', 'username', 'email', 'address'], $request, ['products' => ['name']]);
        $query = $this->applyOrderByFromRequest($query, $request);

        if ($request->has('status')) {

            $request->validate([
                'status' => 'required|regex:/^(\d+\,?)*$/',
            ]);

            $status = explode(',', $request->get('status'));
            $query  = $query->whereIn('status_id', $status);
        }

        if ($request->has('payment_status')) {

            $request->validate([
                'payment_status' => 'required|regex:/^(\d+\,?)*$/',
            ]);

            $payment_status = explode(',', $request->get('payment_status'));
            $query          = $query->whereIn('payment_status', $payment_status);
        }

        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $order    = $query->paginate($per_page);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->paginator($order, $transformer);
    }

    public function show($id, Request $request)
    {
        $order = $this->repositoryOrder->findById($id);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($order, $transformer);
    }

    public function store(Request $request)
    {
        $this->validatorOrder->isValid($request, 'RULE_CREATE');

        $data = $request->all();

        if ($request->has('order_items')) {
            unset($data['order_items']);
        }

        if ($request->has('includes')) {
            unset($data['includes']);
        }

        $order = $this->repositoryOrder->findWhere($data)->first();

        if ($order) {
            throw new \Exception("Order này đã tồn tại");
        }

        if ($request->has('order_items')) {

            $product_ids = collect($request->get('order_items'))->pluck('product_id');
            $products    = Product::whereIn('id', $product_ids)->get();

            $product_exists = array_values(array_diff($product_ids->toArray(), $products->pluck('id')->toArray()));

            if ($product_exists !== []) {
                throw new NotFoundException("Sản phẩm có id = {$product_exists[0]} không tồn tại");
            }

            foreach ($request->get('order_items') as $value) {
                $product = $products->first(function ($item, $key) use ($value) {
                    return $item->id == $value['product_id'];
                });

                if ($product->quantity < $value['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng", 1);
                }
            }
            $order = $this->repositoryOrder->create($data);

            $total = 0;
            foreach ($request->get('order_items') as $value) {
                $product = $products->first(function ($item, $key) use ($value) {
                    return $item->id == $value['product_id'];
                });

                $orderItem = OrderItem::where('product_id', $product->id)->where('order_id', $order->id)->first();

                $amount_price     = $product->price;
                $total_attributes = 0;
                if (isset($value['attributes_value'])) {
                    $attribute_unique = collect($value['attributes_value'])->unique('attribute_id');

                    foreach ($attribute_unique as $attribute_item) {
                        $attribute_chose = $product->attributesValue->search(function ($q) use ($attribute_item) {
                            return $q->id === $attribute_item['value_id'];
                        });

                        if ($attribute_chose !== false) {
                            $attributes_exists = $product->attributesValue->get($attribute_chose);
                            if ($attributes_exists->type === 2) {
                                $total_attr = -$attributes_exists->price;
                            } else if ($attributes_exists->type === 3) {
                                $total_attr = 0;
                            } else {
                                $total_attr = $attributes_exists->price;
                            }
                            $total_attributes += $total_attr;
                        } else {
                            throw new NotFoundException('Thuộc tính có id = ' . $attribute_item['value_id'] . ' không tồn tại !', 1);
                        }
                    }
                }

                $amount_price += $total_attributes;

                if ($orderItem) {
                    $orderItem->update(['quantity' => $value['quantity']]);
                } else {
                    $order_item             = new OrderItem;
                    $order_item->order_id   = $order->id;
                    $order_item->product_id = $product->id;
                    $order_item->price      = $amount_price;
                    $order_item->quantity   = $value['quantity'];
                    $order_item->save();
                }

                if (isset($value['attributes_value'])) {
                    $attribute_unique = collect($value['attributes_value'])->unique('attribute_id');
                    foreach ($attribute_unique as $item) {
                        $attribute_item                = new OrderProductAttribute;
                        $attribute_item->order_item_id = $order_item->id;
                        $attribute_item->product_id    = $product->id;
                        $attribute_item->value_id      = $item['value_id'];
                        $attribute_item->save();
                    }
                }

                $product->quantity -= (int) $value['quantity'];
                $product->sold_quantity += (int) $value['quantity'];
                $product->save();

                $calcualator = $amount_price * $value['quantity'];
                $total += (int) $calcualator;
            }

            $order->total = $total;
            $order->save();

            $attributes_order = collect($request->get('order_items'))->pluck('attributes');

            event(new AddAttributesEvent($order, $attributes_order));
        } else {
            throw new \Exception("Không thể tạo đơn hàng không có sản phẩm nào !", 1);
        }

        $this->entity->sendMailOrder($order);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($order, $transformer);
    }

    public function update(Request $request, $id)
    {
        $this->validatorOrder->isValid($request, 'RULE_ADMIN_UPDATE');

        $this->repositoryOrder->findById($id);

        $data = $request->all();

        if ($request->has('order_items')) {
            unset($data['order_items']);
        }

        if ($request->has('includes')) {
            unset($data['includes']);
        }

        $order = $this->repositoryOrder->update($data, $id);

        if ($request->has('order_items')) {

            $product_ids = collect($request->get('order_items'))->pluck('product_id');
            $products    = Product::whereIn('id', $product_ids)->get();

            foreach ($request->get('order_items') as $value) {
                $product = $products->first(function ($item, $key) use ($value) {
                    return $item->id == $value['product_id'];
                });

                if ($product->quantity < $value['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng", 1);
                }
            }

            $orderitems = OrderItem::where('order_id', $order->id);
            $items      = $orderitems->get();

            $items_old = [];
            foreach ($items as $item) {
                $product = $products->first(function ($item, $key) use ($value) {
                    return $item->id == $value['product_id'];
                });

                if ($item->product_id !== $product->id) {
                    $product_org = Product::where('id', $item->product_id)->first();
                    $product_org->quantity += $item->quantity;
                    $product_org->sold_quantity -= $item->quantity;
                    $product_org->save();
                } else {
                    $data = [
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                    ];
                    array_push($items_old, $data);
                }
            }

            $orderitems->delete();

            $total = 0;

            foreach ($request->get('order_items') as $value) {
                $product = $products->first(function ($item, $key) use ($value) {
                    return $item->id == $value['product_id'];
                });

                $order_item             = new OrderItem;
                $order_item->order_id   = $order->id;
                $order_item->product_id = $product->id;
                $order_item->price      = $product->price;
                $order_item->quantity   = $value['quantity'];
                $order_item->save();

                foreach ($items_old as $item) {
                    if ($item['product_id'] == $product->id) {
                        $product->quantity      = $product->quantity + $item['quantity'] - $value['quantity'];
                        $product->sold_quantity = $product->sold_quantity - $item['quantity'] + $value['quantity'];
                        $product->save();
                    }
                }
                $calcualator = $product->price * $value['quantity'];
                $total += (int) $calcualator;
            }

            $order->total = $total;
            $order->save();
        }

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($order, $transformer);
    }

    public function destroy($id)
    {
        $order = $this->repositoryOrder->findById($id);

        if ($order->status_id !== 2 && $order->orderItems->count()) {
            throw new \Exception("Đang có sản phẩm trong giỏ hàng", 1);
        }

        $order->delete();

        return $this->success();
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validatorOrder->isValid($request, 'UPDATE_STATUS_ITEM');

        $this->repositoryOrder->findById($id);

        $this->repositoryOrder->updateStatus($request, $id);

        return $this->success();
    }

    public function paymentStatus(Request $request, $id)
    {
        $this->validatorOrder->isValid($request, 'UPDATE_PAYMENT_STATUS');

        $this->repositoryOrder->findById($id);

        $this->repositoryOrder->paymentStatus($request, $id);
        return $this->success();
    }
}
