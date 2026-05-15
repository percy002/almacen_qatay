<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseExit;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email', 'role', 'active']),
                'permissions' => $request->user()
                    ? $this->permissionsFor($request->user())
                    : [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function permissionsFor(User $user): array
    {
        return [
            'Product' => [
                'create' => $user->can('create', Product::class),
            ],
            'WarehouseEntry' => [
                'create' => $user->can('create', WarehouseEntry::class),
            ],
            'WarehouseExit' => [
                'create' => $user->can('create', WarehouseExit::class),
            ],
            'StockAdjustment' => [
                'create' => $user->can('create', StockAdjustment::class),
            ],
            'User' => [
                'create' => $user->can('create', User::class),
            ],
        ];
    }
}
