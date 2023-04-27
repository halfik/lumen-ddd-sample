<?php

namespace Domains\AdminPanel\Repositories;

use Domains\Common\Models\Paginator;

interface CompanyUserRepositoryContract
{
    /**
     * @param int         $page
     * @param int         $perPage
     * @param string|null $searchPhrase
     * @return Paginator
     */
    public function list(
        int $page,
        int $perPage,
        ?string $searchPhrase
    ): Paginator;
}
