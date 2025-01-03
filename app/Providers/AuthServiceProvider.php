<?php

namespace App\Providers;

use App\Models\ChatLog;
use App\Policies\ChatLogPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ChatLog::class => ChatLogPolicy::class,
    ];

    public function boot(): void
    {
        // Place any additional Gate definitions here, if needed.
    }
}
