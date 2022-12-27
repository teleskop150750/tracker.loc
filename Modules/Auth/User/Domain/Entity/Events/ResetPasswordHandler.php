<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\Events;

use Illuminate\Support\Facades\Mail;

class ResetPasswordHandler
{
    public function handle(ResetPasswordEvent $event): void
    {
        $frontendUrl = env('SPA_URL_RESET_PASSWORD');
        $params = [
            'token' => $event->passwordResets->getToken()->getToken(),
        ];
        $url = $frontendUrl.'?'.http_build_query($params);
        $data = ['name' => $event->user->getFullName()->getFullName(), 'link' => $url];

        Mail::send('mail.password-reset', $data, static function ($message) use ($event): void {
            $message->to($event->user->getEmail()->toNative(), $event->user->getFullName()->getFullName())->subject('Сбросить пароль');
        });
    }
}
