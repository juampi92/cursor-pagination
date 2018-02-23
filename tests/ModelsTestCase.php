<?php

namespace Juampi92\CursorPagination\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Juampi92\CursorPagination\Tests\Fixtures\Models\User;

class ModelsTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
        $this->setUpRoutes($this->app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('_id');
            $table->string('name');
            $table->rememberToken();
            $table->timestamps();
        });
        foreach (range(1, 40) as $index) {
            User::create(['name' => "user{$index}"]);
        }
    }

    protected function setUpRoutes(Application $app)
    {
        \Route::get('/test/one', function () {
            return User::cursorPaginate(5)->toJson();
        });
        \Route::get('/test/resource', function () {
            $res = (new \Illuminate\Http\Resources\Json\ResourceCollection(User::cursorPaginate(5)));
            return $res->toResponse(request())->getContent();
        });
    }
}