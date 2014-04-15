<?php
class NKMemcache extends NKCache
{
	private static $sharedCache = NULL;
	private $memcache			= NULL;
	
	const SERVER 	= "localhost";
	const PORT		= 11211;
	
	public static function sharedCache()
	{
		if( !self::$sharedCache )
		{
			self::$sharedCache = new NKMemcache();
		}
		return self::$sharedCache;
	}
	
	public function __construct()
	{
		if( !class_exists("Memcache") )
		{
			throw new Exception("Cannot create Memcache session, php-memcache not installed?", 500);
		}
		
		$this->memcache = new Memcache();
		$this->memcache->addserver(self::SERVER,self::PORT);
	}
	
	public function valueForKey($key)
	{
		if( $key )
		{
			return $this->memcache->get($key);
		}
		return NULL;
	}
	
	public function setValueForKey($value,$key)
	{
		$this->memcache->set($key,$value);
	}
	
	public function removeValueForKey($key)
	{
		$this->memcache->delete($key);
	}
	
	public function purge()
	{
		$this->memcache->flush();
	}
}
?>