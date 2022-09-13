<?php

declare(strict_types=1);

namespace Tests;

/**
 * @internal
 *
 * @coversNothing
 */
final class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function testThatBaseEndpointReturnsASuccessfulResponse(): void
    {
        $this->get('/');

        static::assertSame(
            $this->app->version(),
            $this->response->getContent()
        );
    }
}
