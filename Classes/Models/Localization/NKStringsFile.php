<?hh
class NKStringsFile
{
	public array $strings;

	public function __construct(string $path): void
	{
		if( !file_exists($path) )
		{
			throw new Exception('Cannot open strings file at path '.$path, 404);
		}
		
		$this->strings = array();
		
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
				
				$valueStart = strpos($line, '=');
				$valueStart += 1; // its at least 1 further than the equals sign
				
				$value 		= substr($line, $valueStart, strlen($line)-$valueStart-1);
				$valueStart = strpos($value, "\"");
				$value 		= substr($value, $valueStart+1);
				
				// if a section is set use a 2 dimensional array
				// there will als obe checked for duplicate keys / sections
				if( $section )
				{
					if( isset($this->strings[$section][$key]) )
					{
						throw new Exception('Error: duplicate key on section '.$section.': '.$key, 500);
					}
					$this->strings[$section][$key] = $value;
				}
				else
				{
					if( isset($this->strings[$key]) )
					{
						throw new Exception('Error duplicate key on strings file '.$key.' either global key or section name already exists', 500);
					}
					$this->strings[$key] = $value;
				}
			}
			else if( strpos($line, '[') === 0 )
			{
				$section = substr($line, 1, strlen($line)-2);
			}
			else
			{
				// ignore this line.
			}
		}
	}
}