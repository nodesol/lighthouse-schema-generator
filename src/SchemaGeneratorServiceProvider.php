<?php

declare(strict_types=1);

namespace Nodesol\LighthouseSchemaGenerator;

use Doctrine\DBAL\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Nodesol\LighthouseSchemaGenerator\Commands\MakeGraphqlSchemaCommand;

class SchemaGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     * @throws Exception
     */
    public function register(): void
    {
        $this->registerDoctrineTypeMapping();
    }

    /**
     * @throws Exception
     */
    private function registerDoctrineTypeMapping(): void
    {
        if (!defined('__PHPSTAN_RUNNING__')) {
            DB::getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');

        }
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeGraphqlSchemaCommand::class,
            ]);
        }
    }
}
