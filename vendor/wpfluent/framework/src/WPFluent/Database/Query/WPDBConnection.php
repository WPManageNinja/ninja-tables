<?php

/*
 * WPDB Connection
 */

namespace NinjaTables\Framework\Database\Query;

use Closure;
use Exception;
use DateTimeInterface;
use NinjaTables\Framework\Foundation\App;
use NinjaTables\Framework\Database\Schema;
use NinjaTables\Framework\Database\QueryException;
use NinjaTables\Framework\Database\Query\Processor;
use NinjaTables\Framework\Database\Query\Expression;
use NinjaTables\Framework\Database\ConnectionInterface;
use NinjaTables\Framework\Database\Events\QueryExecuted;
use NinjaTables\Framework\Database\Query\Builder as QueryBuilder;
use NinjaTables\Framework\Database\Query\MySqlGrammar as QueryGrammar;

class WPDBConnection implements ConnectionInterface
{
    /**
     * $wpdb Global $wpdb instance
     * @var Object
     */
    protected $wpdb;

    /**
     * The name of the connected database.
     *
     * @var string
     */
    protected $database;

    /**
     * The table prefix for the connection.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The query grammar implementation.
     *
     * @var \NinjaTables\Framework\Database\Query\Grammar
     */
    protected $queryGrammar;

    /**
     * The query post processor implementation.
     *
     * @var \NinjaTables\Framework\Database\Query\Processor
     */
    protected $postProcessor;

    /**
     * The number of total transactions.
     *
     * @var int
     */
    protected $transactionCount = 0;

    /**
     * The event dispatcher.
     *
     * @var NinjaTables\Framework\Events
     */
    protected $event = null;

    /**
     * Create a new database connection instance.
     *
     * @param  $wpdb $pdo
     * @param string $database
     * @param string $tablePrefix
     * @param array $config
     * @return void
     */
    public function __construct(
        $pdo, $database = '', $tablePrefix = '', array $config = []
    )
    {
        $this->setupWpdbInstance($pdo);

        // First we will setup the default properties. We keep track of the DB
        // name we are connected to since it is needed when some reflective
        // type commands are run such as checking whether a table exists.
        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        // We need to initialize a query grammar and the query post processors
        // which are both very important parts of the database abstractions
        // so we initialize these to their default values while starting.
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();

        $this->event = App::make('events');
    }

    /**
     * Populate $wpdb instance & turn off db errors
     *
     * @param  $wpdb Global $wpdb instance
     * @return Null
     */
    protected function setupWpdbInstance($wpdb)
    {
        $this->wpdb = $wpdb;

        if (!str_starts_with(App::env(), 'prod')) {
            $this->wpdb->show_errors(false);
        }
    }

    /**
     * Set the query grammar to the default implementation.
     *
     * @return void
     */
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \NinjaTables\Framework\Database\Query\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new QueryGrammar;
    }

    /**
     * Set the query post processor to the default implementation.
     *
     * @return void
     */
    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    /**
     * Get the default post processor instance.
     *
     * @return \NinjaTables\Framework\Database\Query\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor;
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param \Closure|\NinjaTables\Framework\Database\Query\Builder|string $table
     * @param string|null $as
     * @return \NinjaTables\Framework\Database\Query\Builder
     */
    public function table($table, $as = null)
    {
        return $this->query()->from($table, $as);
    }

    /**
     * Get a new query builder instance.
     *
     * @return \NinjaTables\Framework\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $query = $this->bindParams($query, $bindings);

            $result = $this->wpdb->get_row($query);

            if ($result === false || $this->wpdb->last_error) {
                throw new QueryException(
                    $query, $bindings, new Exception($this->wpdb->last_error)
                );
            }

            return $result;
        });
    }

    /**
     * Run a select statement and return the first column of the first row.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return mixed
     *
     * @throws \NinjaTables\Framework\Database\MultipleColumnsSelectedException
     */
    public function scalar($query, $bindings = [], $useReadPdo = true)
    {
        $record = $this->selectOne($query, $bindings, $useReadPdo);

        if (is_null($record)) {
            return null;
        }

        $record = (array)$record;

        if (count($record) > 1) {
            throw new MultipleColumnsSelectedException;
        }

        return reset($record);
    }

    /**
     * Run a select statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $query = $this->bindParams($query, $bindings);

            $result = $this->wpdb->get_results($query);

            if ($result === false || $this->wpdb->last_error) {
                throw new QueryException(
                    $query, $bindings, new Exception($this->wpdb->last_error)
                );
            }

            return $result;
        });
    }

    /**
     * A hacky way to emulate bind parameters into SQL query
     *
     * @param $query
     * @param $bindings
     *
     * @return mixed
     */
    protected function bindParams($query, $bindings, $update = false)
    {
        $query = str_replace('"', '`', $query);

        $bindings = $this->prepareBindings($bindings);

        if (!$bindings) {
            return $query;
        }

        $bindings = array_map(function ($replace) {

            if (is_string($replace)) {
                $replace = "'" . esc_sql($replace) . "'";
            } elseif ($replace === null) {
                $replace = "null";
            }

            return $replace;

        }, $bindings);

        $query = str_replace(array('%', '?'), array('%%', '%s'), $query);

        $query = vsprintf($query, $bindings);

        return $query;
    }

    /**
     * A hacky way to emulate bind parameters into SQL query for mysqli
     * Only used to run a cursor query using the underlying mysqli instance.
     *
     * @param $query
     * @param $bindings
     *
     * @return mixed
     */
    protected function bindParamsForSqli($query, $bindings, $update = false)
    {
        $query = str_replace('"', '`', $query);

        $bindings = $this->prepareBindings($bindings);

        if (!$bindings) {
            return $query;
        }

        $bindings = array_map(function ($replace) {

            if (is_string($replace)) {
                $replace = "'" . esc_sql($replace) . "'";
            } elseif ($replace === null) {
                $replace = "null";
            }

            return $replace;

        }, $bindings);

        $query = vsprintf($query, $bindings);

        return $query;
    }

    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        // When the underlying driver is not the mysqli.
        // it's not a pure cursor just mimicked like one.
        if (!$this->wpdb->dbh instanceof \mysqli) {
            foreach ($this->select($query, $bindings) as $row) {
                yield $row;
            }
            return;
        }

        // The underlying driver is the mysqli
        $this->wpdb->flush();
        $this->wpdb->insert_id = 0;
        $this->wpdb->check_current_query = true;

        if (!$this->wpdb->check_connection()) {
            throw new QueryException(
                $query, $bindings, new Exception(
                    $this->wpdb->last_error || 'Error reconnecting to the database.'
                )
            );
        }

        if (defined('SAVEQUERIES') && SAVEQUERIES) {
            $this->wpdb->timer_start();
        }

        $statement = $this->wpdb->dbh->prepare(
            $this->bindParamsForSqli($query, $bindings)
        );

        $bindings && $statement->bind_param(
            str_repeat('s', count($bindings)),
            ...$bindings
        );

        $start = microtime(true);

        if ($statement->execute()) {

            $result = $statement->get_result();

            $this->wpdb->num_queries++;
            $this->wpdb->last_query = $query;
            $this->wpdb->num_rows = $result->num_rows;

            if (defined('SAVEQUERIES') && SAVEQUERIES) {
                $this->wpdb->log_query(
                    $query,
                    $this->wpdb->timer_stop(),
                    $this->wpdb->get_caller(),
                    $this->wpdb->time_start,
                    []
                );
            }

            $time = $this->getElapsedTime($this->wpdb->time_start);

            $this->event->dispatch(
                new QueryExecuted($query, $bindings, $time, $this)
            );

            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $this->wpdb->last_result[$i] = $row;
                $i++;
                yield $row;
            }

            return;
        }

        if ($statement->error || $statement->errno) {

            $this->wpdb->last_error = __(
                $statement->error || 'Mysqli Error No: ' . $statement->errno
            );

            throw new QueryException(
                $query, $bindings, new Exception(
                    $statement->error || 'Mysqli Error No: ' . $statement->errno
                )
            );
        }
    }

    /**
     * Run an insert statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $query = $this->bindParams($query, $bindings, true);

            $result = $this->unprepared($query);

            if ($result === false || $this->wpdb->last_error) {
                throw new QueryException(
                    $query, $bindings, new Exception($this->wpdb->last_error)
                );
            }

            return $result;
        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $query = $this->bindParams($query, $bindings, true);

            $result = $this->wpdb->query($query);

            if ($result === false || $this->wpdb->last_error) {
                throw new QueryException(
                    $query, $bindings, new Exception($this->wpdb->last_error)
                );
            }

            return intval($result);
        });
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param string $query
     * @return bool
     */
    public function unprepared($query)
    {
        return $this->wpdb->query($query);
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param \Closure $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        // ...
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into
            // the actual date string. Each query grammar maintains its
            // own date string format so we'll just ask the grammar
            // for the format to get from the date.
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int)$value;
            }
        }

        return $bindings;
    }

    public function run($query, $bindings, $callback)
    {
        $start = microtime(true);

        try {
            return $callback($query, $bindings);
        } finally {
            $time = $this->getElapsedTime($start);
            $this->event->dispatch(
                new QueryExecuted($query, $bindings, $time, $this)
            );
        }
    }

    /**
     * Get a new raw query expression.
     *
     * @param mixed $value
     * @return \NinjaTables\Framework\Database\Query\Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Get the query grammar used by the connection.
     *
     * @return \NinjaTables\Framework\Database\Query\Grammar
     */
    public function getQueryGrammar()
    {
        $this->queryGrammar->setTablePrefix($this->wpdb->prefix);

        return $this->queryGrammar;
    }

    /**
     * Set the query grammar used by the connection.
     *
     * @param \NinjaTables\Framework\Database\Query\Grammar $grammar
     * @return $this
     */
    public function setQueryGrammar(Grammar $grammar)
    {
        $this->queryGrammar = $grammar;

        return $this;
    }

    /**
     * Get the query post processor used by the connection.
     *
     * @return \NinjaTables\Framework\Database\Query\Processor
     */
    public function getPostProcessor()
    {
        return $this->postProcessor;
    }

    /**
     * Set the query post processor used by the connection.
     *
     * @param \NinjaTables\Framework\Database\Query\Processor $processor
     * @return $this
     */
    public function setPostProcessor(Processor $processor)
    {
        $this->postProcessor = $processor;

        return $this;
    }

    /**
     * Return the last insert id
     *
     * @param string $args
     *
     * @return int
     */
    public function lastInsertId($args)
    {
        return $this->wpdb->insert_id;
    }

    /**
     * Return self as PDO, the Processor instance uses it.
     *
     * @return \NinjaTables\Framework\Database\Query\WPDBConnection
     */
    public function getPdo()
    {
        return $this;
    }

    /**
     * Returns the $wpdb object.
     *
     * @return Object $wpdb
     */
    public function getWPDB()
    {
        return $this->wpdb;
    }

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->isSqlite() ? 'sqlite' : 'mysql';
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->wpdb->dbname;
    }

    /**
     * Get the server version for the connection.
     *
     * @return string
     */
    public function getServerVersion(): string
    {
        return $this->getWPDB()->db_version();
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param Closure $callback
     * @param int $attempts
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        $this->beginTransaction();
        try {
            $data = $callback();
            $this->commit();
            return $data;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $transaction = $this->unprepared("START TRANSACTION;");

        if (false !== $transaction) {
            $this->transactionCount++;
        }
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactionCount < 1) {
            return;
        }

        $transaction = $this->unprepared("COMMIT;");

        if (false !== $transaction) {
            $this->transactionCount--;
        }
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactionCount < 1) {
            return;
        }

        $transaction = $this->unprepared("ROLLBACK;");

        if ($transaction !== false) {
            $this->transactionCount--;
        }
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactionCount;
    }

    /**
     * Get the column listing for a given table.
     *
     * @param string $table
     * @return array
     */
    public function getColumnListing($table)
    {
        return Schema::getColumns($table);
    }

    /**
     * Alias for getColumnListing.
     *
     * @param  @param  string  $t
     * @return array
     */
    public function getColumns($t)
    {
        return $this->getColumnListing($t);
    }

    /**
     * Determine if the connected database is a MariaDB database.
     *
     * @return bool
     */
    public function isMaria()
    {
        return str_contains(
            $this->getWPDB()->db_server_info(), 'MariaDB'
        );
    }


    /**
     * Determine if the connected database is a sqlite database.
     *
     * @return bool
     */
    public function isSqlite()
    {
        return defined('DB_ENGINE') && DB_ENGINE === 'sqlite';
    }

    /**
     * Register a database query listener with the connection.
     *
     * @param \Closure $callback
     * @return void
     */
    public function listen(Closure $callback)
    {
        $this->event->listen(QueryExecuted::class, $callback);
    }

    /**
     * Get the elapsed time since a given starting point.
     *
     * @param int $start
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
}
