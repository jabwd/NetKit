<?hh
class NKQueryAnalyzer
{
	private NKDatabase $_database;

	public function __construct(): void
	{
		$this->_database = NKDatabase::defaultDB();
	}
	
	public function track(string $query): void
	{
		
	}
	
	public function shouldCacheQuery(string $query): bool
	{
		return false;
	}
	
	/**
	 * Determines whether the given query string
	 * will result in a full table scan or not
	 * handy for debugging your code
	 */
	public function isFullTableScan($query): bool
	{
		$sql 	= "DESCRIBE ".$query;
		$result = $this->_database->query($sql);
		if( $result )
		{
			$row = $result->fetch_assoc();
			if( $row['type'] == 'ALL' )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
	}
}