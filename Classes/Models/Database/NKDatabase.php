<?php
class NKDatabase {
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
	
	public function __construct()
	{
		$this->_connection = mysql_connect( 
				Config::databaseHost,
				Config::databaseUsername,
				Config::databasePassword
			);
		if( $this->_connection )
		{
			mysql_select_db(Config::databaseName, $this->_connection);
		}
		else
		{
			throw new Exception("Unable to connect to MySQL backend ".mysql_error());
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
		if( $this->debug )
		{
			echo $query;
			$this->_queryCount++;
			$time 	= microtime(); 
			$time 	= explode(" ", $time); 
			$time 	= $time[1] + $time[0]; 
			$time1 	= $time;
			
			$result = mysql_query($query, $this->_connection);
			
			$time 	= microtime(); 
			$time 	= explode(" ", $time); 
			$time 	= $time[1] + $time[0]; 
			$time2 	= $time;
			
			$this->_queryCost += round(($time2-$time1)*1000, 1);
			
			if( $result == NULL )
			{
				throw new Exception(mysql_error(), 500);
			}
			return $result;
		}
		else
		{
			return mysql_query($query, $this->_connection);
		}
		return NULL;
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
		return mysql_real_escape_string($input);
	}

	public static function exec($query)
	{
		if( !self::$_sharedInstance )
		{
			self::defaultDB();
		}
		return self::$_sharedInstance->query($query);
	}
}