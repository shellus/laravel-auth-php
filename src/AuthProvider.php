<?php
namespace Shellus\LaravelAuth;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use \Route;

class AuthProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRouter();
    }

    public function registerRouter()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace('Shellus\LaravelAuth\Controllers')->group(function(){
                Route::post('login', 'AuthController@login');
                Route::post('register', 'AuthController@register');
                Route::post('forgot-password', 'ResetPasswordController@sendResetLinkEmail');
                Route::post('reset-password', 'ResetPasswordController@resetUserPassword');
                Route::post('logout', 'AuthController@logout');
                Route::post('refresh', 'AuthController@refresh');
            });
    }
}
