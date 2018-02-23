<?php

namespace Juampi92\CursorPagination\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Juampi92\CursorPagination\CursorPaginationServiceProvider;

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
