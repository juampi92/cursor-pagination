<?php

namespace Juampi92\CursorPagination\Tests;

use Juampi92\CursorPagination\CursorPaginationServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param \Illuminate\Foundation\Application $application
     *
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return [CursorPaginationServiceProvider::class];
    }
}
