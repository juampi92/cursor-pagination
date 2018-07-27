<?php

namespace Juampi92\CursorPagination\Tests;

use Juampi92\CursorPagination\CursorPaginator;

class RequestTest extends ModelsTestCase
{
    /** @test */
    public function test_a()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();

        $response = $this->get('/test/one/?asd=1');

        $response->assertJsonFragment(['prev_page_url' => "test/one?asd=1&$prev_name=1"]);
        $response->assertJsonFragment(['next_page_url' => "test/one?asd=1&$next_name=5"]);
    }

    /** @test */
    public function test_resource()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();
        $prev_cur = 1;
        $next_cur = 5;

        $response = $this->get('/test/resource');

        $response->assertJsonFragment(['links' => [
            'first' => null,
            'last'  => null,
            'prev'  => "test/resource?$prev_name=$prev_cur",
            'next'  => "test/resource?$next_name=$next_cur",
        ]]);

        $response->assertJsonFragment([
            'meta' => [
                'path'            => 'test/resource?',
                'next_cursor'     => (string) $next_cur,
                'per_page'        => 5,
                'previous_cursor' => (string) $prev_cur,
            ],
        ]);
    }

    /** @test */
    public function test_prev_nav()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();
        $prev_cur = 1;

        $response = $this->get("/test/resource?$prev_name=$prev_cur");

        $response->assertJsonFragment(['links' => [
            'first' => null,
            'last'  => null,
            'prev'  => "test/resource?$prev_name=$prev_cur",
            'next'  => null,
        ]]);

        $response->assertJsonFragment([
            'meta' => [
                'path'            => 'test/resource?',
                'next_cursor'     => null,
                'per_page'        => 5,
                'previous_cursor' => (string) $prev_cur,
            ],
        ]);
    }

    public function test_more_middle_pagination()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();
        $prev_cur = 10;
        $prev_cur_added = $prev_cur - 5;
        $next_cur = 0;
        $first = 1;

        $response = $this->get("/test/resource?$prev_name=$prev_cur");

        $response->assertJsonFragment(['links' => [
            'first' => null,
            'last'  => null,
            'prev'  => "test/resource?$prev_name=$first",
            'next'  => "test/resource?$next_name=$prev_cur_added&$prev_name=$prev_cur",
        ]]);

        $response->assertJsonFragment([
            'meta' => [
                'path'            => 'test/resource?',
                'next_cursor'     => (string) $prev_cur_added,
                'per_page'        => 5,
                'previous_cursor' => (string) $first,
            ],
        ]);

        $data = json_decode($response->getOriginalContent())->data;
        $ids = collect($data)->pluck('_id')->all();
        $this->assertEquals($ids, range($next_cur + 1, min($next_cur + 5, $prev_cur - 1)));
    }

    public function test_prev_is_still_present()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();
        $prev_cur = 10;
        $next_cur = 2;
        $next_cur_after = $next_cur + 5;

        $response = $this->get("/test/resource?$prev_name=$prev_cur&$next_name=$next_cur");

        $response->assertJsonFragment(['links' => [
            'first' => null,
            'last'  => null,
            'prev'  => null,
            'next'  => "test/resource?$next_name=$next_cur_after&$prev_name=$prev_cur",
        ]]);

        $response->assertJsonFragment([
            'meta' => [
                'path'            => 'test/resource?',
                'next_cursor'     => (string) $next_cur_after,
                'per_page'        => 5,
                'previous_cursor' => null,
            ],
        ]);

        $data = json_decode($response->getOriginalContent())->data;
        $ids = collect($data)->pluck('_id')->all();
        $this->assertEquals($ids, range($next_cur + 1, min($next_cur + 5, $prev_cur - 1)));
    }

    /** @test */
    public function test_surroundings_finished()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();
        $prev_cur = 10;
        $next_cur = 7;

        $response = $this->get("/test/resource?$prev_name=$prev_cur&$next_name=$next_cur");

        $response->assertJsonFragment(['links' => [
            'first' => null,
            'last'  => null,
            'prev'  => null,
            'next'  => null,
        ]]);

        $response->assertJsonFragment([
            'meta' => [
                'path'            => 'test/resource?',
                'next_cursor'     => null,
                'per_page'        => 5,
                'previous_cursor' => null,
            ],
        ]);

        $data = json_decode($response->getOriginalContent())->data;
        $ids = collect($data)->pluck('_id')->all();
        $this->assertEquals($ids, range($next_cur + 1, min($next_cur + 5, $prev_cur - 1)));
    }

    /** @test */
    public function test_inverted_order()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();
        $next_cur = 36;

        $response = $this->get("/test/inverse?$next_name=$next_cur");

        $response->assertJsonFragment([$prev_name => null]);
        $response->assertJsonFragment([$next_name => (string) ($next_cur - 5)]);
        $response->assertJsonFragment(['per_page' => config('cursor_pagination.per_page')]);

        $data = json_decode($response->getOriginalContent())->data;
        $ids = collect($data)->pluck('_id')->all();
        $this->assertEquals($ids, array_reverse(range($next_cur - 5, $next_cur - 1)));
    }

    public function test_on_query()
    {
        list($prev_name, $next_name) = CursorPaginator::cursorQueryNames();
        $next_cur = 36;

        $response = $this->get("/test/query_inverse?$next_name=$next_cur");

        $response->assertJsonFragment([$prev_name => null]);
        $response->assertJsonFragment([$next_name => (string) ($next_cur - 5)]);

        $data = json_decode($response->getOriginalContent())->data;
        $ids = collect($data)->pluck('_id')->all();
        $this->assertEquals($ids, array_reverse(range($next_cur - 5, $next_cur - 1)));
    }
}
