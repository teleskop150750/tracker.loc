<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\UpdateUser;

use Doctrine\ORM\EntityManagerInterface;
use Modules\Auth\User\Domain\Entity\ValueObject\UserDepartment;
use Modules\Auth\User\Domain\Entity\ValueObject\UserFullName;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPhone;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPost;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;

class UpdateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateUserCommand $command): void
    {
        $user = $this->userFetcher->getAuthUser();

        if (null !== $command->firstName) {
            $user->setFullName(UserFullName::fromNative(
                $command->firstName,
                $user->getFullName()->getLastName(),
                $user->getFullName()->getPatronymic()
            ));
        }

        if (null !== $command->lastName) {
            $user->setFullName(UserFullName::fromNative(
                $user->getFullName()->getFirstName(),
                $command->lastName,
                $user->getFullName()->getPatronymic()
            ));
        }

        if (null !== $command->patronymic) {
            $user->setFullName(UserFullName::fromNative(
                $user->getFullName()->getFirstName(),
                $user->getFullName()->getLastName(),
                $command->patronymic,
            ));
        }

        if (null !== $command->phone) {
            $user->setPhone(UserPhone::fromNative($command->phone));
        }

        if (null !== $command->post) {
            $user->setPost(UserPost::fromNative($command->post));
        }

        if (null !== $command->department) {
            $user->setDepartment(UserDepartment::fromNative($command->department));
        }

        $this->entityManager->flush();
    }
}
