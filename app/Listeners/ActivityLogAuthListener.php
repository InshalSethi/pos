<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;

class ActivityLogAuthListener
{
    /**
     * Handle authentication events.
     */
    public function handle(object $event): void
    {
        if ($event instanceof Login) {
            /** @var User $user */
            $user = $event->user;
            $actorType = $user->isEmployee() ? 'Employee' : 'User';
            
            ActivityLogger::logAuth(
                event: 'login',
                description: "{$actorType} {$user->name} logged in successfully.",
                user: $user,
                properties: [
                    'guard' => $event->guard,
                    'remember' => $event->remember,
                ]
            );
        } elseif ($event instanceof Logout) {
            /** @var User|null $user */
            $user = $event->user;
            if ($user) {
                $actorType = $user->isEmployee() ? 'Employee' : 'User';
                ActivityLogger::logAuth(
                    event: 'logout',
                    description: "{$actorType} {$user->name} logged out.",
                    user: $user,
                    properties: [
                        'guard' => $event->guard,
                    ]
                );
            }
        } elseif ($event instanceof Failed) {
            $credentials = $event->credentials;
            $email = $credentials['email'] ?? ($credentials['phone'] ?? 'unknown');

            ActivityLogger::log(
                type: 'security',
                event: 'failed_login',
                description: "Failed login attempt for account: {$email}",
                user: $event->user,
                properties: [
                    'guard' => $event->guard,
                    'attempted_email' => $email,
                ]
            );
        } elseif ($event instanceof Registered) {
            /** @var User $user */
            $user = $event->user;

            ActivityLogger::logAuth(
                event: 'account_created',
                description: "New account registered for {$user->name} ({$user->email}).",
                user: $user,
                properties: [
                    'account_type' => $user->type ?? 'user',
                ]
            );
        }
    }
}
