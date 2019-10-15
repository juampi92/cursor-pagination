<?php

namespace Juampi92\CursorPagination\Tests;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Juampi92\CursorPagination\CursorPaginator;
use Juampi92\CursorPagination\Tests\Fixtures\Models\User;

class MacroTest extends ModelsTestCase
{
    public function test_exist()
    {
        $macroName = 'cursorPaginate';
        $this->assertTrue(QueryBuilder::hasMacro($macroName));
        // Cannot assert the same on EloquentBuilder cause it does not extend Macroable
        //EloquentBuilder::hasMacro($macroName);
    }

    public function test_macro_works()
    {
        $p = User::cursorPaginate($perPage = 2);

        $this->assertInstanceOf(CursorPaginator::class, $p);

        $this->assertEquals(count($p->toArray()['data']), $perPage);
    }

    public function test_on_query_builder_without_identifier()
    {
        $p = \DB::table('users')->cursorPaginate(2);
        $this->assertInstanceOf(CursorPaginator::class, $p);

        $this->expectException(\ErrorException::class);

        // This should fail. id (default) is not defined.
        $p->toArray();
    }

    public function test_on_query_builder_with_identifier()
    {
        $p = \DB::table('users')->cursorPaginate(2, ['*'], [
            'identifier' => '_id',
        ]);
        $this->assertInstanceOf(CursorPaginator::class, $p);

        $this->assertNotNull($p->nextCursor());
    }

    public function test_perPage_dynamic_prev()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $p = User::cursorPaginate([$prev_count = 2, $next_count = 4], ['*'], [
            'request' => $req = new Request([
                $prev_name => User::orderBy('_id', 'desc')->first()->_id,
            ]),
            'path'    => '/',
        ]);

        $this->assertEquals($prev_count, $p->perPage());
        $this->assertEquals(count($p->toArray()['data']), $prev_count);
    }

    public function test_perPage_dynamic_empty_cursor_defaults_next()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $p = User::cursorPaginate([$prev_count = 2, $next_count = 4], ['*'], [
            'request' => $req = new Request(),
            'path'    => '/',
        ]);

        $this->assertEquals($next_count, $p->perPage());
        $this->assertEquals(count($p->toArray()['data']), $next_count);
    }

    public function test_perPage_dynamic_next()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $p = User::cursorPaginate([$prev_count = 2, $next_count = 4], ['*'], [
            'request' => $req = new Request([
                $next_name => 1,
            ]),
            'path'    => '/',
        ]);

        $this->assertEquals($next_count, $p->perPage());
        $this->assertEquals(count($p->toArray()['data']), $next_count);
    }

    public function test_both_pagination()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $p = User::cursorPaginate(10, ['*'], [
            'request' => $req = new Request([
                $prev_name => $prev_val = 6,
                $next_name => $next_val = 1,
            ]),
            'path'    => '/',
        ]);

        $this->assertEquals(count($p->toArray()['data']), $prev_val - $next_val - 1);
    }

    public function test_sorted_by_date()
    {
        $p = User::orderBy('datetime', 'asc')->cursorPaginate(10, ['*'], [
            'identifier' => 'datetime',
            'path'       => '/',
        ]);

        $this->assertGreaterThanOrEqual(strtotime('last month'), $p->prevCursor());
        $this->assertLessThanOrEqual(strtotime('now'), $p->prevCursor());
        $this->assertGreaterThanOrEqual(strtotime('last month'), $p->nextCursor());
        $this->assertLessThanOrEqual(strtotime('now'), $p->nextCursor());
    }

    public function test_sorted_by_date_on_query()
    {
        $p = \DB::table('users')
            ->orderBy('datetime', 'asc')
            ->cursorPaginate(10, ['*'], [
                'identifier'      => 'datetime',
                'date_identifier' => true,
                'path'            => '/',
            ]);

        $this->assertGreaterThanOrEqual(strtotime('last month'), $p->prevCursor());
        $this->assertLessThanOrEqual(strtotime('now'), $p->prevCursor());
        $this->assertGreaterThanOrEqual(strtotime('last month'), $p->nextCursor());
        $this->assertLessThanOrEqual(strtotime('now'), $p->nextCursor());
    }

    public function test_sorted_by_date_auto_identifier()
    {
        $p = User::orderBy('datetime', 'asc')->cursorPaginate(10, ['*'], ['path' => '/']);

        $this->assertGreaterThanOrEqual(strtotime('last month'), $p->prevCursor());
        $this->assertLessThanOrEqual(strtotime('now'), $p->prevCursor());
        $this->assertGreaterThanOrEqual(strtotime('last month'), $p->nextCursor());
        $this->assertLessThanOrEqual(strtotime('now'), $p->nextCursor());
    }
}
