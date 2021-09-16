<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Api\Admin;

use Complex\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Repositories\OrderItemRepository;
use VCComponent\Laravel\Order\Transformers\OrderItemTransformer;
use VCComponent\Laravel\Order\Validators\OrderItemValidator;
use VCComponent\Laravel\Product\Entities\Product;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class OrderItemController extends ApiController
{
    protected $repository;

    public function __construct(OrderItemRepository $repository, OrderItemValidator $validator)
    {
        $this->repository  = $repository;
        $this->entity      = $repository->getEntity();
        $this->validator   = $validator;
        $this->transformer = OrderItemTransformer::class;

        if (!empty(config('order.auth_middleware.admin'))) {
            $user = $this->getAuthenticatedUser();
            if (Gate::forUser($user)->denies('manage', $this->entity)) {
                throw new PermissionDeniedException();
            }
        }

    }

    public function update(Request $request, $id)
    {

        $this->validator->isValid($request, 'RULE_ADMIN_UPDATE');

        $orderItem =  $this->repository->findById($id);
        $product   = Product::where('id', $orderItem->product_id)->first();
        $quantity  = $request->get('quantity');

        if ($product->quantity < $quantity) {
            throw new \Exception("Sản phẩm {$product->name} không đủ số lượng", 1);
        }

        $origin_quantity     = $product->quantity + $orderItem->quantity;
        $origin_quantitySold = $product->sold_quantity - $orderItem->quantity;

        $orderItem = $this->repository->update(['quantity' => $quantity], $id);

        $product->quantity      = $origin_quantity - $quantity;
        $product->sold_quantity = $origin_quantitySold + $quantity;
        $product->save();

        $order = Order::where('id', $orderItem->order_id)->with('orderItems')->first();

        $totalPrice = 0;
        foreach ($order->orderItems as $item) {
            $total = $item->quantity * $item->price;
            $totalPrice += $total;
        }
        $order->total = $totalPrice;
        $order->save();

        return $this->response->item($orderItem, new $this->transformer);
    }

    public function destroy($id)
    {
        $orderItem = $this->repository->findById($id);

        $orderItem->delete();

        return $this->success();
    }

}
