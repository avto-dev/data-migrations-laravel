<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

interface ExecutorContract
{
    /**
     * Execute migration abstract data.
     *
     * @param mixed       $data
     * @param string|null $connection_name
     *
     * @return bool
     */
    public function execute($data, $connection_name = null);
}
