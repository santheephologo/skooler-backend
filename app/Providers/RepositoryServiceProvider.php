<?php

namespace App\Providers;

use App\Repository\AdminRepo;
use App\Repository\IAuthRepo;
use App\Repository\IProductRepo;
use App\Repository\IEventRepo;
use App\Repository\IUserRepo;
use App\Repository\IOrderRepo;
use App\Repository\IComplaintRepo;
use App\Repository\OrderRepo;
use App\Repository\AuthRepo;
use App\Repository\ComplaintRepo;
use App\Repository\UserRepo;
use App\Repository\ProductRepo;
use App\Repository\EventRepo;
use App\Repository\IAdminRepo;
use App\Repository\SchoolRepo;
use App\Repository\ISchoolRepo;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IAuthRepo::class, AuthRepo::class);
        $this->app->bind(IUserRepo::class, UserRepo::class);
        $this->app->bind(IAdminRepo::class, AdminRepo::class);
        $this->app->bind(IProductRepo::class, ProductRepo::class);
        $this->app->bind(IEventRepo::class, EventRepo::class);
        $this->app->bind(IOrderRepo::class, OrderRepo::class);
        $this->app->bind(IComplaintRepo::class, ComplaintRepo::class);
        $this->app->bind(ISchoolRepo::class, SchoolRepo::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
