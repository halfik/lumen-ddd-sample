<?php

namespace Domains\Common\Models;

trait SoftDeletableTrait
{
    public ?\DateTimeImmutable $deletedAt = null;

    public function restore(): void
    {
        $this->deletedAt = null;
    }
}
