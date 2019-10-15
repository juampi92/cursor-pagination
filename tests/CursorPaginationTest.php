<?php

namespace Juampi92\CursorPagination\Tests;

use Juampi92\CursorPagination\CursorPaginator;

class CursorPaginationTest extends TestCase
{
    public function test_init()
    {
        $p = new CursorPaginator($array = [
            ['id' => 1], ['id' => 2], ['id' => 3], ['id' => 4], ['id' => 5],
        ], $perPage = 2);

        $this->assertEquals($perPage, $p->perPage());
    }

    public function test_overflow_pagination()
    {
        $p = new CursorPaginator($array = [
            ['id' => 1], ['id' => 2], ['id' => 3], ['id' => 4], ['id' => 5],
        ], $perPage = 2);

        $this->assertEquals($p->items(), [
            $array[0], $array[1],
        ]);
    }

    public function test_next_cursor()
    {
        $p = new CursorPaginator($array = [
            (object) ['id' => 1],
            (object) ['id' => 2],
            (object) ['id' => 3],
            (object) ['id' => 4],
            (object) ['id' => 5],
        ], $perPage = 3);

        $this->assertEquals($p->nextCursor(), 3);
        $this->assertEquals($p->prevCursor(), 1);
    }

    public function test_empty_next_if_no_more()
    {
        $p = new CursorPaginator($array = [
            (object) ['id' => 1],
            (object) ['id' => 2],
            (object) ['id' => 3],
        ], $perPage = 3);

        $this->assertEquals($p->nextCursor(), null);
    }
}
