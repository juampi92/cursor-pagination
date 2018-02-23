<?php

namespace Juampi92\CursorPagination;

class Cursor
{
    protected $prev = null;
    protected $next = null;

    /**
     * Cursor constructor.
     *
     * @param null $prev
     * @param null $next
     */
    public function __construct($prev = null, $next = null)
    {
        $this->prev = $prev;
        $this->next = $next;
    }

    /**
     * @return bool
     */
    public function isPresent()
    {
        return $this->isNext() || $this->isPrev();
    }

    /**
     * @return bool
     */
    public function isNext()
    {
        return !is_null($this->next);
    }

    /**
     * @return bool
     */
    public function isPrev()
    {
        return !is_null($this->prev);
    }

    /**
     * @return mixed
     */
    public function getPrevCursor()
    {
        return $this->prev;
    }

    /**
     * @return mixed
     */
    public function getNextCursor()
    {
        return $this->next;
    }
}
