<?php

namespace Domains\Common\Models;

abstract class PagedListFilters
{
    protected const SORT = [
        'created_at' => 'createdAt',
    ];
    protected const DIR = ['asc', 'desc'];
    protected const MAX_PER_PAGE = 100;
    protected const DEFAULT_PER_PAGE = 10;

    protected int $page;
    protected int $perPage;
    protected string $sortBy;
    protected string $sortDir;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPage(1)
            ->setPerPage(10);

        $this->sortBy = 'createdAt';
        $this->sortDir = 'desc';
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page): self
    {
        $this->page = $page > 0 ? $page : 1;

        return $this;
    }

    /**
     * @param int $perPage
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage > 0 && $perPage <= static::MAX_PER_PAGE ? $perPage : static::DEFAULT_PER_PAGE;

        return $this;
    }

    /**
     * @param string|null $sortBy
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setSortBy(?string $sortBy): self
    {
        if (!$sortBy) {
            return $this;
        }

        if (!array_key_exists($sortBy, static::SORT)) {
            throw new \InvalidArgumentException();
        }

        $this->sortBy = static::SORT[$sortBy];

        return $this;
    }

    /**
     * @param string|null $sortDir
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setSortDir(?string $sortDir): self
    {
        if (!$sortDir) {
            return $this;
        }

        $sortDir = strtolower($sortDir);
        if (!in_array($sortDir, self::DIR)) {
            throw new \InvalidArgumentException();
        }

        $this->sortDir = $sortDir;

        return $this;
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
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * @return string
     */
    public function sortBy(): string
    {
        return $this->sortBy;
    }

    /**
     * @return string
     */
    public function sortDir(): string
    {
        return $this->sortDir;
    }
}
