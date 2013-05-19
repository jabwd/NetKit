<?php
class NKJSONCache extends NKCache
{
	private $storage;
	
	public function __construct()
	{
		if( file_exists('Cache/data.json') )
			$this->storage = json_decode(file_get_contents("Cache/data.json"),true);
		else
			$this->storage = array();
	}
	
	public function valueForKey($key)
	{
		return $this->storage[$key];
	}
	
	public function setValueForKey($value,$key)
	{
		$this->storage[$key] = $value;
		$this->save();
	}
	
	// reset the data
	public function purge()
	{
		file_put_contents("Cache/data.json","");
		unset($this->storage);
		$this->storage = array();
	}
	
	private function save()
	{
		$data = json_encode($this->storage);
		file_put_contents("Cache/data.json", $data);
	}
}
?>