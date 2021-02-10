<?php

namespace App\Http\Controllers;

use App\Entities\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\CustomerResource;
use App\Repositories\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Criteria;
use App\Http\Resources\CustomerListsResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return AnonymousResourceCollection
     * @throws ValidationException
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        $this->validate($request, [
            'order' => [
                Rule::in([
                    Criteria::DESC,
                    Criteria::ASC,
                ]),
            ],
            'limit' => [
                'integer',
            ],
            'page' => [
                'integer',
            ],
        ]);
        /** @var CustomerRepository $repository */
        $repository = $entityManager->getRepository(Customer::class);

        return CustomerListsResource::collection(
            $repository->all(
                $order = $request->get('order', Criteria::DESC),
                $limit = (int) $request->get('limit', CustomerRepository::LIMIT),
                (int) $request->get('page', 1)
            )
                ->withPath(route('customer.index'))
                ->appends(compact('limit', 'order'))
        );
    }

    /**
     * @param Customer $customer
     * @return JsonResource
     */
    public function show(Customer $customer) : JsonResource
    {
        return new CustomerResource($customer);
    }
}
