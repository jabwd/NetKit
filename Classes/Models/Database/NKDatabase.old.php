<?php
/*
 * [NKDatabase is the basic class that other objects use in order
 *	to communicate with the database]
 *
 * Version: 0.1
 * Author: 	Antwan van Houdt
 * Created: 18-12-2012
 */
 
// BIG TODO: We need auto-caching in the NKDatabase code somewhere. It would be awesome if we were able to make caching some kind of database output and content
// generation more automatically so that the programmer does not have to think about performance too much...
// For instance: where we are generating some kind of default UI element that is in some respects the same for most users
 
class NKDatabase {
	public 	$queryCount	= 0;
	public 	$queryCost	= 0; // in seconds
	public 	$debug		= false;
	private $connection = null;
	
	// shared instance
	private static $sharedInstance = null;
	
	public static function sharedDatabase()
	{
		if( !self::$sharedInstance ) {
			self::$sharedInstance = new NKDatabase();
		}
		return self::$sharedInstance;
	}
	
	public function __construct() {
		$this->connection = mysql_connect(	Config::databaseHost, 
											Config::databaseUsername, 
											Config::databasePassword);
											
		if( $this->connection ) {
			mysql_select_db(Config::databaseName,$this->connection);
		}
	}
	
	
	/*
	 * Sanitize a given string for Mysql usage
	 */
	 public static function sanitize($string) {
		 return mysql_real_escape_string(($string));
	 }
	 
	 
	 /*
	  *	Description:	queries the database using the currently being used database engine
	  *
	  *	$query:			a string containing the query
	  *
	  * Returns:		returns a result set
	  */
	 public function query($query)
	 {
		  $this->queryCount++;
		  if( $this->debug ) {
		  	  echo $query;
		  }
		  
		  // count the query execution time
		  $time = microtime(); 
		  $time = explode(" ", $time); 
		  $time = $time[1] + $time[0]; 
		  $time1 = $time;
		  
		  // execute the query
		  $result = mysql_query($query,$this->connection);
		  
		  $time = microtime(); 
		  $time = explode(" ", $time); 
		  $time = $time[1] + $time[0]; 
		  $time2 = $time;
		  
		  $this->queryCost += round(($time2-$time1)*1000, 1);
			 
		  
		  if( $result == null && mysql_error() && !$this->debug ) {
			  throw new Exception("An error occured", 500);
		  } else if( $result == NULL && mysql_error() ) {
		  		echo mysql_error();
		  		exit;
		  }
		  return $result;
	 }
	 
	 /*
	  *	Description:	queries the database using the currently being used database engine
	  *
	  *	$query:			a string containing the query
	  *
	  * Returns:		returns a result set
	  */
	  public static function exec($query) {
	  	  return NKDatabase::sharedDatabase()->query($query);
	  }
	  
	  /*
	   * Description:	Returns a string describing the current database engine being used
	   *
	   * Returns:		void
	   */
	  public function databaseEngineName() {
	  	  $row = mysql_fetch_array($this->query("SHOW VARIABLES WHERE `Variable_name`='version'"));
		  return "MySQL ".$row[1];
	  }
}