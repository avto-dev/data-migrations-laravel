<?php

namespace AvtoDev\DataMigrationsLaravel\Contracts;

interface MigratorContract
{
    /**
     * Get migrations repository instance.
     *
     * @return RepositoryContract
     */
    public function repository();

    /**
     * Get the migrations source instance.
     *
     * @return SourceContract
     */
    public function source();
}
