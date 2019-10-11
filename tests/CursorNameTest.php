<?php

namespace Juampi92\CursorPagination\Tests;

use Juampi92\CursorPagination\CursorPaginator;

class CursorNameTest extends TestCase
{
    public function test_camel_case()
    {
        config(['cursor_pagination.transform_name' => 'snake_case']);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $this->assertStringContainsString('_', $prev_name);
        $this->assertStringContainsString('_', $next_name);

        config(['cursor_pagination.transform_name' => 'camel_case']);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $this->assertStringNotContainsString('_', $prev_name);
        $this->assertStringNotContainsString('_', $next_name);

        config(['cursor_pagination.transform_name' => 'kebab_case']);
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $this->assertStringContainsString('-', $prev_name);
        $this->assertStringContainsString('-', $next_name);

        config(['cursor_pagination.transform_name' => null]);
    }
}
