<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\VerifyEmail;

use Doctrine\ORM\EntityManagerInterface;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmailVerifiedAt;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;

class VerifyEmailCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(VerifyEmailCommand $command): void
    {
        $user = $this->userFetcher->getAuthUser();
        $user->setEmailVerifiedAt(UserEmailVerifiedAt::now());
        $this->entityManager->flush();
    }
}
