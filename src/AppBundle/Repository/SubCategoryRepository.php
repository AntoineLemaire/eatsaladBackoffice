<?php

namespace AppBundle\Repository;

/**
 * SubCategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SubCategoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function findEnabledOnes(){
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s, q')
            ->leftJoin('s.questions','q')
            ->where('s.active = true')
            ->andWhere('q.active = true')
        ;
        return $qb->getQuery()->getResult();
    }
}
