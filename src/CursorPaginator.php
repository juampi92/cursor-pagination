<?php

namespace Juampi92\CursorPagination;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use IteratorAggregate;
use JsonSerializable;

class CursorPaginator extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable, PaginatorContract
{
    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    protected $hasMore;

    /**
     * @var string
     */
    protected $identifier = 'id';

    /**
     * Should cast to date the identifier.
     *
     * @var bool
     */
    protected $date_identifier = false;

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var Cursor
     */
    protected $cursor = null;

    /**
     * @var array|null
     */
    protected $cursor_queue_names = null;

    /**
     * Create a new paginator instance.
     *
     * @param mixed $items
     * @param int   $perPage
     * @param array $options
     */
    public function __construct($items, $perPage, array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = $perPage;

        if (is_null($this->request)) {
            $this->request = request();
        }

        $this->cursor = self::resolveCurrentCursor($this->request);

        $this->query = $this->getRawQuery();
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : rtrim($this->request->path(), '/');

        $this->setItems($items);
    }

    /**
     * Set the items for the paginator.
     *
     * @param mixed $items
     *
     * @return void
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = $this->items->count() > $this->perPage;

        $this->items = $this->items->slice(0, $this->perPage);
    }

    /**
     * @param Request|null $request
     *
     * @return Cursor
     */
    public static function resolveCurrentCursor(Request $request = null)
    {
        $request = $request ?? request();
        list($prev_name, $next_name) = self::cursorQueryNames();

        $req_prev = $request->input($prev_name);
        $req_next = $request->input($next_name);

        return new Cursor($req_prev, $req_next);
    }

    /**
     * @return array
     */
    public static function cursorQueryNames()
    {
        $ident = config('cursor_pagination.identifier_name');
        $ident = ucfirst($ident);
        list($prev, $next) = config('cursor_pagination.navigation_names');

        return [
            self::formatNames("{$prev}$ident"),
            self::formatNames("{$next}$ident"),
        ];
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected static function formatNames($name)
    {
        $transform_name = config('cursor_pagination.transform_name', null);

        if (!is_null($transform_name)) {
            $str_method = Str::replaceLast('_case', '', $transform_name);

            return Str::$str_method($name);
        }

        return $name;
    }

    /**
     * @return array
     */
    public function getCursorQueryNames()
    {
        if (is_null($this->cursor_queue_names)) {
            $this->cursor_queue_names = static::cursorQueryNames();
        }

        return $this->cursor_queue_names;
    }

    public function nextCursor()
    {
        return $this->hasMorePages() ? $this->lastItem() : null;
    }

    /**
     * The URL for the next page, or null.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        list($prev, $next) = $this->getCursorQueryNames();

        if ($this->nextCursor()) {
            $query = [
                $next => $this->nextCursor(),
            ];

            if ($this->cursor->isPrev()) {
                $query[$prev] = $this->cursor->getPrevCursor();
            }

            return $this->url($query);
        }
    }

    /**
     * @return string|null
     */
    public function prevCursor()
    {
        if ($this->isFirstPage()) {
            return ($this->cursor->isPrev() && $this->isEmpty()) ?
                $this->cursor->getPrevCursor() :
                $this->firstItem();
        }
    }

    /**
     * @return null|string
     */
    public function previousPageUrl()
    {
        list($prev) = $this->getCursorQueryNames();

        if ($pre_cursor = $this->prevCursor()) {
            return $this->url([
                $prev => $pre_cursor,
            ]);
        }
    }

    /**
     * Returns the request query without the cursor parameters.
     *
     * @return array
     */
    protected function getRawQuery()
    {
        list($prev, $next) = $this->getCursorQueryNames();

        return collect($this->request->query())
            ->diffKeys([
                $prev => true,
                $next => true,
            ])->all();
    }

    /**
     * @param array $cursor
     *
     * @return string
     */
    public function url($cursor = [])
    {
        $query = array_merge($this->query, $cursor);

        return $this->path
            .(Str::contains($this->path, '?') ? '&' : '?')
            .http_build_query($query, '', '&')
            .$this->buildFragment();
    }

    /**
     * Determine if there is more items in the data store.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->hasMore;
    }

    /**
     * @return bool
     */
    public function isFirstPage()
    {
        return !$this->cursor->isNext();
    }

    /**
     * Return the first identifier of the results.
     *
     * @return mixed
     */
    public function firstItem()
    {
        return $this->getIdentifier($this->items->first());
    }

    /**
     * Return the last identifier of the results.
     *
     * @return mixed
     */
    public function lastItem()
    {
        return $this->getIdentifier($this->items->last());
    }

    /**
     * Gets and casts identifier.
     *
     * @param $model
     *
     * @return mixed
     */
    protected function getIdentifier($model)
    {
        if (!isset($model)) {
            return;
        }

        $id = $model->{$this->getIdentifierName()};

        if (!$this->isDateIdentifier($id)) {
            return $id;
        }

        return $this->parseDateIdentifier($id);
    }

    protected function getIdentifierName(): string
    {
        return $this->identifier_alias ?? $this->identifier;
    }

    /**
     * @param $id
     *
     * @return int
     */
    protected function parseDateIdentifier($id): int
    {
        return Carbon::parse($id)->timestamp;
    }

    /**
     * Will check if the identifier is type date.
     *
     * @param $id
     *
     * @return bool
     */
    protected function isDateIdentifier($id): bool
    {
        return $this->date_identifier || $id instanceof Carbon;
    }

    /**
     * Render the paginator using a given view.
     *
     * @param string|null $view
     * @param array       $data
     *
     * @return string
     */
    public function render($view = null, $data = [])
    {
        // No render method
        return '';
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        list($prev, $next) = $this->getCursorQueryNames();

        return [
            'data'          => $this->items->toArray(),
            'path'          => $this->url(),
            $prev           => self::castCursor($this->prevCursor()),
            $next           => self::castCursor($this->nextCursor()),
            'per_page'      => (int) $this->perPage(),
            'next_page_url' => $this->nextPageUrl(),
            'prev_page_url' => $this->previousPageUrl(),
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @param mixed $val
     *
     * @return null|string
     */
    protected static function castCursor($val = null)
    {
        if (is_null($val)) {
            return $val;
        }

        return (string) $val;
    }
}
