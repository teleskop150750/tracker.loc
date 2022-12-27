<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\User\Domain\Entity\PasswordResets;
use Modules\Auth\User\Domain\Entity\User;

class ResetPasswordEvent extends Event
{
    use SerializesModels;

    public User $user;
    public PasswordResets $passwordResets;

    public function __construct(User $user, PasswordResets $passwordResets)
    {
        $this->user = $user;
        $this->passwordResets = $passwordResets;
    }
}
