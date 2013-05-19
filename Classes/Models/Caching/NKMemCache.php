<?php
class NKMemCache extends NKCache
{
	private static $sharedCache = null;
	private $memCache			= null;
	
	const SERVER 	= "localhost";
	const PORT		= 11211;
	
	public static function sharedCache()
	{
		if( !self::$sharedCache )
		{
			self::$sharedCache = new NKMemCache();
		}
		return self::$sharedCache;
	}

	public function NKMemCache()
	{
		$this->memCache = new Memcache();
		$this->memCache->addserver(self::SERVER,self::PORT);
	}
	
	public function valueForKey($key)
	{
		if( $key )
		{
			return $this->memCache->get($key);
		}
		return NULL;
	}
	
	public function setValueForKey($value,$key)
	{
		$this->memCache->set($key,$value);
	}
	
	public function purge()
	{
		$this->memCache->flush();
	}
}
?>