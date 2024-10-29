<?php

namespace NinjaTables\Framework\Database;

class Schema
{
	/**
	 * Get the global $wpdb instance
	 * 
	 * @return global $wpdb instance
	 */
	public static function db()
	{
		return $GLOBALS['wpdb'];
	}

	/**
	 * Get schema/db information
	 *
	 * @return string|array
	 */
	public static function getInfo($key = null)
	{
		$db = static::db();

		$info = [
			'dbname' => $db->dbname,
			'prefix' => $db->prefix,
			'dbhost' => $db->dbhost,
		    'username' => $db->dbuser,
		    'password' => $db->dbpassword,
		    'charset' => $db->charset,
		    'collation' => $db->collate,
		    'tables' => static::getTableList()
		];

		return $key ? $info[$key] : $info;
	}

	/**
	 * Migrates database table(s)
	 * 
	 * @param  string|array $table The table name without prefix
	 *  or an array where each key is table name and value is sql.
	 *  
	 * @param  string $sql Optional
	 * @return mixed
	 */
	public static function migrate($table, $sql = null)
	{
		if (!$sql && is_array($table)) {
			$result = [];
			foreach ($table as $t => $sql) {
				$result = array_merge(
					$result, (array) static::createTable($t, $sql)
				);
			}
			return $result;
		} else {
			return static::createTable($table, $sql);
		}
	}

	/**
	 * Creates a new table if doesn't exist using dbDelta function
	 * 
	 * @param  string $table The table name without the prefix
	 * @param  string $sql   The sql to create table or an absolute path of a
	 * .sql file containing the column definations for creating the new table.
	 * 
	 * @return string Message
	 */
	public static function createTableIfNotExist($table, $sql)
	{
		if (!static::hasTable($table)) {
			return static::createTable($table, $sql);
		}
	}

	/**
	 * Checks if a column exists in a table
	 * 
	 * @param  string $column The column name of the table
	 * @param  string $table The table name without prefix
	 * @return boolean
	 */

	public static function hasColumn($column, $table)
    {
        $wpdb = static::db();

		$table = static::table($table);

        $columns = $wpdb->get_col("DESCRIBE $table");

        return in_array($column, $columns);
    }

	/**
	 * Checks if a table exists
	 * 
	 * @param  string $table The table name without prefix
	 * @return boolean
	 */
	public static function hasTable($table)
	{
		$wpdb = static::db();

		$table = static::table($table);

		$result = $wpdb->get_var("SHOW TABLES LIKE '" . $table . "'") == $table;

		if ($result) {
			return $result;
		}

		// Check if any temporary table exists by this table name.

		// At first, store the original state of the error suppress
		// error and then turn off the error from being shown, so
		// error will be not shown if there is no temporary
		// table, then restore the error state.
		$isErrorSuppressed = $wpdb->suppress_errors;
		
		$wpdb->suppress_errors = true;

		$result = static::query("SELECT 1 FROM %{$table}% WHERE 0");

		$wpdb->suppress_errors = $isErrorSuppressed;

		return $result === 0;
	}

	/**
	 * Resolves the table prefix and makes the table name with prefix
	 * 
	 * @param  string $table The table name without the prefix
	 * @return string The resolved table name with the prefix
	 */
	public static function table($table)
	{
		$wpdb = static::db();
		
		$prefix = $wpdb->prefix;

		if (strpos($table, $prefix) === 0) {
			return $table;
		}

		return isset($wpdb->{$table}) ? $wpdb->{$table} : ($wpdb->prefix.$table);
	}


    /**
     * Resolves the sql prefix
     *
     * @param string $sql The file name or the raw sql
     * @return string The resolved sql
     */
    public static function sql($sql = '')
    {
        $allowedSqlFileFormats = [
            'sql'
        ];

        foreach ($allowedSqlFileFormats as $format) {
            if (str_ends_with($sql, '.' . $format)) {
                $sql = @file_exists($sql) ? file_get_contents($sql) : $sql;
                break;
            }
        }
        
        return $sql;
    }

	/**
	 * Creates a new table using dbDelta function or alters the table if exists.
	 * 
	 * @param  string $table The table name without the prefix
	 * @param  string $sql   The sql to create table or an absolute path of a
	 * .sql file containing the column definations for creating the new table.
	 * 
	 * @return string message
	 */
	public static function createTable($table, $sql)
	{
		$table = static::table($table);

		$sql = static::sql($sql);

        $collate = static::db()->get_charset_collate();

        return static::callDBDelta(
        	$table, "CREATE TABLE $table (
        		".PHP_EOL.trim(trim($sql), ',').PHP_EOL."
        	) $collate;"
        );
	}

	/**
	 * Alters an existing table if exists
	 * 
	 * @param  string $table The table name without the prefix
	 * @param    string $sql   The sql to create table or an absolute path of a
	 * .sql file containing the column definations for creating the new table.
	 * 
	 * @return string message
	 */
	public static function alterTableIfExists($table, $sql)
	{
		if (static::hasTable($table)) {
			return static::alterTable($table, $sql);
		}
	}

	/**
	 * Alters an existing table
	 * 
	 * @param  string $table The table name without the prefix
	 * @param  string $sql   The sql to create table or an absolute path of a
	 * .sql file containing the column definations for creating the new table.
	 * 
	 * @return string message
	 */
	public static function alterTable($table, $sql)
	{
		$table = static::table($table);

		$sql = static::sql($sql);

		$sql = array_map(function($i) { return trim($i);}, explode(',', $sql));
        
        $sql = "ALTER TABLE $table ".PHP_EOL.rtrim(
        	trim(implode(','.PHP_EOL, $sql)), ';'
        ).";";

        return static::query($sql);
	}

	/**
	 * Alters an existing table using dbDelta function if exists, otherwise creates
	 * it. Alters an existing table but takes the table creation column defination.
	 * This is because, the dbDelta functioin can create or update a table using 
	 * the table creation defination. In this, case, if a table exists and the
	 * columns are matched then nothing happens but if there's any difference
	 * in the new sql then the dbDelta alters the table using the new sql
	 * defination but doesn't delete any columns. So, after the dbDelta
	 * finishes it's job, any non-existing columns in the new sql
	 * defination will be deleted from the existing table. if
	 * table is not there then the table gets created.
	 * 
	 * @param  string $table The table name without the prefix
	 * @param  string $sql   The sql to create table or an absolute path of a
	 * .sql file containing the column definations for creating the new table.
	 * 
	 * @return string message
	 */
	public static function updateTable($table, $sql)
	{
		$result = static::createTable(
			$table, implode(",\n", array_map('trim', explode(',', $sql)))
		);

		if ($existingColumns = static::getColumns($table)) {

			$columns = array_map(function($l) {
				if (preg_match('/(\S+)/', $l, $matches)) return $matches[1];
			}, array_map('trim', explode(',', $sql)));

			foreach ($existingColumns as $column) {
				if (!in_array($column, $columns)) {
					$tbl = static::table($table);
					static::query("alter table $tbl drop column $column");
					$tblColumn = $tbl.'.'.$column;
					$result[$tblColumn] = "Dropped column {$tblColumn}";
				}
			}
		}

		return $result;
	}

    /**
     * Drops/deletes an existing table if exists
     *
     * @param string $table The table name without the prefix
     * @param bool $disableForeignKeyCheck Optional. Whether to disable foreign key checks before dropping the table. Default is true.     *
     * @return bool
     */

    public static function dropTableIfExists($table, $disableForeignKeyCheck = true)
    {
        if (static::hasTable($table)) {
            if ($disableForeignKeyCheck) {
                static::db()->query("ALTER TABLE " . static::table($table) . " DISABLE KEYS;");
            }
            return static::db()->query('DROP TABLE ' . static::table($table));
        }
    }

	/**
	 * Truncate a table.
	 * 
	 * @param  string $table
	 * @return bool
	 */
	public static function truncate($table)
	{
		$table = static::table($table);

		return static::db()->query("TRUNCATE TABLE $table");
	}

	/**
	 * Truncate a table if exists.
	 * 
	 * @param  string $table
	 * @return bool
	 */
	public static function truncateTableIfExists($table)
	{
		if (static::hasTable($table)) {
			return static::truncate($table);
		}
	}

	/**
	 * Adds a new  index to a column of given table.
	 * 
	 * @param string $table Table name
	 * @param string $index Columns name
	 * @return bool
	 * @see https://developer.wordpress.org/reference/functions/add_clean_index
	 */
	public static function addIndex($table, $index)
	{
		return add_clean_index(static::table($table), $index);
	}

	/**
	 * Drops an index from a column of given table.
	 * 
	 * @param  string $table Table name
	 * @param  string $index Columns name
	 * @return bool
	 * @see https://developer.wordpress.org/reference/functions/drop_index
	 */
	public static function dropIndex($table, $index)
	{
		return drop_index(static::table($table), $index);
	}

	/**
	 * Makes raw query and can resolve the table name from the query
	 * and can form a full table name including the table prefix if
	 * the table name is wrapped like: %table_name% in the query.
	 * 
	 * @param  straing $query
	 * @return mixed
	 */
	public static function query($query)
	{
		if (preg_match('/%.*%/', $query, $m)) {
			$query = str_replace(
				$m[0], static::table(trim($m[0], '%')), $query
			);
		}

		return static::db()->query($query);
	}

	/**
	 * Get a list of all columns from the given table name.
	 * 
	 * @param  string $table The table name without the prefix
	 * @return array
	 */
	public static function getColumns($table)
	{
		if (static::hasTable($table)) {
			return static::db()->get_col(
	            'DESC ' . static::table($table), 0
	        );
		}
	}

	/**
	 * Gets a list of all columns including column information
	 * 
	 * @param  string $table The table name without the prefix
	 * @return array
	 */
	public static function getColumnsWithTypes($table)
	{
		if (!static::hasTable($table)) return;
		
		$db = static::db()->dbname;
		$table = static::table($table);
		$fields = [
			'COLUMN_NAME',
			'ORDINAL_POSITION',
			'COLUMN_DEFAULT',
			'IS_NULLABLE',
			'DATA_TYPE',
			'CHARACTER_MAXIMUM_LENGTH',
			'NUMERIC_PRECISION',
			'NUMERIC_SCALE',
			'COLUMN_KEY',
			'EXTRA',
		];
		
		$sql = "SELECT " . implode(',', $fields) . " FROM INFORMATION_SCHEMA.COLUMNS";
		$sql .= " WHERE TABLE_NAME = '".$table."' AND TABLE_SCHEMA = '".$db."'";

		return array_map(function($i) {
			$item = [];
			foreach ((array) $i as $key => $value) {
				$item[strtolower($key)] = $value;
			}
			return $item;
		}, static::db()->get_results($sql));
	}

	/**
	 * Retrieves the list of all available tables in the database.
	 * 
	 * @param  string $dbname optional
	 * @return array
	 */
	public static function getTableList($dbname = null)
	{
		$dbname = $dbname ?: static::db()->dbname;
		$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES";
		$sql .= " WHERE TABLE_SCHEMA = '".$dbname."'";
		return array_map(function($i) {
			return $i->TABLE_NAME;
		}, static::db()->get_results($sql));
	}

	/**
	 * The wrapper for calling dbDelta function
	 * 
	 * @param  string $sql
	 * @return mixed
	 */
	protected static function callDBDelta($table, $sql)
	{
		if (!function_exists('dbDelta')) {
			require (ABSPATH . 'wp-admin/includes/upgrade.php');
		}

		return dbDelta($sql);
	}
}
