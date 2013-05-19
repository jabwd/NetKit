<?php
class NKAPCCache extends NKCache {
	public function valueForKey($key) {
		return apc_fetch($key);
	}
	
	public function setValueForKey($value, $key) {
		apc_add($key, $value, 0);
	}
	
	public function purge() {
		apc_clear_cache();
	}
}