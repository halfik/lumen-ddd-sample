<?php

namespace Domains\Common\Models;

class Paginator
{
    private array $items;
    private int  $page;
    private int $perPage;
    private int $total;

    /**
     * @param array $items
     * @param int   $total
     * @param int   $page
     * @param int   $perPage
     */
    public function __construct(array $items, int $total, int $page, int $perPage)
    {
        $this->items = $items;
        $this->page = $page;
        $this->total = $total;
        $this->perPage = $perPage > 0 ? $perPage : 1;
    }

    /**
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function page(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function lastPage(): int
    {
        return ceil($this->total() / $this->perPage());
    }

    /**
     * @return bool
     */
    public function isOnLastPage(): bool
    {
        return $this->page() >= $this->lastPage();
    }

    /**
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function total(): int
    {
        return $this->total;
    }
}
