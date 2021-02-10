<?php

namespace App\Services\Customer;

use App\Entities\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Customer\Events\CustomerImportEvent;
use App\Services\Customer\Contracts\CustomerToImportContract;
use App\Services\Customer\Contracts\CustomerImporterContract;

class CustomerImporter implements CustomerImporterContract
{

    protected EntityManagerInterface $entityManager;

    protected Dispatcher $dispatcher;

    protected CustomerManager $manager;

    /**
     * CustomerImporter constructor.
     * @param CustomerManager $manager
     * @param EntityManagerInterface $entityManager
     * @param Dispatcher $dispatcher
     */
    public function __construct(CustomerManager $manager, EntityManagerInterface $entityManager, Dispatcher $dispatcher)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param CustomerImporterContract|string $contract
     * @param array $options
     */
    public function import($contract, array $options = []) : void
    {
        $results = $this->manager->results($options);
        if ($results !== null) {
            $results->each(function ($result, $index) use ($contract) {
                $this->entityManager->persist(
                    $this->customerCreateOrUpdate(
                        is_string($contract) ? new $contract : $contract,
                        $result
                    )
                );
                $this->dispatchEventOnImport($result, $index);
            });
            $this->entityManager->flush();
        }
    }

    /**
     * @param Customer $customer
     * @return Customer|object
     */
    protected function findEntity(Customer $customer) : Customer
    {
        return $this->entityManager->getRepository(get_class($customer))
                ->findOneBy(['email' => $customer->getEmail()]) ?? $customer;
    }

    /**
     * @param array $result
     * @param int $index
     */
    protected function dispatchEventOnImport(array $result, int $index) : void
    {
        if ($this->dispatcher !== null) {
            $this->dispatcher->dispatch(CustomerImportEvent::class, compact('result', 'index'));
        }
    }

    /**
     * @param CustomerToImportContract $contract
     * @param array|mixed $result
     * @return Customer
     */
    protected function customerCreateOrUpdate(CustomerToImportContract $contract, array $result) : Customer
    {
        $importClass = $contract->toImport($result);
        $entity = $this->findEntity($importClass);
        if ($entity->getId() !== null) {
            return $contract->toImport($result, $entity);
        }
        return $importClass;
    }
}
