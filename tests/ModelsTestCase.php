<?php

namespace Juampi92\CursorPagination\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Juampi92\CursorPagination\Tests\Fixtures\Models\User;

class ModelsTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Reset config on each request
        config(['cursor_pagination' => require __DIR__.'/Fixtures/config/simple.php']);
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
            $table->dateTime('datetime');
            $table->rememberToken();
            $table->timestamps();
        });
        foreach (range(1, 40) as $index) {
            User::create([
                'name'     => "user{$index}",
                'datetime' => date('Y-m-d H:i:s', mt_rand(strtotime('last month'), strtotime('now'))),
            ]);
        }
    }

    protected function setUpRoutes(Application $app)
    {
        \Route::get('/test/one', function () {
            return User::cursorPaginate(5)->toJson();
        });
        \Route::get('/test/inverse', function () {
            return User::orderBy('_id', 'desc')->cursorPaginate()->toJson();
        });
        \Route::get('/test/query_inverse', function () {
            return User::getQuery()->orderBy('_id', 'desc')->cursorPaginate()->toJson();
        });
        \Route::get('/test/resource', function () {
            $res = (new \Illuminate\Http\Resources\Json\ResourceCollection(User::cursorPaginate(5)));

            return $res->toResponse(request())->getContent();
        });
    }
}
