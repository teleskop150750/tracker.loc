<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Arr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\InteractsWithTime;

class UrlGenerator extends \Laravel\Lumen\Routing\UrlGenerator
{
    use InteractsWithTime;

    /**
     * The encryption key resolver callable.
     *
     * @var callable
     */
    protected $keyResolver;

    /**
     * Create a signed route URL for a named route.
     *
     * @param array|mixed $parameters
     */
    public function signedRoute(string $name, array $parameters = [], \DateInterval|\DateTimeInterface|int $expiration = null, bool $absolute = true): string
    {
        $parameters = Arr::wrap($parameters);

        if (\array_key_exists('signature', $parameters)) {
            throw new \InvalidArgumentException(
                '"Signature" is a reserved parameter when generating signed routes. Please rename your route parameter.'
            );
        }

        if ($expiration) {
            $parameters += ['expires' => $this->availableAt($expiration)];
        }

        $key = \call_user_func($this->keyResolver);

        return $this->route(
            $name,
            $parameters + [
                'signature' => hash_hmac('sha256', $this->route($name, $parameters, $absolute), $key),
            ],
            $absolute
        );
    }

    public function signed(string $name, array $parameters = [], \DateInterval|\DateTimeInterface|int $expiration = null, bool $absolute = true): array
    {
        $parameters = Arr::wrap($parameters);

        if (\array_key_exists('signature', $parameters)) {
            throw new \InvalidArgumentException(
                '"Signature" is a reserved parameter when generating signed routes. Please rename your route parameter.'
            );
        }

        if ($expiration) {
            $parameters += ['expires' => $this->availableAt($expiration)];
        }

        $key = \call_user_func($this->keyResolver);

        $parameters += [
            'signature' => hash_hmac('sha256', $this->route($name, $parameters, $absolute), $key),
        ];

        return $parameters;
    }

    /**
     * Create a temporary signed route URL for a named route.
     */
    public function temporarySignedRoute(string $name, \DateInterval|\DateTimeInterface|int $expiration, array $parameters = [], bool $absolute = true): string
    {
        return $this->signedRoute($name, $parameters, $expiration, $absolute);
    }

    public function temporarySigned(string $name, \DateInterval|\DateTimeInterface|int $expiration, array $parameters = [], bool $absolute = true): array
    {
        return $this->signed($name, $parameters, $expiration, $absolute);
    }

    /**
     * Determine if the given request has a valid signature.
     *
     * @param mixed $request
     */
    public function hasValidSignature($request, bool $absolute = true): bool
    {
        return $this->hasCorrectSignature($request, $absolute) && $this->signatureHasNotExpired($request);
    }

    /**
     * Determine if the given request has a valid signature for a relative URL.
     */
    public function hasValidRelativeSignature(Request $request): bool
    {
        return $this->hasValidSignature($request, false);
    }

    /**
     * Determine if the signature from the given request matches the URL.
     */
    public function hasCorrectSignature(Request $request, bool $absolute = true): bool
    {
        $url = $absolute ? $request->url() : '/'.$request->path();
        $original = rtrim($url.'?'.Arr::query(Arr::except($request->query(), 'signature')), '?');
        $signature = hash_hmac('sha256', $original, \call_user_func($this->keyResolver));

        return hash_equals($signature, (string) $request->query('signature', ''));
    }

    /**
     * Determine if the expires timestamp from the given request is not from the past.
     */
    public function signatureHasNotExpired(Request $request): bool
    {
        $expires = $request->query('expires');

        return !($expires && Carbon::now()->getTimestamp() > $expires);
    }

    /**
     * Set the encryption key resolver.
     *
     * @return $this
     */
    public function setKeyResolver(callable $keyResolver): static
    {
        $this->keyResolver = $keyResolver;

        return $this;
    }
}
