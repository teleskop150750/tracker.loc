<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\ResendVerificationNotification;

use Modules\Auth\User\Domain\Services\EmailVerification;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;

class ResendVerificationNotificationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly EmailVerification $emailVerification,
    ) {
    }

    public function __invoke(ResendVerificationNotificationCommand $command): void
    {
        $user = $this->userFetcher->getAuthUser();
        $this->emailVerification->sendEmailVerificationNotification($user);
    }
}
