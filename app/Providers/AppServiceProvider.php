<?php

namespace App\Providers;

use App\Domain\Wallet\Repository\TransactionWriterInterface;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use App\Infrastructure\Persistence\EloquentWalletRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(EloquentWalletRepository::class);

        $this->app->bind(WalletRepositoryInterface::class, EloquentWalletRepository::class);
        $this->app->bind(TransactionWriterInterface::class, EloquentWalletRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
