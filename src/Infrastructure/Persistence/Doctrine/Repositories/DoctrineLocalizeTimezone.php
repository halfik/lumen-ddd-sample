<?php

namespace Infrastructure\Persistence\Doctrine\Repositories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

trait DoctrineLocalizeTimezone
{
    /**
     * @param string|null $timezone
     */
    public function setTimezone(?string $timezone): void
    {
        $_SESSION['timezone'] = $timezone;
    }

    /**
     * @return string
     */
    public function getLocalTimeZone(): string
    {
        return $_SESSION['timezone'] ?? 'UTC';
    }

    /**
     * @param Connection $connection
     * @throws Exception
     */
    public function localizeConnection(Connection $connection): void
    {
        $connection->executeStatement("SET timezone = '" .  $this->getTimeZone() . "';");
    }
}
