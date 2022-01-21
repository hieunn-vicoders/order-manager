<?php

namespace VCComponent\Laravel\Order\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Order\Entities\OrderItem;
use VCComponent\Laravel\Order\Repositories\OrderItemRepository;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

/**
 * Class AccountantRepositoryEloquent.
 */
class OrderItemRepositoryEloquent extends BaseRepository implements OrderItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        if (isset(config('order.models')['order'])) {
            return config('order.models.order-item');
        } else {
            return OrderMail::class;
        }
    }

    public function getEntity()
    {
        return $this->model;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function findById($id)
    {
        $orderItem = $this->model->find($id);
        if (!$orderItem) {
            throw new NotFoundException('Order Item');
        }
        return $orderItem;
    }
}
