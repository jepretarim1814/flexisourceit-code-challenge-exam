<?php

namespace App\Services\Customer;

use App\Entities\Customer;
use App\Services\Customer\Contracts\CustomerImporterContract;
use App\Services\Customer\Contracts\CustomerManagerContract;
use App\Services\Customer\Contracts\CustomerToImportContract;
use App\Services\Customer\Events\CustomerImportEvent;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Events\Dispatcher;

class CustomerImporter implements CustomerImporterContract
{
    protected CustomerManagerContract $manager;

    protected ?Dispatcher $dispatcher;

    protected EntityManagerInterface $entityManager;

    /**
     * CustomerImporter constructor.
     * @param CustomerManagerContract $manager
     * @param EntityManagerInterface $entityManager
     * @param ?Dispatcher $dispatcher
     */
    public function __construct(CustomerManagerContract $manager, EntityManagerInterface $entityManager, ?Dispatcher $dispatcher = null)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param CustomerImporterContract|string $contract
     * @param mixed[] $options
     */
    public function import($contract, array $options = []): void
    {
        $results = $this->manager->results($options);
        if ($results !== null) {
            $results->each(function ($result, $index) use ($contract) {
                $this->entityManager->persist(
                    $this->customerCreateOrUpdate(
                        $result,
                        is_string($contract) ? new $contract : $contract
                    )
                );
                $this->dispatchEventOnImport($result, $index);
            });

            $this->entityManager->flush();
        }
    }

    /**
     * @param mixed[] $result
     * @param CustomerToImportContract $contract
     * @return Customer
     */
    protected function customerCreateOrUpdate(array $result, CustomerToImportContract $contract): Customer
    {
        $importClass = $contract->toImport($result);
        $entity = $this->findEntity($importClass);
        if ($entity->getId() !== null) {
            return $contract->toImport($result, $entity);
        }

        return $importClass;
    }

    /**
     * @param Customer $customer
     * @return Customer|object
     */
    protected function findEntity(Customer $customer): Customer
    {
        return $this->entityManager->getRepository(get_class($customer))
                ->findOneBy(['email' => $customer->getEmail()]) ?? $customer;
    }

    /**
     * @param mixed $result
     * @param int $index
     */
    protected function dispatchEventOnImport(array $result, int $index): void
    {
        if ($this->dispatcher !== null) {
            $this->dispatcher->dispatch(CustomerImportEvent::class, compact('result', 'index'));
        }
    }

    /**
     * @return Dispatcher|null
     */
    public function getDispatcher(): ?Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @param Dispatcher|null $dispatcher
     * @return CustomerImporter
     */
    public function setDispatcher(?Dispatcher $dispatcher = null): CustomerImporter
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}
