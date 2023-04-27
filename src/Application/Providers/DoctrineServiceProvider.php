<?php

namespace App\Providers;

use Doctrine\Persistence\ManagerRegistry;
use Infrastructure\Persistence\Doctrine\Repositories;
use LaravelDoctrine\ORM\DoctrineServiceProvider as ServiceProvider;
use Domains\Accounts\Models\Company\CompanyAccount;
use Domains\Accounts\Models\User\User;
use Domains\Accounts\Repositories\CompanyAccountRepositoryContract;
use Domains\Accounts\Repositories\PasswordResetRepositoryContract;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Sales\Models\Lead\Lead;
use Domains\Sales\Models\Workflow\Workflow;
use Domains\Sales\Repositories\LeadRepositoryContract;
use Domains\Sales\Repositories\WorkflowRepositoryContract;
use Domains\AdminPanel;

class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerAdminPanelRepositories();
        $this->registerRepositories();
    }

    /**
     * Register admin panel repositories
     */
    protected function registerAdminPanelRepositories()
    {
        $em = app(ManagerRegistry::class)->getManager('admin_panel');

        $this->app->singleton(AdminPanel\Repositories\CompanyAccountRepositoryContract::class, function() use ($em) {
              return new Repositories\AdminPanel\DoctrineCompanyAccountRepository(
                  $em,
                  $em->getClassMetaData(AdminPanel\Models\Accounts\CompanyAccount::class)
              );
          });
        $this->app->singleton(AdminPanel\Repositories\CompanyUserRepositoryContract::class, function() use ($em) {
            return new Repositories\AdminPanel\DoctrineCompanyUserRepository(
                $em,
                $em->getClassMetaData(AdminPanel\Models\Accounts\UserCompanyAccount::class)
            );
        });
        $this->app->singleton(AdminPanel\Repositories\UserRepositoryContract::class, function() use ($em) {
            return new Repositories\AdminPanel\DoctrineUserRepository(
                $em,
                $em->getClassMetaData(AdminPanel\Models\Accounts\User::class)
            );
        });
    }

    /**
     * Register repositories
     */
    protected function registerRepositories(): void
    {
        $this->app->singleton(CompanyAccountRepositoryContract::class, function($app) {
            return new Repositories\Accounts\DoctrineCompanyAccountRepository(
                $app['em'],
                $app['em']->getClassMetaData(CompanyAccount::class)
            );
        });
        $this->app->singleton(LeadRepositoryContract::class, function($app) {
            return new Repositories\Sales\DoctrineLeadRepository(
                $app['em'],
                $app['em']->getClassMetaData(Lead::class)
            );
        });
        $this->app->singleton(PasswordResetRepositoryContract::class, function($app) {
            return new Repositories\Accounts\DoctrinePasswordResetRepository(
                $app['em'],
                $app['em']->getClassMetaData(PasswordReset::class)
            );
        });
        $this->app->singleton(UserRepositoryContract::class, function($app) {
            return new Repositories\Accounts\DoctrineUserRepository(
                $app['em'],
                $app['em']->getClassMetaData(User::class)
            );
        });
        $this->app->singleton(WorkflowRepositoryContract::class, function($app) {
            return new Repositories\Sales\DoctrineWorkflowRepository(
                $app['em'],
                $app['em']->getClassMetaData(Workflow::class)
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            ActivityRepositoryContract::class,
            CompanyAccountRepositoryContract::class,
            ContactRepositoryContract::class,
            IndustryRepositoryContract::class,
            PasswordResetRepositoryContract::class,
            UserRepositoryContract::class,
            WorkflowRepositoryContract::class,
        ];
    }
}
