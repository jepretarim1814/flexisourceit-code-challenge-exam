<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelDoctrine\ORM\Pagination\PaginatesFromParams;

/**
 * CustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerRepository extends EntityRepository
{
    use PaginatesFromParams;

    public const LIMIT = 15;

    /**
     * @param string $order
     * @param int $limit
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function all($order = Criteria::DESC, int $limit = self::LIMIT, int $page = 1) : LengthAwarePaginator
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.id', $order)
            ->getQuery();

        return $this->paginate($qb, $limit, $page);
    }
}
