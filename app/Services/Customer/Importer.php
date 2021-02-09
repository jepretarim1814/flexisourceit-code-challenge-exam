<?php


namespace App\Services\Customer;


use App\Entities\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Customer\Contracts\ImporterContract;
use App\Services\Customer\Contracts\ToImportContract;

class Importer implements ImporterContract
{

    protected EntityManagerInterface $entityManager;

    protected Dispatcher $dispatcher;

    protected Manager $manager;

    /**
     * Importer constructor.
     * @param Manager $manager
     * @param EntityManagerInterface $entityManager
     * @param Dispatcher $dispatcher
     */
    public function __construct(Manager $manager, EntityManagerInterface $entityManager, Dispatcher $dispatcher)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ToImportContract|string $contract
     * @param array $options
     */
    public function import($contract, array $options = []) : void
    {
        $results = $this->manager->results($options);
        $results->each(function ($result, $index) use ($contract) {
            $this->entityManager->persist(
                $this->createOrUpdate($this->checkIfString($contract), $result)
            );
            $this->dispatchPersist($result, $index);
        });
        $this->entityManager->flush();
    }

    /**
     * @param ToImportContract|string $contract
     * @return ToImportContract
     */
    protected function checkIfString($contract) : ToImportContract
    {
        return is_string($contract) ? new $contract : $contract;
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
    protected function dispatchPersist(array $result, int $index) : void
    {
        if ($this->dispatcher !== null)
        {
            $this->dispatcher->dispatch('customer.import', compact('result', 'index'));
        }
    }

    /**
     * @param ToImportContract $contract
     * @param array|mixed $result
     * @return Customer
     */
    protected function createOrUpdate(ToImportContract $contract, array $result) : Customer
    {
        $importClass = $contract->toImport($result);
        $entity = $this->findEntity($importClass);
        if ($entity->getId() !== null)
        {
            return $contract->toImport($result, $entity);
        }
        return $importClass;
    }
}
