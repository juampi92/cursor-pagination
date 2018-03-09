<?php

namespace Juampi92\CursorPagination\Tests;

use Illuminate\Http\Request;
use Juampi92\CursorPagination\CursorPaginator;

class UrlDetectionTest extends TestCase
{
    public function test_resolves_urls_on_no_cursor()
    {
        $p = new CursorPaginator($array = [
            (object) ['id' => 1],
            (object) ['id' => 2],
            (object) ['id' => 3],
        ], $perPage = 2, [
            'request' => new Request(),
            'path'    => $path = 'api',
        ]);

        $firstId = $array[0]->id;
        $lastId = $array[$perPage - 1]->id;

        $this->assertTrue($p->isFirstPage());
        $this->assertEquals($p->prevCursor(), 1);

        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $this->assertEquals($p->previousPageUrl(), "$path?$prev_name=$firstId");
        $this->assertEquals($p->nextPageUrl(), "$path?$next_name=$lastId");
    }

    public function test_resolves_no_prev_on_cursor()
    {
        list(, $next_name) = CursorPaginator::cursorQueryNames();

        $p = new CursorPaginator($array = [
            (object) ['id' => 2],
            (object) ['id' => 3],
            (object) ['id' => 4],
        ], $perPage = 2, [
            'request' => new Request([
                $next_name => 1,
            ]),
            'path'    => $path = 'api',
        ]);

        $lastId = $array[$perPage - 1]->id;

        $this->assertFalse($p->isFirstPage());
        $this->assertEquals($p->prevCursor(), null);

        $this->assertEquals($p->previousPageUrl(), null);
        $this->assertEquals($p->nextPageUrl(), "$path?$next_name=$lastId");
    }

    public function test_resolves_with_other_query()
    {
        list(, $next_name) = CursorPaginator::cursorQueryNames();

        $p = new CursorPaginator($array = [
            (object) ['id' => 2],
            (object) ['id' => 3],
            (object) ['id' => 4],
        ], $perPage = 2, [
            'request' => new Request([
                'a'        => 'b',
                $next_name => 1,
            ]),
            'path'    => $path = 'api',
        ]);

        $lastId = $array[$perPage - 1]->id;

        $this->assertEquals($p->nextPageUrl(), "$path?a=b&$next_name=$lastId");
    }

    public function test_resolves_prev_intact_if_no_elements()
    {
        list($prev_name) = CursorPaginator::cursorQueryNames();

        $p = new CursorPaginator($array = [], $perPage = 2, [
            'request' => new Request([
                $prev_name => $prev_query = 1,
            ]),
            'path'    => $path = 'api',
        ]);

        $this->assertEquals($p->previousPageUrl(), "$path?$prev_name=$prev_query");
    }

    public function test_stops_when_no_more_items()
    {
        list(, $next_name) = CursorPaginator::cursorQueryNames();

        $p = new CursorPaginator($array = [
            (object) ['id' => 98],
            (object) ['id' => 99],
        ], $perPage = 2, [
            'request' => new Request([
                $next_name => $next_query = 97,
            ]),
            'path'    => $path = 'api',
        ]);

        $this->assertNotTrue($p->hasMorePages());
        $this->assertEquals($p->nextPageUrl(), null);
        $this->assertEquals($p->previousPageUrl(), null);
    }

    public function test_different_identifier()
    {
        CursorPaginator::cursorQueryNames();

        $p = new CursorPaginator($array = [
            (object) ['id' => 98, '_id' => 1],
            (object) ['id' => 99, '_id' => 2],
            (object) ['id' => 100, '_id' => 3],
        ], $perPage = 2, [
            'request'    => new Request(),
            'identifier' => '_id',
        ]);

        $this->assertEquals($p->nextCursor(), 2);
    }
}
