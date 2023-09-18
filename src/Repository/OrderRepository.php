<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

/**
 * findSuccessOrder()
 * permet d'afficher les commandes dans l'espace membre de l'uttilisateur
 *
 * @param User $user L'utilisateur dont les commandes sont recherchées
 * @return array Les résultats de la requête
 */
public function findSuccessOrder($user)
{
    // Crée une nouvelle instance de QueryBuilder sur cette classe pour créer une requête
    return $this->createQueryBuilder('o')
        // Ajoute une condition pour sélectionner uniquement les commandes ayant un état supérieur à 0
        ->andWhere('o.state > 0')
        // Ajoute une condition pour sélectionner uniquement les commandes de l'utilisateur donné en paramètre
        ->andWhere('o.user = :user')
        // Remplace le paramètre :user dans la requête avec la valeur de l'utilisateur donné en paramètre
        ->setParameter('user',$user)
        // Trie les résultats en ordre décroissant selon l'identifiant de la commande
        ->orderBy('o.id','DESC')
        // Obtient la requête SQL créée jusqu'à présent
        ->getQuery()
        // Exécute la requête et retourne les résultats sous forme d'un tableau
        ->getResult();
}

//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
