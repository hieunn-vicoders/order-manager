<?php

namespace VCComponent\Laravel\Order\Repositories;

use VCComponent\Laravel\Order\Repositories\CartRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Order\Entities\Cart;

/**
 * Class AccountantRepositoryEloquent.
 */
class CartRepositoryEloquent extends BaseRepository implements CartRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        if (isset(config('order.models')['order'])) {
            return config('order.models.cart');
        } else {
            return Cart::class;
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
}
