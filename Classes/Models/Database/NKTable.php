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
	public function fetchAll($where = "", $tail = "")
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
		if( $where )
		{
			$query .= " WHERE ".$where;
		}
		if( $queryConstraints )
		{
			if( !$where )
			{
				$query .= " WHERE ";
			}
			else
			{
				$query .= " AND ";
			}
			$constraint = array_shift($queryConstraints);
			$query .= $constraint;
			foreach($queryConstraints as $constraint)
			{
				$query .= " AND ".$constraint;
			}
		}
		
		$query .= ' '.$tail;
		
		
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
		
	/**
	 * Handy method for quickly getting the current item
	 * Usually used in the view action. Uses the request ID
	 * as its entry point using the find function
	 * Throws pagenotfoundException when cannot be found
	 *
	 * @returns Object given row for main ID
	 */
	public function findMain()
	{
		$row = $this->find(NKWebsite::sharedWebsite()->request->ID);
		if( !$row )
		{
			throw new PageNotFoundException(); // this would be done in the GUI otherwise
		}
		return $row;
	}
	
	/**
	 * Finds a row in the table using the given
	 * value for the primary key of this table
	 *
	 * @return Object row for given ID
	 */
	public function find($id)
	{
		$id = (int)$id;
		if( $id > 0 && $this->rowClass )
		{
			$array = $this->fetchAll($this->tableName.'.'.$this->primaryKey.'='.$id);
			return $array[0];
		}
		return NULL;
	}
	
	/**
	 * A find method that offers a bindings like API by replacing
	 * question marks with the given function arguments after the
	 * first $where sting parameter
	 *
	 * @param 		string $where the where clause
	 * @param_n+1 	any variable in the where clause
	 * @return		the result set
	 */
	public function findWhere($where)
	{
		$args = func_get_args();
		
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
			{
				continue;
			}
				
			if( strpos($where, "?") )
			{
				if( is_array($value) )
				{
					$value = "'".mysql_real_escape_string(serialize($value))."'";
				}
				else if( is_string($value) )
				{
					$value = "'".mysql_real_escape_string($value)."'";
				}
				$where = str_replace_once("?", $value, $where);
			}
		}
		$result = $this->fetchAll($where);
		return $result;
	}
	
	public function insert($object)
	{
		if( count($this->tableLayout) > 0 )
		{
			$query = "INSERT INTO ".$this->tableName." (";
			$cnt = 0;
			foreach($this->tableLayout as $tableKey)
			{
				if( $tableKey === $this->primaryKey || $object->$tableKey == null )
				{
					continue;
				}
				$comma = ",";
				if( $cnt == 0 )
				{
					$comma = "";
				}
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
			NKDatabase::sharedDatabase()->query($query);
			return mysql_insert_id();
		}
		else
		{
			throw new Exception("Cannot insert row into ".$this->tableName." if no tableLayout is known");
		}
	}
	
	public function update($object)
	{
		$key = $this->primaryKey;
		$id = (int)$object->$key;
		if( $id > 0 && count($this->tableLayout) > 0 )
		{
			$query = "UPDATE ".$this->tableName." SET ";
			$cnt = 0;
			foreach($this->tableLayout as $tableKey)
			{
				if( $tableKey === $this->primaryKey )
				{
					continue;
				}
				$comma = ",";
				if( $cnt == 0 )
				{
					$comma = "";
				}
				$query .= $comma.$tableKey."='".mysql_real_escape_string($object->$tableKey)."'";
				$cnt++;
			}
			$query .= " WHERE ".$this->primaryKey."=".$id;
			NKDatabase::sharedDatabase()->query($query);
		}
	}
	
	public function delete($object)
	{
		$key 	= $this->primaryKey;
		$id 	= (int)$object->$key;
		if( $id > 0 && count($this->tableLayout) > 0 )
		{
			NKDatabase::sharedDatabase()->query("DELETE FROM ".$this->tableName." WHERE ".$key."=".$id);
		}
	}
	
	public function count()
	{
		$rows = NKDatabase::exec("SELECT COUNT(*) FROM ".$this->tableName);
		$rows = mysql_fetch_array($rows);
		return $rows[0];
	}
}