<?php

namespace Juampi92\CursorPagination\Tests;

use Illuminate\Http\Request;
use Juampi92\CursorPagination\Cursor;
use Juampi92\CursorPagination\CursorPaginator;

class CursorTest extends TestCase
{
    public function test_basic_init()
    {
        $cursor = CursorPaginator::resolveCurrentCursor();

        $this->assertInstanceOf(Cursor::class, $cursor);
    }

    public function test_resolves_default_cursor()
    {
        $req = new Request([]);

        $cursor = CursorPaginator::resolveCurrentCursor($req);

        $this->assertFalse($cursor->isNext());
        $this->assertFalse($cursor->isPrev());
        $this->assertFalse($cursor->isPresent());
    }

    public function test_resolves_prev_cursor()
    {
        $get_prev = config('cursor_pagination.navigation_names')[0].'_'.
            config('cursor_pagination.identifier_name');

        $val = 2;

        $req = new Request([
            $get_prev => $val,
        ]);

        $cursor = CursorPaginator::resolveCurrentCursor($req);

        $this->assertEquals($val, $cursor->getPrevCursor(), 'Cursor\'s nav should be prev');
        $this->assertFalse($cursor->isNext(), 'is not next');
        $this->assertTrue($cursor->isPrev(), 'is prev');
        $this->assertTrue($cursor->isPresent(), 'is present');
    }

    public function test_resolves_next_cursor()
    {
        $get_next = config('cursor_pagination.navigation_names')[1].'_'.
            config('cursor_pagination.identifier_name');

        $val = 3;

        $req = new Request([
            $get_next => $val,
        ]);

        $cursor = CursorPaginator::resolveCurrentCursor($req);

        $this->assertEquals($val, $cursor->getNextCursor(), "Cursor's value should be $val");
        $this->assertTrue($cursor->isNext(), 'is next');
        $this->assertTrue(!$cursor->isPrev(), 'is not prev');
        $this->assertTrue($cursor->isPresent(), 'is present');
    }

    public function test_both_cursor()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $prev_val = 3;
        $next_val = 1;

        $req = new Request([
            $prev_name => $prev_val,
            $next_name => $next_val,
        ]);

        $cursor = CursorPaginator::resolveCurrentCursor($req);

        $this->assertEquals($prev_val, $cursor->getPrevCursor());
        $this->assertEquals($next_val, $cursor->getNextCursor());
        $this->assertTrue($cursor->isNext(), 'is next');
        $this->assertTrue($cursor->isPrev(), 'is prev');
    }
}
