<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Traits;

use PHPUnit\Framework\AssertionFailedError;

/**
 * Trait AdditionalAssertsTrait.
 *
 * Trait with additional asserts methods.
 */
trait AdditionalAssertsTrait
{
    /**
     * Assert that value is array.
     *
     * @param $value
     *
     * @throws AssertionFailedError
     */
    public function assertIsArray($value)
    {
        $this->assertTrue(is_array($value), 'Must be an array');
    }

    /**
     * Assert that value is'n empty string.
     *
     * @param $value
     *
     * @throws AssertionFailedError
     */
    public function assertIsNotEmptyString($value)
    {
        $this->assertIsString($value);
        $this->assertNotEmpty($value);
    }

    /**
     * Assert that value is string.
     *
     * @param $value
     *
     * @throws AssertionFailedError
     */
    public function assertIsString($value)
    {
        $this->assertTrue(is_string($value), 'Must be string');
    }

    /**
     * Assert that database has table.
     *
     * @param string      $table_name
     * @param string|null $connection
     *
     * @throws AssertionFailedError
     *
     * @return mixed
     */
    public function assertTableExists($table_name, $connection = null)
    {
        return $this->assertTrue($this->tableExists($table_name, $connection));
    }

    /**
     * Assert that database has no table.
     *
     * @param string      $table_name
     * @param string|null $connection
     *
     * @throws AssertionFailedError
     *
     * @return mixed
     */
    public function assertTableNotExists($table_name, $connection = null)
    {
        return $this->assertFalse($this->tableExists($table_name, $connection));
    }

    /**
     * @param string      $table_name
     * @param string|null $connection
     *
     * @return mixed
     */
    protected function tableExists($table_name, $connection = null)
    {
        return $this->app->make('db')->connection($connection)->getSchemaBuilder()->hasTable($table_name);
    }
}
