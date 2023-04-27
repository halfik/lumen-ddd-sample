<?php

namespace Domains\AdminPanel\Models\Sales;

use Domains\AdminPanel\Models\Accounts\CompanyAccount;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;

class Workflow extends AggregateRoot
{
    private CompanyAccount $companyAccount;
    private string $name;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param AggregateRootId $id
     * @param string          $name
     * @param CompanyAccount  $companyAccount
     */
    private function __construct(AggregateRootId $id, string $name, CompanyAccount $companyAccount)
    {
        parent::__construct($id);
        $this->name = $name;
        $this->companyAccount = $companyAccount;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return CompanyAccount
     */
    public function companyAccount(): CompanyAccount
    {
        return $this->companyAccount;
    }
}
