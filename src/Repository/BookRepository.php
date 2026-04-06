<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    public function findByFilters(?string $search, ?string $category, ?string $author): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.categories', 'c')
            ->leftJoin('b.language', 'l');

        if ($search) {
            $qb->andWhere('LOWER(b.title) LIKE LOWER(:search) OR LOWER(b.author) LIKE LOWER(:search) OR LOWER(b.description) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $qb->andWhere('LOWER(c.name) = LOWER(:category)')
                ->setParameter('category', $category);
        }

        if ($author) {
            $qb->andWhere('LOWER(b.author) LIKE LOWER(:author)')
                ->setParameter('author', '%' . $author . '%');
        }

        return $qb->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array Returns an array of unique book titles
     */
    public function findAllTitles(): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.title')
            ->distinct(true)
            ->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return array Returns an array of unique authors
     */
    public function findAllAuthors(): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.author')
            ->distinct(true)
            ->where('b.author IS NOT NULL')
            ->andWhere('b.author != :empty')
            ->setParameter('empty', '')
            ->orderBy('b.author', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
