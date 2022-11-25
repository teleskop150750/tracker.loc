<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Services;

use App\Services\UrlGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\PasswordToken;

class EmailVerification
{
    public function __construct(
        private readonly UrlGenerator $urlGenerator,
    ) {
    }

    public function sendEmailVerificationNotification(User $user): void
    {
        $frontendUrl = env('SPA_URL_EMAIL_VERIFICATION');
        $verifyUrl = $this->urlGenerator->temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->getUuid()->getId(), 'hash' => sha1($user->getEmail()->toNative())],
            false
        );
        $parsedUrl = parse_url($verifyUrl);
        $verifyUrl = "{$parsedUrl['path']}?{$parsedUrl['query']}";
        $url = $frontendUrl.'?api_url='.urlencode($verifyUrl);
        $data = ['name' => $user->getFullName()->getFullName(), 'link' => $url];
        Mail::send('mail.register', $data, static function ($message) use ($user): void {
            $message->to($user->getEmail()->toNative(), $user->getFullName()->getFullName())->subject('Подтвердить пароль');
        });
    }

    public function sendResetPasswordNotification(User $user, PasswordToken $token): void
    {
        $frontendUrl = env('SPA_URL_RESET_PASSWORD');
        $url = $frontendUrl.'?token='.urlencode($token->getToken());
        $data = ['name' => $user->getFullName()->getFullName(), 'link' => $url];
        Mail::send('mail.password-reset', $data, static function ($message) use ($user): void {
            $message->to($user->getEmail()->toNative(), $user->getFullName()->getFullName())->subject('Сбросить пароль');
        });
    }
}
