<?hh // decl

class NKTable
{
	public string 	$tableName; 		// DB table name
	public string 	$rowClass;		// PHP Class name of the NKTableRow subclass
	public string 	$primaryKey;		// table primary key ( auto discovered )
	public array 	$tableLayout;	// table layout ( auto discovered )
	public array	$extraTable;		// used for joins
	public string	$databaseName;	// custom database to be used with this table?
	protected NKDatabase $database;	// custom database instance
	public array	$comments;		// table column comments
	
	public static function defaultTable(): NKTable;
	
	protected function query(string $query);
	
	protected function database(): NKDatabase;
	
	/**
	 * The executeQuery generates a query, execs
	 * the given query and handles the resulting data
	 *
	 *
	 * @param string $tail your own addons to the query
	 *						like a sort or order
	 * @return Object or anything really, depending no the query
	 */
	public function fetchAll(string $where = "", string $tail = "", array $skip = NULL): ?array;
		
	/**
	 * Handy method for quickly getting the current item
	 * Usually used in the view action. Uses the request ID
	 * as its entry point using the find function
	 * Throws pagenotfoundException when cannot be found
	 *
	 * @returns Object given row for main ID
	 */
	public function findMain(): NKTableRow;
	
	/**
	 * Finds a row in the table using the given
	 * value for the primary key of this table
	 *
	 * @return Object row for given ID
	 */
	public function find(int $id): ?NKTableRow;
	
	/**
	 * A find method that offers a bindings like API by replacing
	 * question marks with the given function arguments after the
	 * first $where sting parameter
	 *
	 * @param 		string $where the where clause
	 * @param_n+1 	any variable in the where clause
	 * @return		the result set
	 */
	public function findWhere(mixed $where, ?mixed $arg1 = NULL, ?mixed $arg2 = NULL);
	
	public function insert(NKTableRow $object): ?int;
	
	/**
	 * Updates the given NKTableRow in the database
	 * by using its primary key as where constraint
	 */
	public function update(NKTableRow $object): void;
	
	/**
	 * Deletes the given object from the backend
	 * 
	 * @param object NKTableRow
	 */
	public function delete(NKTableRow $object): void;
	
	/**
	 * Returns the amount of rows for the given
	 * where clause o the current table/database configuration
	 *
	 * @param string $where OPTIONAL
	 *
	 * @return int
	 */
	public function rowCount(?string $where): int;
	
	public function blueprint(): string;
}