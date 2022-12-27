<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\ResendVerificationNotification;

use Illuminate\Support\Facades\Event;
use Modules\Auth\User\Domain\Entity\Events\ResendEmailVerificationEvent;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;

class ResendVerificationNotificationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(ResendVerificationNotificationCommand $command): void
    {
        $user = $this->userFetcher->getAuthUser();
        Event::dispatch(new ResendEmailVerificationEvent($user));
    }
}
