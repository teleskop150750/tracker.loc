<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ExampleEvent;

class ExampleListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(ExampleEvent $event): void
    {
    }
}
