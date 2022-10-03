<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractDoctrineRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function entityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function persistEntity(object $entity): void
    {
        $this->entityManager()->persist($entity);
        $this->entityManager()->flush();
    }

    protected function removeEntity(object $entity): void
    {
        $this->entityManager()->remove($entity);
        $this->entityManager()->flush();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @return EntityRepository<T>
     */
    protected function repository(string $entityClass): EntityRepository
    {
        return $this->entityManager()->getRepository($entityClass);
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager()->createQueryBuilder();
    }
}
