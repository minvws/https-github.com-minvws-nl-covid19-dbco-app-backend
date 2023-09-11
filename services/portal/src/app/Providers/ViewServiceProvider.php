<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\View\Composers\HeaderComposer;
use App\Http\View\Composers\LayoutComposer;
use App\Http\View\Composers\UserInfoComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('includes/header', HeaderComposer::class);
        View::composer('includes/userinfo', UserInfoComposer::class);
        View::composer('*', LayoutComposer::class);
    }
}
