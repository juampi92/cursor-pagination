<?php

namespace Juampi92\CursorPagination;

class Cursor
{
    protected $prev = null;
    protected $next = null;
    protected $date_identifier = false;

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
     * @param bool $value
     */
    public function setDateIdentifier($value = true)
    {
        $this->date_identifier = $value;
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

    /**
     * @return mixed
     */
    public function getPrevQuery()
    {
        $prev = $this->getPrevCursor();

        if ($this->date_identifier && is_numeric($prev)) {
            return date('c', $prev);
        }

        return $prev;
    }

    /**
     * @return mixed
     */
    public function getNextQuery()
    {
        $next = $this->getNextCursor();

        if ($this->date_identifier && is_numeric($next)) {
            return date('c', $next);
        }

        return $next;
    }
}
