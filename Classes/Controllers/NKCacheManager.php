<?php
// since NKCacheManager is a required subsystem of NetKit we are
// not making use of the autoloader here just yet!
require_once 'NetKit/Classes/Models/Caching/NKCache.php';
require_once 'NetKit/Classes/Models/Caching/NKMemCache.php';

class NKCacheManager
{
	private $_backingStore;
	private static $_defaultController;
	
	/**
	 * Description: You are not limited to using 1 cache controller as you might
	 *				want to use different backing stores throughout your code
	 *				This one, however, is mainly used for the general stuff
	 *				and is easy to access anywhere around your code
	 */
	public static function defaultManager()
	{
		if( !self::$_defaultController )
		{
			self::$_defaultController = new self;
		}
		return self::$_defaultController;
	}
	
	public function __construct($engine = 'memcache')
	{
		// determine what backing store to use
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
			$this->_backingStore = new NKMemCache();
		}
		else
		{
			throw new Exception($engine . ' -- Unknown backing store for NKCacheController', 500);
		}
	}
	
	public function setValueForKey($value, $key)
	{
		$this->_backingStore->setValueForKey($value, $key);
	}
	
	public function valueForKey($key)
	{
		return $this->_backingStore->valueForKey($key);
	}
	
	public function removeValueForKey($key)
	{
		return $this->_backingStore->removeValueForKey($key);
	}
	
	public function purge()
	{
		$this->_backingStore->purge();
	}
}