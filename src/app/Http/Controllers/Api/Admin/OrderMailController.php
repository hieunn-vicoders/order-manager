<?php

namespace VCComponent\Laravel\Order\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use VCComponent\Laravel\Order\Repositories\OrderMailRepository;
use VCComponent\Laravel\Order\Transformers\OrderMailTransformer;
use VCComponent\Laravel\Order\Validators\OrderMailValidator;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;
use VCComponent\Laravel\Vicoders\Core\Exceptions\PermissionDeniedException;

class OrderMailController extends ApiController
{
    protected $repository;
    protected $validator;

    public function __construct(OrderMailRepository $repository, OrderMailValidator $validator)
    {
        $this->repository = $repository;
        $this->entity = $repository->getEntity();
        $this->validator = $validator;
        $this->transformer = OrderMailTransformer::class;

        if (config('order.auth_middleware.admin.middleware') !== '') {
            $this->middleware(
                config('order.auth_middleware.admin.middleware'),
                ['except' => config('order.auth_middleware.admin.middleware.except')]
            );
        }
        else{
            throw new Exception("Admin middleware configuration is required");
        }
        $user = $this->getAuthenticatedUser();
        if (!$this->entity->ableToUse($user)) {
            throw new PermissionDeniedException();
        }
    }

    public function index(Request $request)
    {
        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['email'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        if ($request->has('status')) {

            $request->validate([
                'status' => 'required|regex:/^(\d+\,?)*$/',
            ]);

            $status = explode(',', $request->get('status'));
            $query = $query->whereIn('status', $status);
        }

        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $order_mail = $query->paginate($per_page);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->paginator($order_mail, $transformer);
    }

    public function show(Request $request, $id)
    {
        $order_mail = $this->repository->findById($id);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($order_mail, $transformer);
    }

    public function store(Request $request)
    {
        $this->validator->isValid($request, 'RULE_ADMIN_CREATE');

        $data = $request->all();

        $order_mail = $this->repository->firstOrCreate($data);

        return $this->response->item($order_mail, new $this->transformer());
    }

    public function update(Request $request, $id)
    {
        $this->validator->isValid($request, 'RULE_ADMIN_UPDATE');

        $this->repository->findById($id);

        $data = $request->all();

        $email_exist = $this->entity->whereEmail($data['email'])->first();

        if ($email_exist && $email_exist->id != $id) {
            throw new \Exception("Mail này đã tồn tại", 1);
        }

        $order_mail = $this->repository->update($data, $id);

        return $this->response->item($order_mail, new $this->transformer());
    }

    public function destroy($id)
    {
        $order_mail = $this->repository->findById($id);

        $order_mail->delete();

        return $this->success();
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validator->isValid($request, 'UPDATE_STATUS_ITEM');

        $this->repository->findById($id);

        $this->repository->updateStatus($request, $id);

        return $this->success();
    }
}
