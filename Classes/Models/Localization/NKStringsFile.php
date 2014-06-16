<?php
class NKStringsFile
{
	public function __construct($path)
	{
		if( !file_exists($path) )
		{
			throw new Exception('Cannot open strings file at path '.$path);
		}
		
		$content = file_get_contents($path);
	}
}