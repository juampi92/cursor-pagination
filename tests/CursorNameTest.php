<?php

namespace Juampi92\CursorPagination\Tests;

use Juampi92\CursorPagination\CursorPaginator;

class CursorNameTest extends TestCase
{
    public function test_camel_case()
    {
        config(['cursor_pagination.transform_name' => 'snake_case']);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $this->assertContains('_', $prev_name);
        $this->assertContains('_', $next_name);

        config(['cursor_pagination.transform_name' => 'camel_case']);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $this->assertNotContains('_', $prev_name);
        $this->assertNotContains('_', $next_name);

        config(['cursor_pagination.transform_name' => 'kebab_case']);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $this->assertContains('-', $prev_name);
        $this->assertContains('-', $next_name);

        config(['cursor_pagination.transform_name' => null]);
    }
}
