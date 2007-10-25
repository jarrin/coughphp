<?php
/**
 * Default DatabaseSchemaGenerator configuration options.
 * 
 * You don't have to pass these to the schema generator as it will use
 * reasonable defaults. The are replicated here to make them easy to change.
 *
 * @package CoughPHP
 * @author Anthony Bush
 **/


$config = array(
	// REQUIRED CONFIG
	
	// All databases will be scanned unless specified in the 'databases' parameter in the OPTIONAL CONFIG SECTION.
	'dsn' => array(
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		'driver' => 'mysql'
	),
	
	// OPTIONAL ADDITIONAL CONFIG
	
	'table_settings' => array(
		// This match setting is so the database scanner can resolve relationships better, e.g. know that when it sees "ticket_id" that a "wfl_ticket" table is an acceptable match.
		'match_table_name_prefixes' => array(), // Example: array('cust_', 'wfl_', 'baof_'),
		// You can ignore tables all together, too:
		'ignore_tables_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
	),
	
	'field_settings' => array(
		// In case of non FK detection, you can have the Database Schema Generator check for ID columns matching this regex.
		// This is useful, for example, when no FK relationships set up)
		'id_to_table_regex' => '/^(.*)_id$/',
	),
	
);

?>