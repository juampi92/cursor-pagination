<?php

namespace Juampi92\CursorPagination\Tests;

use Juampi92\CursorPagination\CursorPaginator;

class CursorNameTest extends TestCase
{
    public function test_camel_case()
    {
        config(['cursor_pagination.camel_case' => false]);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames(true);

        $this->assertContains('_', $prev_name);
        $this->assertContains('_', $next_name);

        config(['cursor_pagination.camel_case' => true]);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames(true);

        $this->assertNotContains('_', $prev_name);
        $this->assertNotContains('_', $next_name);

        config(['cursor_pagination.camel_case' => false]);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames(true);
    }
}
