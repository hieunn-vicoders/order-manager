<?php

namespace VCComponent\Laravel\Order\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Order\Entities\CartItem;
use VCComponent\Laravel\Order\Repositories\CartItemRepository;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;
/**
 * Class AccountantRepositoryEloquent.
 */
class CartItemRepositoryEloquent extends BaseRepository implements CartItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        if (isset(config('order.models')['order'])) {
            return config('order.models.cartItem');
        } else {
            return CartItem::class;
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
        $cartItem = $this->model->find($id);
        if (!$cartItem) {
            throw new NotFoundException('Cart item');
        }
        return $cartItem;
    }
}
