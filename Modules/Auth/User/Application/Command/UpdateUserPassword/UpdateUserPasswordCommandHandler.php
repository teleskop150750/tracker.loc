<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\UpdateUserPassword;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPassword;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;

class UpdateUserPasswordCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateUserPasswordCommand $command): void
    {
        $user = $this->userFetcher->getAuthUser();

        if (!Hash::check($command->currentPassword, $user->getPassword()->toNative())) {
            throw ValidationException::withMessages(['currentPassword' => 'Неверный пароль']);
        }

        $user->setPassword(UserPassword::fromPlainText($command->password));
        $this->entityManager->flush();
    }
}
