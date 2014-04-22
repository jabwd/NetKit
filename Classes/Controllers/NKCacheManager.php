<?php
// since NKCacheManager is a required subsystem of NetKit we are
// not making use of the autoloader here just yet!
require_once 'NetKit/Classes/Models/Caching/NKCache.php';
require_once 'NetKit/Classes/Models/Caching/NKMemcache.php';

class NKCacheManager
{
	private $_backingStore;
	private static $_defaultController;
	
	/**
	 * If you don't want to bother creating
	 * and configuring your own cache controller
	 * a system wide one can be used through this method
	 *
	 * @return object NKCacheManager
	 */
	public static function defaultManager()
	{
		if( !self::$_defaultController )
		{
			self::$_defaultController = new self;
		}
		return self::$_defaultController;
	}
	
	/**
	 * @param string $engineName
	 */
	public function __construct($engine = 'memcache')
	{
		if( $engine === 'apc' )
		{
			$this->_backingStore = new NKAPCCache();
		}
		else if( $engine === 'json' )
		{
			$this->_backingStore = new NKJSONCache();
		}
		else if( $engine === 'memcache' )
		{
			$this->_backingStore = new NKMemcache();
			
			// just testin
			$this->setValueForKey("test", "testKey");
			if( $this->valueForKey("testKey") !== "test" )
			{
				throw new Exception('memcache doesnt actually work');
			}
		}
		else
		{
			throw new Exception($engine . ' -- Unknown backing store for NKCacheController', 500);
		}
	}
	
	public function setValueForKey($value, $key)
	{
		$this->_backingStore->setValueForKey($value, Config::domainName.$key);
	}
	
	public function valueForKey($key)
	{
		return $this->_backingStore->valueForKey(Config::domainName.$key);
	}
	
	public function removeValueForKey($key)
	{
		return $this->_backingStore->removeValueForKey(Config::domainName.$key);
	}
	
	public function purge()
	{
		$this->_backingStore->purge();
	}
}