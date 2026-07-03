<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Pin the test database before RefreshDatabase migrates.
     *
     * The container injects DB_DATABASE=knowledge_base via docker-compose `env_file`,
     * which makes it sticky in $_SERVER — .env.testing and phpunit <env> can't override
     * it. Overriding the resolved config here (and purging the connection) guarantees
     * the suite can only ever touch DB_TEST_DATABASE, never the dev database.
     */
    protected function refreshApplication()
    {
        parent::refreshApplication();

        config(['database.connections.pgsql.database' => env('DB_TEST_DATABASE', 'knowledge_base_test')]);
        $this->app['db']->purge('pgsql');
    }
}
