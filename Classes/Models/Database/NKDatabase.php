<?php
class NKDatabase {
	public $debug 		= false;
	
	protected $_queryCount 	= 0;
	protected $_queryCost 	= 0;
	protected $_connection;

	protected static $_sharedInstance;

	public static function defaultDB() {
		if( self::$_sharedInstance == NULL ) {
			self::$_sharedInstance = new NKDatabase();
		}
		return self::$_sharedInstance;
	}

	// legacy, NetKit 0.1 API
	public static function sharedDatabase() {
		return self::defaultDB();
	}
	
	/**
	 * Create a basic instance and connect to the database
	 * By default we use MySQL as our backend
	 */
	public function __construct() {
		$this->_connection = mysql_connect( 
				Config::databaseHost,
				Config::databaseUsername,
				Config::databasePassword
			);
		if( $this->_connection ) {
			mysql_select_db(Config::databaseName, $this->_connection);
		} else {
			throw new Exception("Unable to connect to MySQL backend ".mysql_error());
		}
	}
	
	/**
	 * Execute a query on the current connection
	 * This function also counts the query
	 * and measures the time needed for querying the database
	 * ( this is useful for profiling your app without a performance
	 * 	  hit like xdebug or XHProf would give you )
	 */
	public function query($query) {
		$this->_queryCount++;
		if( $this->debug ) {
			echo $query;
		}

		// count the query execution time
		$time 	= microtime(); 
		$time 	= explode(" ", $time); 
		$time 	= $time[1] + $time[0]; 
		$time1 	= $time;

		// execute the query
		$result = mysql_query($query, $this->_connection);

		// get the time we needed to execute this query
		$time 	= microtime(); 
		$time 	= explode(" ", $time); 
		$time 	= $time[1] + $time[0]; 
		$time2 	= $time;

		$this->_queryCost += round(($time2-$time1)*1000, 1);


		// when we want to debug our SQL code we can throw exceptions
		// in order to display the error more easily
		if( $result == NULL && $this->debug ) {
			throw new Exception(mysql_error(), 500);
		}
		return $result;
	}
	
	/**
	 * The amount of queries executed during
	 * the lifetime of this object
	 */
	public function queryCount() {
		return $this->_queryCount;
	}
	
	/**
	 * The amount of ms spent in executing queries on the database
	 * a large amount usually means the database is bottlenecking
	 */
	public function queryCost() {
		return $this->_queryCost;
	}

	public static function sanitize($input) {
		if( is_numeric($input) ) {
			return $input; // numbers are already safe
		} else if( is_array($input) || is_object($input) ) {
			return self::sanitize(serialize($input));
		}

		// default behavior
		return mysql_real_escape_string($input);
	}

	//--------------------------------------
	// Legacy code

	public static function exec($query) {
		return self::defaultDB()->query($query);
	}

	public function databaseEngineName() {
		return "MySQL 5";
	}
}