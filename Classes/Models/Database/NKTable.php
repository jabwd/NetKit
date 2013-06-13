<?php
/*
 * [NKTable a class that makes use of NKDatabase in order to
 *			make accessing databases easier]
 *
 * Version: 0.1
 * Author: 	Antwan van Houdt
 * Created: 18-12-2012
 */
class NKTable {
	public $tableName; 		// DB table name
	public $rowClass;		// PHP Class name of the NKTableRow subclass
	public $primaryKey;		// table primary key ( auto discovered )
	public $tableLayout;	// table layout ( auto discovered )
	
	public $extraTable;		// used for joins
	
	private $secondaryTableInstance; // this is used for caching, and generally speeding stuff up so we do not have to re-create objects
									 // all the fucking time
	private $_sort = null;
	
	/**
	 * A singleton accessor for NKTable classes. In some cases in your code
	 * you might want to use a certain table object more than once over several
	 * different functions. Rather than passing it through in parameters you can use
	 * the singleton accessor instead.
	 */
	public static function defaultTable()
	{
		static $instance;
		$className = get_called_class();
		if( $instance == NULL )
		{
			$instance = new $className;
		}
		return $instance;
	}
	
	public function __construct()
	{
		if( !$this->tableName ) 
		{
			throw new Exception("NKTable not configured properly, missing tableName", 500);
			return;
		}
		
		
		// get the tablelayout from cache
		$key 	= "db:".$this->tableName;
		$cached = NKMemCache::sharedCache()->valueForKey($key);
		if( $cached )
		{
			$this->tableLayout 	= $cached['layout'];
			$this->primaryKey 	= $cached['primary'];
		}
		
		// Determine whether we still need to discover the content
		// or layout of the table we are using
		if( count($this->tableLayout) < 1 )
		{
			// query the database for our table layout
			$database 	= NKDatabase::sharedDatabase();
			$result 	= $database->query("DESCRIBE ".$this->tableName);
			
			// parse the result and write it to cache
			if( $result && mysql_num_rows($result) > 0 )
			{
				$layout = array();
				while($row = mysql_fetch_array($result))
				{
					// fetched a field
					$layout[] = $row['Field'];
					
					// determine whether its a primary key or not
					if( $row['Key'] === 'PRI' ) {
						$this->primaryKey = $row['Field'];
					}
				}
				$this->tableLayout = $layout;
				
				// cache the layout in memcache
				NKMemCache::sharedCache()->setValueForKey(array(
					'primary'=>$this->primaryKey,
					'layout'=>$layout
				), $key);
			}
			else
			{
				throw new Exception("Unable to determine table layout, something went wrong with the database", 500);
			}
		}
	}
	
	/**
	 * The executeQuery generates a query, execs
	 * the given query and handles the resulting data
	 *
	 *
	 * @param string $tail your own addons to the query
	 *						like a sort or order
	 * @return Object or anything really, depending no the query
	 */
	public function executeQuery($tail = "")
	{
		$query = "SELECT ";
		
		// Add the columns of 'self' to the current query
		// With a good prefix so they do not interfere with any
		// added later
		$layout = $this->tableLayout;
		$column	= array_shift($layout);
		$name	= $this->tableName;
		$query .= $name.'.'.$column.' as '.$name.'_X_'.$column;
		foreach($layout as $column)
		{
			$query .= ", ".$name.'.'.$column.' as '.$name.'_X_'.$column;
		}
		
		
		// if an extratable property is set it means that we need to do
		// a join on other tables. Thus we are adding the new columns
		// to the select and create queryConstraints ( for the where clause )
		// and tablenames for the FROM clause
		if( is_array($this->extraTable) )
		{
			$tables			= array();
			$tableCount 	= 0;
			foreach($this->extraTable as $key=>$extraTable)
			{
				$extraTableName = $extraTable[0];
				
				// create the table instance for the given tableName
				$tableInstance 			= new $extraTableName();
				$layout					= $tableInstance->tableLayout;
				if( !$layout )
				{
					throw new Exception("Cannot find model for ".$extraTableName, 500);
				}
				
				// create the new nickname for the given table
				// this is "xN" where N is the number of secondary table
				// we are currently looping through
				$tableName = 'x'.$tableCount;
				
				
				// add the columns
				$layout = $tableInstance->tableLayout;
				foreach($layout as $column)
				{
					$query .= ", ".$tableName.'.'.$column.' as '.$tableName.'_X_'.$column;
				}
				
				// create some additional stuff so we do not have to loop again later
				// on in the code below
				$queryConstraints[] 		= $name.'.'.$key.'='.$tableName.'.'.$tableInstance->primaryKey;
				$tableNames[] 				= $tableInstance->tableName.' as '.$tableName;
				$tableInstance->_alias		= $extraTable[1];
				$tableClasses[$tableName] 	= $tableInstance;
				
				$tableCount++;
			}
		}
		
		// Properly add the FROM clause with all the required tables
		// and their new nicknames to make sure nothing interferes
		$query .= " FROM ".$this->tableName;
		if( $tableNames )
		{
			foreach($tableNames as $table)
			{
				$query .= ", ".$table;
			}
		}
		
		// Add constraints to actually make the join happen
		// We use the given fields together with the primary keys
		// of the tables to create these constraints
		if( $queryConstraints )
		{
			$query .= " WHERE ";
			$constraint = array_shift($queryConstraints);
			$query .= $constraint;
			foreach($queryConstraints as $constraint)
			{
				$query .= " AND ".$constraint;
			}
		}
		
		// add the tail if any
		$query .= $tail;
		
		
		$result = NKDatabase::sharedDatabase()->query($query);
		if( $result && mysql_num_rows($result) > 0 )
		{
			while( $row = mysql_fetch_array($result) )
			{
				$instances = array();
				$self = new $this->rowClass($this);
				
				foreach($row as $key=>$value)
				{
					if( is_numeric($key) )
					{
						continue;
					}
					
					$tableName 	= explode("_X_", $key);
					$column		= $tableName[1];
					$tableName 	= $tableName[0];
					
					if( $tableName == $this->tableName )
					{
						$self->$column = $value;
					}
					else
					{
						$instance = $instances[$tableName];
						if( !$instance )
						{
							$tableInstance 		= $tableClasses[$tableName];
							$instance 			= new $tableInstance->rowClass($tableInstance);
							$instance->_alias 	= $tableInstance->_alias;
							$instances[$tableName] = $instance;
						}
						$instance->$column = $value;
					}
				}
				
				foreach($instances as $instance)
				{
					$name = $instance->_alias;
					$self->$name = $instance;
					unset($instance->_alias);
				}
				
				unset($instances);
				
				$finalResult[] = $self;
			}
		}
		return $finalResult;
	}
	
	private function handleOutput($result)
	{
		if( !$result )
		{
			return NULL;
		}
		
		// loop through all the result rows we have
		if( mysql_num_rows($result) > 0 )
		{
			while( $row = mysql_fetch_row($result) ) {
				$class = new $this->rowClass($this);
				for($i=0;$i<count($this->tableLayout);$i++) {
					$key = $this->tableLayout[$i];
					$class->$key = $row[$i];
				}
				$class->tableRow = $row;
				$output[] = $class;
			}
		}
		return $output;
	}
	
	public function simpleQuery($query)
	{
		$result = NKDatabase::exec($query);
		return $this->handleOutput($result);
	}
	
	/*
	 * Description:	this function is used in order to build the extra part for the query
	 *				that is used in the where clause in order to execute a join
	 *
	 *	Returns:	a string containing a where clause
	 */
	 // TODO: This code has too many loops, needs to be sorted out.
	private function buildExtraQuery($sort = "")
	{
		// since field names can overlap it is important that we extend this query with the layout of the other table
		
		// special case when we want more than 1 join
		if( count($this->extraTable) > 0 ) {
			// the table class names are the keys of this array
			$extraTables = array_keys($this->extraTable);
			
			$this->secondaryTableInstance = array();
			$query = "SELECT ";
			$first = true;
			foreach($this->tableLayout as $column)
			{
				if( !$first )
				{
					$query .= ",";
				}
				else
				{
					$first = false;
				}
				$query .= $this->tableName.'.'.$column;
			}
			
			// the first part is built, now build the part for the rest of the tables
			foreach($extraTables as $count=>$className)
			{
				$class = null;
				
				// get a cached instance
				if( array_key_exists($className, $this->secondaryTableInstance) ) {
					$class = $this->secondaryTableInstance[$className];
				}
				
				// fallback
				if( ! $class )
				{
					$class = new $className();
					$this->secondaryTableInstance[$className] = $class;
				}
				
				// If the buildExtraQuery fails the result of our table should be un-useable
				if( !$class || !$class->tableLayout )
				{
					throw new Exception("Unable to find ".$className." make sure that your database is correct",500);
				}
				
				// the prefix used in order to make sure that the table names/columns do not collide
				$prefix = strtolower($class->tableName).$count.'_';
				foreach($class->tableLayout as $column)
				{
					$query .= ','.$class->tableName.'.'.$column.' AS '.$prefix.$column;
				}
			}
			
			// build the from part
			$query .= " FROM ".$this->tableName;
			$where = " WHERE ";
			$first = true;
			foreach($extraTables as $className)
			{
				// get a cached instance
				if( array_key_exists($className, $this->secondaryTableInstance) ) {
					$table = $this->secondaryTableInstance[$className];
				}
				if( ! $table )
				{
					// this should never happen
					throw new Exception("Error in fetching cached table instance",500);
				}
				$query .= ",`".$table->tableName."`";
				
				// also build the WHERE clause
				if( $first ) {
					$first = false;
				}
				else
				{
					$where .= ' AND ';
				}
				$where .= $this->tableName.".".$this->extraTable[$className]."=".$table->tableName.".".$table->primaryKey;
			}
			$query .= $where; // stick together
			
			// finally sort if we need to
			// the user only supplies the key and whether its ascending or descending
			if( strlen($sort) > 0 )
			{
				$sort = " ORDER BY ".$sort;
			}
			$query .= $sort;
			return $query;
		}
		else
		{
			throw new Exception("[NKTable] Using secondaryTable values is deprecated in favor of extraTables",500);
		}
	}
	
	/**
	 * Description: This method calls NKRequest in order to get the current URL ID
	 *				then uses the find function to return a single row
	 *
	 * Returns:		a row fetched with find()
	 */
	public function findMain()
	{
		$row = $this->find(NKWebsite::sharedWebsite()->request->ID);
		if( ! $row ) {
			throw new PageNotFoundException(); // this would be done in the GUI otherwise
		}
		return $row;
	}
	
	/*
	 * Creates a rowClass instance and fills it with the found information
	 * Uses the primary key to fetch the row
	 */
	public function find($id)
	{
		// make sure we're dealing with an ID here
		$id = (int)$id;
		if( $id > 0 && $this->rowClass )
		{
			if( $this->extraTable )
			{
				$query = $this->buildExtraQuery()." AND ";
			}
			else
			{
				$query = "SELECT * FROM ".$this->tableName." WHERE ";
			}
			$query .= $this->tableName.'.'.$this->primaryKey."=".$id." LIMIT 0,1";
			$result = NKDatabase::exec($query);
			if( $result && ($row = mysql_fetch_array($result)) )
			{
				$class = new $this->rowClass($this);
				foreach(array_keys($row) as $key)
				{
					$class->$key = $row[$key];
				}
				
				
				// in our result set parse for the extra table values
				if( $this->extraTable )
				{
					$extraTables = array_keys($this->extraTable);
					foreach($extraTables as $count=>$className)
					{
						$rowInstance = $this->buildRowforTable($this->secondaryTableInstance[$className], $row, $count);
						$name = strtolower($this->secondaryTableInstance[$className]->rowClass);
						$class->$name = $rowInstance;
					}
				}
				
				
				return $class;
			}
		}
	}
	
	/*
	 * Description: this method will be able to build an instance of the rowClass of the secondary table
	 *				used for the join
	 *				this method will automatically remove the prefixes and so on
	 *
	 * Returns:		an instance of $this->secondaryTable->rowClass
	 */
	public function buildRowForTable($table, $values, $count = 0)
	{
		if( ! $table )
		{
			return;
		}
		$prefix 	= strtolower($table->tableName).$count.'_';
		$rowClass 	= $table->rowClass;
		$row 		= new $rowClass();
		foreach($table->tableLayout as $column)
		{
			$value = $values[$prefix.$column];
			if( $value )
			{
				$row->$column = $value;
			}
		}
		return $row;
	}
	
	public function fetchAll($extra = "")
	{
		// create the basic query
		if( $this->extraTable ) {
			$query = $this->buildExtraQuery();
		} else {
			$query = "SELECT * FROM ".$this->tableName;
		}
		
		// finish the query and execute on the db
		$query 		.= 	' ' . $extra;
		$result 	= 	NKDatabase::exec($query);
		$rows 		= 	null;
		
		// fetch the results
		while($result && $row = mysql_fetch_array($result))
		{
			// set the main values for the main fetch
			$class = new $this->rowClass($this);
			foreach(array_keys($row) as $key) {
				$class->$key = $row[$key];
			}
			
			// primary key fix
			if( $this->primaryKey )
			{
				$key = $this->primaryKey;
				$value = $row[0]; // should work in this case
				$class->$key = $value;
			}
			
			// in our result set parse for the extra table values
			// from $this->buildExtraQuery()
			if( $this->extraTable ) {
				$extraTables = array_keys($this->extraTable);
				foreach($extraTables as $className) {
					$rowInstance = $this->buildRowforTable($this->secondaryTableInstance[$className], $row);
					$name = strtolower($this->secondaryTableInstance[$className]->rowClass);
					$class->$name = $rowInstance;
				}
			}
			$rows[] = $class;
		}
		return $rows;
	}
	
	/**
	 * Description:	This is a bit similar to bindings with SQlite3
	 *				on a desktop environment. This function allows you
	 *				to easily pass values into your query without worrying
	 *				about sanitizing your inputs; this method handles it for you
	 *
	 * Returns:		a resultset
	 */
	public function findWhere($where) {
		// this is a process we use in order to allow people to pass a dynamic amount of
		// variables into this functino
		$args = func_get_args();
		
		// determine whether the second argument is an array or not
		if( is_array($args[1]) && count($args[1]) > 0 )
		{
			$values = $args[1];
		}
		else
		{
			$values = $args;
		}
		
		foreach($values as $value)
		{
			if( $value === $where )
				continue;
				
			if( strpos($where, "?") )
			{
				// make the value 'safe'
				if( is_array($value) )
				{
					$value = "'".mysql_real_escape_string(serialize($value))."'";
				}
				else if( is_string($value) )
				{
					$value = "'".mysql_real_escape_string($value)."'";
				}
				else if( is_numeric($value) )
				{
					// do nothing, the value should be safe!
				}
			
				// replace
				$where = str_replace_once("?", $value, $where);
			}
		}
		$result = $this->fetchWhere($where, $this->_sort);
		$this->_sort = null;
		return $result;
	}
	
	/**
	 * Description:	*sigh* this is a problem of the current 
	 *				architecture of NKTable, currently
	 * 				it is very difficult to pass a sort clause
	 *				into your query ( coz of the way we handle
	 *				findwhere ). Use this function for your one-time
	 *				usage sort clause
	 *
	 * Returns:		void
	 */
	public function setSingleSort($sortValue) {
		$this->_sort = $sortValue;
	}
	
	/*
	 * Fetches row with the given where clause
	 */
	public function fetchWhere($where = "", $sort = null)
	{
		if( $this->extraTable )
		{
			$query = $this->buildExtraQuery()." AND ".$where;
		}
		else
		{
			$query = "SELECT * FROM ".$this->tableName." WHERE ".$where;
		}
		if( $sort ) {
			// by putting "ORDER BY" here we sort of force the developer
			// to really use the syntax for sorting and not use some fancy tricks
			// to try to break or hack into this code ( MySQL will return an error he won't expect )
			$query .= " ORDER BY ".$sort;
		}
		$result = NKDatabase::exec($query);
		$rows = null;
		while($result && $row = mysql_fetch_array($result))
		{
			$class = new $this->rowClass($this);
			foreach(array_keys($row) as $key)
			{
				$class->$key = $row[$key];
			}
			
			// in our result set parse for the extra table values
			if( $this->extraTable )
			{
				$extraTables = array_keys($this->extraTable);
				foreach($extraTables as $className)
				{
					$rowInstance = $this->buildRowforTable($this->secondaryTableInstance[$className], $row);
					$name = strtolower($this->secondaryTableInstance[$className]->rowClass);
					$class->$name = $rowInstance;
				}
			}
			
			$rows[] = $class;
		}
		return $rows;
	}
	
	/*
	 * Inserts a given rowClass object into the database
	 * $object:	rowClass instance that will be inserted
	 *
	 * Returns: void
	 */
	public function insert($object)
	{
		if( count($this->tableLayout) > 0 )
		{
			$query = "INSERT INTO ".$this->tableName." (";
			$cnt = 0;
			foreach($this->tableLayout as $tableKey)
			{
				if( $tableKey === $this->primaryKey )
					continue;
					
				// determine whether we need this one
				if( $object->$tableKey == null )
				{
					continue; // do not insert the null values, let the database handle it with default values
				}
				
				$comma = ",";
				if( $cnt == 0 )
					$comma = "";
				$query .= $comma.$tableKey;
				
				$cnt++;
			}
			$query .= ") VALUES (";
			$cnt = 0;
			foreach($this->tableLayout as $tableKey)
			{
				if( $tableKey === $this->primaryKey )
					continue;
					
				// determine whether we need this one
				if( $object->$tableKey == null )
				{
					continue; // do not insert the null values, let the database handle it with default values
				}
				
				$comma = ",";
				if( $cnt == 0 )
					$comma = "";
				$value = $object->$tableKey;
				if( $value && is_string($value) )
					$value = "'".mysql_real_escape_string($value)."'";
				else if( ! $value )
					$value = 'null'; // database style!
					
				$query .= $comma.$value;
				
				$cnt++;
			}
			$query .= ")";
			NKDatabase::exec($query);
			return mysql_insert_id();
		}
		else
		{
			die("<b>Error:</b> Cannot insert row into ".$this->tableName." if no tableLayout is known");
		}
	}
	
	/*
	 * Updates a given rowClass object
	 * $object:	rowClass instance that we will use its integer based
	 *			primary key of to update the given row using the object's values
	 *			[Note: doesn't update the primary key]
	 * Returns: void
	 */
	public function update($object)
	{
		$key = $this->primaryKey;
		$id = (int)$object->$key;
		if( $id > 0 && count($this->tableLayout) > 0 )
		{
			$query = "UPDATE ".$this->tableName." SET ";
			$cnt = 0;//not using the foreach count for a very good reason
			foreach($this->tableLayout as $tableKey)
			{
				if( $tableKey === $this->primaryKey )
					continue; // skip updating the primary key?
				$comma = ",";
				if( $cnt == 0 )
					$comma = "";
				$query .= $comma.$tableKey."='".mysql_real_escape_string($object->$tableKey)."'";
				
				$cnt++;
			}
			$query .= " WHERE ".$this->primaryKey."=".$id;
			NKDatabase::exec($query);
		}
	}
	
	/*
	 * Deletes a given rowClass object from the database
	 * $object:	rowClass instance that we will use its integer based
	 *			primary key of to delete the row
	 *
	 * Returns: void
	 */
	public function delete($object)
	{
		// get primary key value
		$key = $this->primaryKey;
		$id = (int)$object->$key;
		
		// verify we actually have the preconditions set
		if( $id > 0 && count($this->tableLayout) > 0 )
		{
			// delete the row from the database
			NKDatabase::exec("DELETE FROM ".$this->tableName." WHERE ".$key."=".$id);
		}
	}
	
	public function count() {
		$rows = NKDatabase::exec("SELECT COUNT(*) FROM ".$this->tableName);
		$rows = mysql_fetch_array($rows);
		return $rows[0];
	}
	
	public function beginTransaction() {
		throw new PageNotFoundException();
	}
	
	public function endTransaction() {
		$this->commit();
	}
	
	public function commit() {
		throw new PageNotFoundException();
	}
	
	public function rollBack() {
		
	}
}