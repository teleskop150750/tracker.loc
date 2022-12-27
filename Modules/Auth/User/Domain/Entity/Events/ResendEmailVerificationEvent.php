<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\User\Domain\Entity\User;

class ResendEmailVerificationEvent extends Event
{
    use SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
