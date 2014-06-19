<?hh
class NKQueryAnalyzer
{
	public function __construct()
	{
		$qData = file_get_contents('queryLog.log');
		$queries = explode("[QUERY_START]", $qData);
		file_put_contents("queryLog.log", "");
		
		foreach($queries as $query)
		{
			if( strlen($query) > 2 )
			{
				$this->track($query);
			}
		}
	}

	public function track(string $query): void
	{
		$hash 		= $this->hashQuery($query);
		$queries 	= Queries::defaultTable();
		
		$rows = $queries->findWhere('hash=?', $hash);
		if( count($rows) > 0 )
		{
			// extend the count
			$row = $rows[0];
			$row->count++;
			$row->save();
		}
		else
		{
			$row = new Query();
			$row->query = base64_encode($query);
			$row->hash = $hash;
			$row->count = 1;
			$row->fullTable = ($this->isFullTableScan($query) ? 1:0);
			
			$row->save();
		}
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
		$result = NKDatabase::defaultDB()->query($sql);
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
	
	private function hashQuery(string $query): string
	{
		return sha1($query);
	}
}