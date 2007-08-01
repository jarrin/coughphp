<?php

/**
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package Schema
 * @author Anthony Bush
 * @copyright Anthony Bush (http://anthonybush.com/), 2006-08-26
 **/
class SchemaTable {
	
	protected $database = null; // reference to parent
	protected $tableName = null;
	protected $columns = array();
	
	// Getters
	
	public function getDatabase() {
		return $this->database;
	}
	
	public function getTableName() {
		return $this->tableName;
	}
	
	public function getColumns() {
		return $this->columns;
	}
	
	public function getColumn($columnName) {
		if (isset($this->columns[$columnName])) {
			return $this->columns[$columnName];
		} else {
			return null;
		}
	}
	
	public function getPrimaryKey() {
		$primaryKey = array();
		foreach ($this->columns as $columnName => $column) {
			if ($column->isPrimaryKey()) {
				$primaryKey[$columnName] = $column;
			}
		}
		return $primaryKey;
	}
	
	// Setters
	
	public function setDatabase($database) {
		$this->database = $database;
	}
	
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}
	
	public function addColumn($column) {
		$this->columns[$column->getColumnName()] = $column;
	}
	
}

?>