<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Commands;

class MigrateCommandTest extends AbstractCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getCommandSignature()
    {
        return 'data-migrate';
    }
}
