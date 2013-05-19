<?php
class NKCache {
	public function valueForKey($key) {
		throw new Exception('This method shouldve been subclassed', 500);
	}
	
	public function setValueForKey($value,$key)	{
		throw new Exception('This method shouldve been subclassed', 500);
	}
	
	public function purge() {
		throw new Exception('This methoud shouldve been subclassed', 500);
	}
}