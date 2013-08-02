<?php
/*
 * Author: Antwan van Houdt
 * Created: 12-22-2012
 * 
 * Convenience class for handling requests
 * making it a bit less cluttered on the NKWebsite instance
 */
class NKRequest
{
	/*
	 * Stores the current request ID
	 * usually the value of the current primary
	 * key or something similar
	 */
	public $ID = 0;
	
	/*
	 * Stores the controller name of the current request
	 */
	public $controllerName;
	
	/*
	 * Stores the action name of the current request
	 */
	public $actionName;
	
	/*
	 * Hashtable of key and its value
	 */
	private $values = array();
	
	/**
	 * This method wraps code for getting the current request URI
	 * Since you might be using HIPHOP-PHP ( Which is natively supported by NetKit )
	 * you are going to need to modify NGINX or whatever you are using to return
	 * the real request_uri in a proxy header
	 *
	 * @return String current request URI
	 */
	public static function getURI()
	{
		if( array_key_exists("HTTP_HIPHOP", $_SERVER) && $_SERVER['HTTP_HIPHOP'] === 'YES' ) 
		{
			return $_SERVER['HTTP_REALURI'];
		}
		return $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Same functionality as the getURI() but then for the IP address
	 * @returns a string containing an IPv4 address
	 */
	public static function getRequestIP() 
	{
		// TODO TODO TODO TODO DOES NOT SUPPORT PROXIES!!!
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public function __construct($URL = NULL)
	{
		if( !$URL ) 
		{	
			$URL = self::getURI();
		}
		
		// determine the page controller we should use now.
		// the basic pattern we use is:
		// /controller/action/id/varName/varValue/var2Name/var2Value
		// scan the string
		$parts 		= explode("/", $URL);
		$cnt		= count($parts);
		
		if( $cnt > 1 )
		{
			$this->controllerName 	= $parts[1];
			
			// normally we'd not have to check for hte length but
			// if we're using HPHP-VM then some stricter coding guidelines 
			// are required
			if( $cnt > 2 ) 
			{
				$this->actionName = $parts[2];
				
				if( $cnt > 3 )
				{
					$this->ID = (int)$parts[3];
					
					$idx = 4;
					if( $this->ID == 0 && $this->actionName )
					{
						$idx = 3;
					}
					
					// continue scanning for URL key / values
					// if we have the space for it
					if( $cnt > $idx )
					{
						// $i starts with 3 since we can skip the first 3
						// Then we go in steps of 2 ( key + value )
						for($i=$idx;($i+1)<$cnt;$i+=2)
						{
							$this->values[$parts[$i]] = $parts[$i+1]; 
						}
					}
				}
			}
		}
		
		if( !$this->controllerName )
		{
			$this->controllerName = 'index';
		}
		if( !$this->actionName )
		{
			$this->actionName = 'index';
		}
	}
	
	/*
	 * Description: key-value accessor for the values encoded with keys in the URL
	 *				parsed by this class
	 *
	 * Returns:		whatever value is in the array at $key
	 */
	public function valueForKey($key) 
	{
		return $this->values[$key];
	}
	
	/*
	 * Description:	this is used in order to determine whether the main view should be drawn or not
	 *				this is relevent since ajax requests usually only return JSON data or some HTML parts
	 *
	 * Return:		returns true or false depending whether get_ajax is true
	 */
	public function isAjaxRequest() 
	{
		return (isset($this->values["ajax"]));
	}
}