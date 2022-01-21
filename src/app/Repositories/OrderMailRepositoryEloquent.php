<?php

namespace VCComponent\Laravel\Order\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Order\Entities\OrderMail;
use VCComponent\Laravel\Order\Repositories\OrderMailRepository;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

/**
 * Class AccountantRepositoryEloquent.
 */
class OrderMailRepositoryEloquent extends BaseRepository implements OrderMailRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        if (isset(config('order.models')['order'])) {
            return config('order.models.order-mail');
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
        $order = $this->model->find($id);
        if (!$order) {
            throw new NotFoundException('Mail');
        }
        return $order;
    }

    public function updateStatus($request, $id)
    {
        $updateStatus            = $this->find($id);
        $updateStatus->status = $request->input('status');
        $updateStatus->save();
    }
}
