<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use VCComponent\Laravel\Export\Services\Export\Export;
use VCComponent\Laravel\Order\Entities\Customer;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Entities\OrderProductAttribute;
use VCComponent\Laravel\Order\Events\AddAttributesEvent;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\Order\Transformers\OrderTransformer;
use VCComponent\Laravel\Order\Validators\OrderValidator;
use VCComponent\Laravel\Product\Entities\Product;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;
use VCComponent\Laravel\Vicoders\Core\Exceptions\PermissionDeniedException;

class OrderController extends ApiController
{
    protected $repository;
    protected $validator;

    public function __construct(OrderRepository $repository, OrderValidator $validator)
    {
        $this->repository = $repository;
        $this->entity = $repository->getEntity();
        $this->validator = $validator;
        $this->transformer = OrderTransformer::class;
        if (config('order.auth_middleware.admin.middleware') !== '') {
            $this->middleware(
                config('order.auth_middleware.admin.middleware'),
                ['except' => config('order.auth_middleware.admin.middleware.except')]
            );
        } else {
            throw new Exception("Admin middleware configuration is required");
        }
        $user = $this->getAuthenticatedUser();
        if (!$this->entity->ableToUse($user)) {
            throw new PermissionDeniedException();
        }
    }

    public function export(Request $request)
    {
        $this->validator->isValid($request, 'RULE_EXPORT');

        $data = $request->all();
        $orders = $this->getReportOrders($request);

        $args = [
            'data' => $orders,
            'label' => $request->label ? $data['label'] : 'Orders',
            'extension' => $request->extension ? $data['extension'] : 'Xlsx',
        ];

        $export = new Export($args);
        $url = $export->export();

        if (config('order.test_mode')) {
            return $this->response->array(['data' => $orders]);
        } else {
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
            // 'users.username as `Người tạo`',
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
            $query = $query->whereIn('status_id', $status);
        }

        $query = $query->leftJoin('customers', function ($join) {
            $join->on('orders.customer_id', '=', 'customers.id');
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
            $query = $query->whereIn('status_id', $status);
        }

        if ($request->has('payment_status')) {

            $request->validate([
                'payment_status' => 'required|regex:/^(\d+\,?)*$/',
            ]);

            $payment_status = explode(',', $request->get('payment_status'));
            $query = $query->whereIn('payment_status', $payment_status);
        }

        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $order = $query->paginate($per_page);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->paginator($order, $transformer);
    }

    public function show($id, Request $request)
    {
        $order = $this->repository->findById($id);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($order, $transformer);
    }

    public function store(Request $request)
    {
        $this->validator->isValid($request, 'RULE_CREATE');

        $data = $request->all();

        if ($request->has('order_items')) {
            unset($data['order_items']);
        }
        if ($request->has('promo_code')) {
            unset($data['promo_code']);
        }

        if ($request->has('includes')) {
            unset($data['includes']);
        }

        $order = $this->repository->findWhere($data)->first();

        if ($order) {
            throw new \Exception("Order này đã tồn tại");
        }

        if ($request->has('order_items')) {

            $product_ids = collect($request->get('order_items'))->pluck('product_id');
            $products = Product::whereIn('id', $product_ids)->get();

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
            $order = $this->repository->create($data);

            $total = 0;
            foreach ($request->get('order_items') as $value) {
                $product = $products->first(function ($item, $key) use ($value) {
                    return $item->id == $value['product_id'];
                });

                $orderItem = OrderItem::where('product_id', $product->id)->where('order_id', $order->id)->first();

                $amount_price = $product->price;
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
                    $order_item = new OrderItem;
                    $order_item->order_id = $order->id;
                    $order_item->product_id = $product->id;
                    $order_item->price = $amount_price;
                    $order_item->quantity = $value['quantity'];
                    $order_item->save();
                }

                if (isset($value['attributes_value'])) {
                    $attribute_unique = collect($value['attributes_value'])->unique('attribute_id');
                    foreach ($attribute_unique as $item) {
                        $attribute_item = new OrderProductAttribute;
                        $attribute_item->order_item_id = $order_item->id;
                        $attribute_item->product_id = $product->id;
                        $attribute_item->value_id = $item['value_id'];
                        $attribute_item->save();
                    }
                }

                $product->quantity -= (int) $value['quantity'];
                $product->sold_quantity += (int) $value['quantity'];
                $product->save();

                $calcualator = $amount_price * $value['quantity'];
                $total += (int) $calcualator;
            }
            $total = $this->repository->usePromoCode($request, $order, $total);

            $order->total = $total;
            $order->save();

            $attributes_order = collect($request->get('order_items'))->pluck('attributes');

            event(new AddAttributesEvent($order, $attributes_order));
        } else {
            throw new \Exception("Không thể tạo đơn hàng không có sản phẩm nào !", 1);
        }
        $customer = Customer::updateOrCreate(
            ['phone_number' => $order->phone_number],
            [
                'name' => $order->username,
                'email' => $order->email,
            ]
        );
        $customer->update([
            'oder_count' => $customer->order_count++,
            'total_amount' => $customer->total_amount + $order->total,
        ]);
        $this->entity->where('id', $order->id)->update(['customer_id' => $customer->id]);

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
        $this->validator->isValid($request, 'RULE_ADMIN_UPDATE');

        $this->repository->findById($id);

        $data = $request->all();

        if ($request->has('order_items')) {
            unset($data['order_items']);
        }

        if ($request->has('includes')) {
            unset($data['includes']);
        }

        $order = $this->repository->update($data, $id);

        if ($request->has('order_items')) {

            $product_ids = collect($request->get('order_items'))->pluck('product_id');
            $products = Product::whereIn('id', $product_ids)->get();

            foreach ($request->get('order_items') as $value) {
                $product = $products->first(function ($item, $key) use ($value) {
                    return $item->id == $value['product_id'];
                });

                if ($product->quantity < $value['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng", 1);
                }
            }

            $orderitems = OrderItem::where('order_id', $order->id);
            $items = $orderitems->get();

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
                        'quantity' => $item->quantity,
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

                $order_item = new OrderItem;
                $order_item->order_id = $order->id;
                $order_item->product_id = $product->id;
                $order_item->price = $product->price;
                $order_item->quantity = $value['quantity'];
                $order_item->save();

                foreach ($items_old as $item) {
                    if ($item['product_id'] == $product->id) {
                        $product->quantity = $product->quantity + $item['quantity'] - $value['quantity'];
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
        $order = $this->repository->findById($id);
        $order->delete();
        return $this->success();
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validator->isValid($request, 'UPDATE_STATUS_ITEM');

        $this->repository->findById($id);

        $this->repository->updateStatus($request, $id);

        return $this->success();
    }

    public function paymentStatus(Request $request, $id)
    {
        $this->validator->isValid($request, 'UPDATE_PAYMENT_STATUS');

        $this->repository->findById($id);

        $this->repository->paymentStatus($request, $id);
        return $this->success();
    }
}
