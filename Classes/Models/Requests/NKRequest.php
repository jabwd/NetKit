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
	 * At some point you might start using some fancy server setups
	 * where the request_uri is no longer the true URI used
	 * for your request. Use this method in your code
	 * so NetKit / you can handle this at 1 singel point of failure
	 * This is important for code maintainability and scalabirity of your project
	 *
	 * @return string
	 */
	public static function getURI()
	{
		if( isset($_SERVER['XXX_HTTP_REQUEST_URI']) ) 
		{
			return $_SERVER['XXX_HTTP_REQUEST_URI'];
		}
		return $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Returns the IP used for the current request
	 * Should be modified if you are behind some kind of proxy
	 * that hides the true IP of the request
	 *
	 * @return String a string containing an IPv4 address
	 */
	public static function getRequestIP() 
	{
		if( isset($_SERVER['XXX_REMOTE_ADDR']) )
		{
			return $_SERVER['XXX_REMOTE_ADDR'];
		}
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
							$this->values[$parts[$i]] = urldecode($parts[$i+1]); 
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
		
		// Request translation module, allows you to write URLs
		// in a different language.
		if( Config::siteMap )
		{
			require 'Website/Sitemap.php';
			if( isset($map[$this->controllerName]) )
			{
				$this->controllerName = $map[$this->controllerName];
			}
			if( isset($map[$this->actionName]) )
			{
				$this->actionName = $map[$this->actionName];
			}
		}
	}
	
	/*
	 * Accessor of the 'key/value' pairs in the URL of the
	 * current request
	 *
	 * @param string $key
	 * 
	 * @return string $value
	 */
	public function valueForKey($key) 
	{
		if( isset($this->values[$key]) )
		{
			return $this->values[$key];
		}
		return NULL;
	}
	
	/**
	 * Set a value for a key... need to figure out a way ofgetting rid of this 
	 * method without removing the obvious funcionality benefits for the bootstrap controller...
	 *
	 * @param mixed $value
	 * @param string $key
	 *
	 * @return void
	 */
	public function setValueForKey($value, $key)
	{
		$this->values[$key] = $value;
	}
	
	/*
	 * Determines whether the current request supposedly came from
	 * a javascript request, and therefore should be rendered
	 * faster / simpler without the full templating system
	 *
	 * @return boolean
	 */
	public function isAjaxRequest() 
	{
		return (isset($this->values["ajax"]));
	}
	
	/**
	 * Reformats the given string into something that you can safely put into an URL
	 * Use it to add a readable title to view URLs
	 *
	 * @param $titleString
	 *
	 * @return string Safe title string
	 */
	public static function stringForURLTitle($titleString)
	{
		$titleString = str_replace(" ", "-", $titleString);
		return urlencode($titleString);
	}
}