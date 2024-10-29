<?php

namespace NinjaTables\Framework\Database;

interface ConnectionResolverInterface
{
    /**
     * Get a database connection instance.
     *
     * @param  string|null  $name
     * @return \NinjaTables\Framework\Database\ConnectionInterface
     */
    public function connection($name = null);

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection();

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name);
}
