<?php
class NKUserDefaults
{
	/**
	 * Returns a shared instance
	 *
	 * @return object NKUserDefaults
	 */
	public static function standardUserDefaults()
	{
		static $instance = NULL;
		if( !$instance )
		{
			$instance = new NKUserDefaults();
		}
		return $instance;
	}

	public function __construct()
	{
		
	}
	
	/**
	 * Sets a given value ( str max len 500 ) to the given key
	 *
	 * @param string $value
	 * @param string $key
	 */
	public function setValueForKey($value, $key)
	{
		$value = "".$value;
		if( strlen($value) > 500 )
		{
			throw new Exception("Currently the value of NKUserDefaults is limited to a max of 500 characters and min of 1", 500);
		}
		if( strlen($key) > 100 || !$key )
		{
			throw new Exception("Currently the key of NKUserDefaults is maxed at 100 characters");
		}
	
		// check if row exists
		$row = Defaults::defaultTable()->findWhere("defaults.key = ?", $key);
		if( count($row) == 1 )
		{
			$row = $row[0];
			$row->value = $value;
			$row->save();
			return;	
		}
		$row 		= new DefaultValue();
		$row->key 	= $key;
		$row->value = $value;
		$row->save();
	}
	
	/**
	 * Returns the value for the given key or null if the value
	 * key pair does not exist
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function valueForKey($key)
	{
		$row = Defaults::defalutTable()->findWhere("key = ?", $key);
		if( count($row) == 1 )
		{
			return $row[0]->value;
		}
		return NULL;
	}
}