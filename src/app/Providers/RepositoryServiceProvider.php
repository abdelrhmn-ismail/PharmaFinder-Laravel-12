<?php

namespace App\Providers;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        
        // Bind repositories
        $this->app->bind(ProductRepository::class, function ($app) {
            return new ProductRepository($app->make('App\Models\Product'));
        });
    }
}