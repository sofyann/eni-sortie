<?php

namespace App\Repository;

use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Trip|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trip|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trip[]    findAll()
 * @method Trip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }
/**
* Requête personnalisée pour récupérer les trips
*
*/
    public function findListTrips()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->addSelect('t')
            ->addSelect('s')
            ->join('t.state', 's');
        //$qb->addOrderBy('t.registration_deadline', 'DESC');
        $query = $qb->getQuery();

        $query->setMaxResults(30);
        $query->setFirstResult(0);

        $results = $query->getResult();
        return $results;
    }



}
