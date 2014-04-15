<?php
class NKAPCCache extends NKCache
{
	public function __construct()
	{
		// verify whether APC cache is possible
		if( !function_exists("apc_add") )
		{
			throw new Exception("Cannot use APC cache without having APC installed", 500);
		}	
	}
	
	public function valueForKey($key)
	{
		return apc_fetch($key);
	}
	
	public function setValueForKey($value, $key)
	{
		apc_add($key, $value, 0);
	}
	
	public function purge()
	{
		apc_clear_cache();
	}
}