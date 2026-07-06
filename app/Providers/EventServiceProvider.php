<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    public function boot(): void
    {
        parent::boot();

        \Event::listen(Login::class, function (Login $event) {
            activity()
                ->causedBy($event->user)
                ->withProperties(['ip' => request()->ip(), 'user_agent' => substr(request()->userAgent() ?? '', 0, 150)])
                ->event('login')
                ->log('Inicio de sesión');
        });

        \Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                activity()
                    ->causedBy($event->user)
                    ->withProperties(['ip' => request()->ip()])
                    ->event('logout')
                    ->log('Cierre de sesión');
            }
        });
    }
}
