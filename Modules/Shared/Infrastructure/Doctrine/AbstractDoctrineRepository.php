<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Doctrine;

use App\Support\Arr;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Domain\ValueObject\Identity\UUID;

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

//    /**
//     * @param array<string, mixed> $filters
//     */
//    protected function qbFilter(QueryBuilder $qb, array $filters): QueryBuilder
//    {
//        foreach ($filters['where'] as $where) {
//            $qb->andWhere($where);
//        }
//
//        foreach ($filters['orWhere'] as $where) {
//            $qb->orWhere($where);
//        }
//
//        foreach ($filters['parameters'] as $parameter) {
//            $qb->setParameter($parameter['key'], $parameter['value']);
//        }
//
//        return $qb;
//    }

    /**
     * @param array<int, mixed> $array
     *
     * @return array<int, mixed>
     */
    protected function formatArray(array $array): array
    {
        if (Arr::isList($array)) {
            return Arr::map($array, function ($item) {
                return $this->formatArrayRecursive($item);
            });
        }

        return $this->formatArrayRecursive($array);
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     */
    protected function formatArrayRecursive(mixed $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (\is_array($value) && Arr::isList($value)) {
                $v = Arr::map($value, function ($item) {
                    return $this->formatArrayRecursive($item);
                });
            } else {
                $v = \is_array($value) ? $this->formatArrayRecursive($value) : $value;
            }

            if ($v instanceof UUID) {
                $v = $v->getId();
                $key = 'id';
            }

            if ($v instanceof \DateTimeImmutable) {
                $v = $v->format(\DateTimeInterface::W3C);
            }

            $k = str_replace('.value', '', $key);
            $result[$k] = $v;
        }

        return Arr::undot($result);
    }
}
