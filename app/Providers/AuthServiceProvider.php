<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseExit;
use App\Models\StockAdjustment;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehouseEntryPolicy;
use App\Policies\WarehouseExitPolicy;
use App\Policies\StockAdjustmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        User::class => UserPolicy::class,
        WarehouseEntry::class => WarehouseEntryPolicy::class,
        WarehouseExit::class => WarehouseExitPolicy::class,
        StockAdjustment::class => StockAdjustmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
