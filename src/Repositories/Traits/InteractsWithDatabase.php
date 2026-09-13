<?php

namespace App\Repositories\Traits;

use PDO;

trait InteractsWithDatabase
{
    protected PDO $connection;

    public function setConnection(PDO $connection): void
    {
        $this->connection = $connection;
    }
}