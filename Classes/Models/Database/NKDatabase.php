<?php
class NKDatabase
{
	public $debug 		= false;
	
	protected $_queryCount 	= 0;
	protected $_queryCost 	= 0;
	protected $_connection;

	protected static $_sharedInstance;

	public static function defaultDB()
	{
		if( self::$_sharedInstance == NULL )
		{
			self::$_sharedInstance = new NKDatabase();
		}
		return self::$_sharedInstance;
	}

	// legacy, NetKit 0.1 API
	public static function sharedDatabase()
	{
		return self::defaultDB();
	}
	
	public function __construct($alternativeConnection = NULL)
	{
		if( $alternativeConnection )
		{
			$this->_connection = new mysqli(
				$alternativeConnection['host'],
				$alternativeConnection['username'],
				$alternativeConnection['password'],
				$alternativeConnection['database']
			);
		}
		else
		{
			$this->_connection = new mysqli(
				Config::databaseHost,
				Config::databaseUsername,
				Config::databasePassword,
				Config::databaseName
			);
		}
		
		if( $this->_connection )
		{
			// success
		}
		else
		{
			throw new Exception("Unable to connect to MySQL backend");
		}
	}
	
	/**
	 * the function query executes the given query
	 * on the given database connection and returns the result
	 *
	 * @param string $query the query string
	 * @return resource
	 */
	public function query($query)
	{
		$this->_queryCount++;
		$time 	= microtime(); 
		$time 	= explode(" ", $time); 
		$time 	= $time[1] + $time[0]; 
		$time1 	= $time;
		if( $this->debug )
		{
			echo $query;
		}
		$result = $this->_connection->query($query);
		if( $result == NULL )
		{
			return;
		}
		$time 	= microtime(); 
		$time 	= explode(" ", $time); 
		$time 	= $time[1] + $time[0]; 
		$time2 	= $time;
		$this->_queryCost += round(($time2-$time1)*1000, 1);
		return $result;
	}
	
	public static function exec($query)
	{
		if( !self::$_sharedInstance )
		{
			self::defaultDB();
		}
		return self::$_sharedInstance->query($query);
	}
	
	/**
	 * Begins a transaction
	 *
	 * @return void
	 */
	public function beginTransaction()
	{
		$this->_connection->begin_transaction();
	}
	
	/**
	 * Ends a transaction
	 *
	 * @return bool
	 */
	public function commit()
	{
		return $this->_connection->commit();
	}
	
	/**
	 * Cancels a transaction
	 *
	 * @return boolean
	 */
	public function rollback()
	{
		return $this->_connection->rollback();
	}
	
	/**
	 * Returns the last inserted row ID
	 *
	 * @return integer last inserted row ID
	 */
	public function lastInsertID()
	{
		return $this->_connection->insert_id;
	}
	
	/**
	 * The amount of queries executed during
	 * the lifetime of this object
	 *
	 * @return integer amount of queries executed during
	 *					the lifetime of this object
	 */
	public function queryCount()
	{
		return $this->_queryCount;
	}
	
	/**
	 * The amount of time spent in mysql_query()
	 * during the lifetime of this object
	 *
	 * @return int time in ms
	 */
	public function queryCost()
	{
		return $this->_queryCost;
	}

	/**
	 * This method makes sure that the input is
	 * clean and ready for use in the database
	 *
	 * @param string $input the string to clean
	 * @return string $out the cleaned string
	 */
	public static function sanitize($input)
	{
		if( is_numeric($input) )
		{
			return $input;
		}
		else if( is_array($input) || is_object($input) )
		{
			return self::sanitize(serialize($input));
		}
		return self::escapeString($input);
	}
	
	public function escString($input)
	{
		return $this->_connection->escape_string($input);
	}
	
	public static function escapeString($input)
	{
		return self::defaultDB()->escString($input);
	}
	
	public function engineName()
	{
		return "MySQL 5";
	}
}