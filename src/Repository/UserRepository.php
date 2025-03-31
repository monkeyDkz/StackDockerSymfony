<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function generateQueryByCriteria(array $criteria, array $order = [], $limit = null): Query
    {
        $qb = $this->createQueryBuilder('u');

        $joinKeys = [
        ];
        $joined = [];

        foreach ($criteria as $key => $criterion) {
            switch ($key) {
                case 'externalId':
                    $qb->andWhere("JSON_CONTAINS(u.externalId, :externalId) = true")
                        ->setParameter('externalId', $criterion)
                    ;
                    break;
                default:
                    $qb->andWhere("u.$key = :$key")
                        ->setParameter($key, $criterion)
                    ;
                    break;
            }
        }
        foreach ($order as $key => $value) {
            if (strchr($key, '.')) {
                [$subJoin, $subKey] = explode('.', $key);
            } else {
                if (array_key_exists($key, $joinKeys)) {
                    $subJoin = $key;
                    $subKey = "id";
                } else {
                    $subJoin = null;
                    $subKey = $key;
                }
            }
            if ($subJoin !== null) {
                $prefix = $joinKeys[$subJoin];
                $joined[$prefix] = true;
            } else {
                $prefix = 'u';
            }
            $qb->addOrderBy("{$prefix}.{$subKey}", $value);
        }

        foreach ($joined as $prefix => $value) {
            $qb->join("u.$prefix", $prefix);
        }

        return $qb->getQuery();
    }

    public function findByCriteria(array $criteria, array $order = []): array
    {
        return $this->generateQueryByCriteria($criteria, $order)->getResult();
    }

    public function findOneByCriteria(array $criteria, array $order = [])
    {
        return $this->generateQueryByCriteria($criteria, $order)->getOneOrNullResult();
    }

}
