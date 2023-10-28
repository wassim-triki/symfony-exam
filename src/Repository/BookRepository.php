<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByRef(int $ref): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.ref = :ref')
            ->setParameter('ref', $ref)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Book[]
     */
    public function findOrderedByAuthor(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.author', 'a')
            ->orderBy('a.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countPublishedBooks(): int
    {
        return $this->createQueryBuilder('b')
            ->select('count(b.ref)')
            ->where('b.published = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNonPublishedBooks(): int
    {
        return $this->createQueryBuilder('b')
            ->select('count(b.ref)')
            ->where('b.published = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countBooksByCategory(string $category): int
    {
        $em = $this->getEntityManager();
        $dql = "SELECT COUNT(b.ref) FROM App\Entity\Book b WHERE b.category = :category";
        $query = $em->createQuery($dql)->setParameter('category', $category);

        return $query->getSingleScalarResult();
    }

    public function findBooksPublishedBetween(\DateTime $startDate, \DateTime $endDate): array
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT b FROM App\Entity\Book b 
            WHERE b.publicationDate >= :startDate 
            AND b.publicationDate <= :endDate';

        $query = $em->createQuery($dql)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $query->getResult();
    }

    public function findBooksBefore2023ByAuthorsWithMoreThan35Books(): array
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.author', 'a')
            ->where('b.publicationDate < :year2023')
            ->andWhere('a.nb_books > 35')
            ->setParameter('year2023', new \DateTime('2023-01-01'));

        return $qb->getQuery()->getResult();
    }


//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
