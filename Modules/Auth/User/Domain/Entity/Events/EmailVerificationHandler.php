<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\Events;

use App\Services\UrlGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class EmailVerificationHandler
{
    public function __construct(
        private readonly UrlGenerator $urlGenerator,
    ) {
    }

    public function handle(RegisterUserEvent $event): void
    {
        $frontendUrl = env('SPA_URL_EMAIL_VERIFICATION');
        $params = $this->urlGenerator->temporarySigned(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $event->user->getUuid()->getId(), 'hash' => sha1($event->user->getEmail()->toNative())],
            false
        );
        $url = $frontendUrl.'?'.http_build_query($params);
        $data = ['name' => $event->user->getFullName()->getFullName(), 'link' => $url];

        Mail::send('mail.register', $data, static function ($message) use ($event): void {
            $message->to($event->user->getEmail()->toNative(), $event->user->getFullName()->getFullName())->subject('Подтвердить пароль');
        });
    }
}
