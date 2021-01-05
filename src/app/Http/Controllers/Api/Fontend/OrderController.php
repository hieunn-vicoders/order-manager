<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Api\Fontend;

use Complex\Exception;
use Illuminate\Http\Request;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\Order\Transformers\OrderItemsTransformer;
use VCComponent\Laravel\Order\Transformers\OrderTransformer;
use VCComponent\Laravel\Order\Validators\OrderValidator;
use VCComponent\Laravel\Product\Entities\Product;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class OrderController extends ApiController
{
    protected $repository;
    protected $validator;

    public function __construct(OrderRepository $repository, OrderValidator $validator)
    {
        $this->repository  = $repository;
        $this->entity      = $repository->getEntity();
        $this->validator   = $validator;
        $this->transformer = OrderTransformer::class;
    }

    public function index(Request $request, $id)
    {
        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, [], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $order    = $query->where('user_id', $id)->paginate($per_page);

        return $this->response->paginator($order, new $this->transformer);
    }

    public function show(Request $request, $id)
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
        $this->validator->isValid($request, 'RULE_ADMIN_CREATE');

        $data = $request->all();

        if ($request->has('order_items')) {
            unset($data['order_items']);
        }

        $order = $this->entity->where($data)->first();

        if ($order) {
            throw new \Exception("Order này đã tồn tại", 1);
        }

        if ($request->has('order_items')) {

            $product_ids = collect($request->get('order_items'))->pluck('product_id');
            $products    = Product::whereIn('id', $product_ids)->get();

            $product_exists = array_values(array_diff($product_ids->toArray(), $products->pluck('id')->toArray()));

            if ($product_exists !== []) {
                throw new \Exception("Sản phẩm có id = {$product_exists[0]} không tồn tại", 1);
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

                $amount_price     = $product->price;
                $total_attributes = 0;
                if(isset($value['attributes_value'])) {
                    $attribute_unique = collect($value['attributes_value'])->unique('attribute_id');

                    foreach ($attribute_unique as $attribute_item) {
                        $attribute_chose = $product->attributesValue->search(function ($q) use ($attribute_item) {
                            return $q->id === $attribute_item['value_id'];
                        });

                        if($attribute_chose !== false) {
                            $attributes_exists = $product->attributesValue->get($attribute_chose);
                            if ($attributes_exists->type === 2) {
                                $total_attr = - $attributes_exists->price;
                            } else if ($attributes_exists->type === 3) {
                                $total_attr = 0;
                            } else {
                                $total_attr = $attributes_exists->price;
                            }
                            $total_attributes += $total_attr;
                        } else {
                            throw new \Exception('Thuộc tính có id = '. $attribute_item['value_id'] .' không tồn tại !', 1);
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

                if(isset($value['attributes_value'])) {
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
        } else {
            throw new \Exception("Không thể tạo đơn hàng không có sản phẩm nào !", 1);
        }

        $this->entity->sendMailOrder($order);

        return $this->response->item($order, new $this->transformer);
    }
}
