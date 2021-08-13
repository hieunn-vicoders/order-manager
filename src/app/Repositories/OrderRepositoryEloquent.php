<?php

namespace VCComponent\Laravel\Order\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Order\Entities\Order;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Entities\OrderStatus;
use VCComponent\Laravel\Order\Repositories\OrderRepository;
use VCComponent\Laravel\Product\Entities\Product;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

/**
 * Class AccountantRepositoryEloquent.
 */
class OrderRepositoryEloquent extends BaseRepository implements OrderRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        if (isset(config('order.models')['order'])) {
            return config('order.models.order');
        } else {
            return Post::class;
        }
    }

    public function getEntity()
    {
        return $this->model;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function findById($id)
    {
        $order = $this->model->find($id);
        if (!$order) {
            throw new NotFoundException('Order');
        }
        return $order;
    }

    public function updateStatus($request, $id)
    {
        $updateStatus            = $this->find($id);
        $status = OrderStatus::where('status_id', $request->input('status_id'))->first();
        $updateStatus->status_id = $request->input('status_id');
        if (!$status) {
            throw new NotFoundException('Order Status');
        }
        $updateStatus->save();

    }

    public function paymentStatus($request, $id)
    {
        $paymentStatus                 = $this->find($id);
        $paymentStatus->payment_status = $request->input('payment_status');
        $paymentStatus->save();
    }
}
