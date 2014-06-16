<?hh
class NKStringsFile
{
	protected array $storage;

	public function __construct(string $path): void
	{
		if( !file_exists($path) )
		{
			throw new Exception('Cannot open strings file at path '.$path);
		}
		
		$this->storage = array();
		
		$content 	= file_get_contents($path);
		$lines 		= explode("\n", $content);
		$section	= NULL;
		
		unset($content); // keep memory footprint low.
		
		// scan line by line, its a simple file format.
		foreach($lines as $lineCount=>$line)
		{
			$line = trim($line); // ignore whitespaces on both ends.
			
			// for comments in the file format itself.
			if( strpos($line, "//") === 0 || strpos($line, "/*") === 0 )
			{
				continue; // ignore.
			}
			else if( strpos($line, "\"") === 0 )
			{
				// valid line detected, scan it
				$key 	= substr($line, 1);
				$keyEnd = strpos($key, "\"");
				$key 	= substr($key, 0, $keyEnd);
				
				$valueStart = strpos($line, ' = ');
				$valueStart += 4; // _=_"
				
				$value = substr($line, $valueStart, strlen($line)-$valueStart-1);
				
				if( $section )
				{
					$this->storage[$section][$key] = $value;
				}
				else
				{
					$this->storage[$key] = $value;
				}
			}
			else if( strpos($line, '[') === 0 )
			{
				$section = substr($line, 1, strlen($line)-2);
			}
			else
			{
				echo 'Ignoring line '.$lineCount;
			}
		}
	}
	
	public function valueForKey(string $key): ?string
	{
		if( !isset($this->storage[$key]) )
		{
			return NULL;
		}
		return $this->storage[$key];
	}
}