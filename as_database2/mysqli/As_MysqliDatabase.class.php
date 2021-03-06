<?php

class As_MysqliDatabase extends As_Database
{
	protected $dsn = array(
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		'client_flags' => 0,
	);
	
	protected $parameters = '';
	
	protected $lastError = '';
	
	/**
	 * Construct new Mysql database object and connect to specified DSN, format of:
	 * 
	 * <code>
	 * array(
	 *     'host' => 'localhost',
	 *     'user' => 'nobody',
	 *     'pass' => '',
	 *     'port' => 3306,
	 *     'client_flags' => 0,
	 *     'db_name' => 'default_db_name',
	 * )
	 * </code>
	 * 
	 * Everything is optional.
	 * 
	 * @return void
	 * @author Anthony Bush
	 **/
	public static function constructByConfig($dbConfig)
	{
		return new As_MysqliDatabase($dbConfig);
	}
	
	protected function connect()
	{
		$dbName = isset($this->dsn['db_name']) ? $this->dsn['db_name'] : '';
		$port = isset($this->dsn['port']) && !empty($this->dsn['port']) ? (int) $this->dsn['port'] : null;
		
		// @todo consider using @ (do some testing) and pass error messages to exception
		// instead. Also might try playing with `print_r(error_get_last());`

		// @todo figure out how to add back in client flags in mysqli driver
		$this->connection = mysqli_connect($this->dsn['host'], $this->dsn['user'], $this->dsn['pass'], $dbName, $port, $this->dsn['socket']);
		if (!$this->connection) {
			throw new As_DatabaseConnectException('mysql', $this->dsn["host"], $this->dsn["port"], $this->dsn["user"]);
		}
		
		// select default DB if provided
		if (isset($this->dsn['db_name'])) {
			$this->selectDb($this->dsn['db_name']);
		} else {
			$this->dbName = null;
		}
	}
	
	protected function disconnect()
	{
		if ($this->connection !== false)
		{
			mysqli_close($this->connection);
		}
	}
	
	protected function _selectDb($dbName)
	{
		return mysqli_select_db($this->connection, $dbName);
	}
	
	public function quote($value)
	{
		// Handle special PHP values and SQL Functions
		if ($value === null) {
			return 'NULL';
		} else if ($value === false) {
			return '0';
		} else if ($value === true) {
			return '1';
		} else if ($value instanceof As_SqlFunction) {
			return $value->__toString();
		}
		
		return '"' . mysqli_real_escape_string( $this->connection, $value) . '"';
	}
	
	public function backtick($value)
	{
		return '`' . str_replace('`', '', $value) . '`';
	}
	
	protected function _query($sql)
	{
		$result = mysqli_query($this->connection, $sql);
		if (!$result)
		{
			$this->lastError = mysqli_error($this->connection);
			return $result;
		}
		else if ($result === true)
		{
			return $result;
		}
		else
		{
			$this->lastError = '';
		}
		return new As_MysqliDatabaseResult($result);
	}
	
	public function _queryPreparedStmt($sql, $parameters, $types = '') {
		
		$this->query = $sql;
		$this->parameters = $parameters;
		$stmt = mysqli_stmt_init($this->connection);
		$result = mysqli_stmt_prepare($stmt, $sql);
		if ($result === false)
		{
			$this->lastError = mysqli_stmt_error($stmt);
			return $result;
		}
		else
		{
			$this->lastError = '';
		}
		
		if ($types == '')
		{
			foreach ($parameters as $parameter)
			{
				$types .= 's';
			}
		}
		$params = array($types);
		foreach ($parameters as $key => $parameter)
		{
			$params[] = &$parameters[$key];
		}
		call_user_func_array(array($stmt, 'bind_param'), $params); 
		$result = mysqli_stmt_execute($stmt);
		
		if (!$result) {
			$this->lastError = mysqli_stmt_error($stmt);
			return $result;
		}
		else
		{
			$this->lastError = '';
		}
		return new As_MysqliDatabaseResult($stmt);
	
	}
	
	
	
	public function getNumAffectedRows()
	{
		return mysqli_affected_rows($this->connection);
	}
	
	public function getLastInsertId()
	{
		return mysqli_insert_id($this->connection);
	}
	
	/**
	 * Number of found rows from the last run query.
	 * 
	 * Make sure to put SQL_CALC_FOUND_ROWS immediately after the SELECT in
	 * order for this to work.
	 * 
	 * @return int
	 **/
	public function getNumFoundRows()
	{
		return $this->getResult('SELECT FOUND_ROWS()');
	}
	
	public function getError()
	{
		if ($this->connection) {
			if ($this->isInTransaction()) {
				return 'Transaction Failed with mysql_error: ' . $this->lastError;
			} else {
				return $this->lastError;
			}
		}
	}
	
	public function startTransaction()
	{
		if ($this->transactionCount == 0) {
			$this->query('START TRANSACTION');
		}
		++$this->transactionCount;
	}
	
	public function commit()
	{
		if ($this->transactionCount > 0) {
			--$this->transactionCount;
		}
		// Multiple calls to commit shall run the commit query each time (as was done before).
		if ($this->transactionCount == 0) {
			$this->query('COMMIT');
		}
	}
	
	public function rollback()
	{
		$this->query('ROLLBACK');
		$this->transactionCount = 0;
	}
	
	public function canQueryPreparedStmt()
	{
		return true;
	}
	
	
}

?>