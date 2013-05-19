<?php
/**
 * [NKWebsite is similar to Zend_Application in Zend. 
 * It is the main class, handles the request and starts
 * the services that NetKit offers to the user]
 *
 * Version: 0.1
 * Author: 	Antwan van Houdt
 * Created: 18-12-2012
 */
require_once 'NetKit/lib/libnetkit.php';
require_once 'Website/Config.php';

// also get some packages we are going to use nomatter what
// so we can save our autoloader some time and sweat
require_once 'NetKit/Classes/Models/Requests/NKRequest.php';
require_once 'NetKit/Classes/Models/Session/NKSession.php';

require_once 'NetKit/Classes/Controllers/NKCacheManager.php';

require_once 'NetKit/Classes/Views/default/NKView.php';
require_once 'NetKit/Classes/Views/default/NKMainView.php';

class NKWebsite
{
	const NetKitVersion = "0.11.1";

	private static $_sharedInstance;
	private $_controller;
	
	public 	$request;
	
	/**
	 * Singleton accessor for NKWebsite
	 */
	public static function sharedWebsite()
	{
		if( !self::$_sharedInstance )
		{
			self::$_sharedInstance = new self;
		}
		return self::$_sharedInstance;
	}
	
	private function __construct()
	{
		// no implementation, singleton class
	}
	
	/**
	 * Starts up the website, only method you need to call
	 * in your index.php
	 */
	public static function start()
	{
		// re-cache everything, handy when developing
		$cacheManager = NKCacheManager::defaultManager();
		if( Config::noCache )
		{
			$cacheManager->purge();
		}
		
		// set up the globally available list of classes for the autoloader found in
		// libnetkit.php [NetKit/Tools/libnetkit.php]
		$classes = $cacheManager->valueForKey("NetKit_Autoloader_Classes");
		if( !$classes || Config::debugMode )
		{
			// no cache found, create using cacheForDirectory ( which just scans for the contents
			// and finds the classes and their paths )
			$classes = cacheForDirectory(".");
			
			// save for the next page load
			$cacheManager->setValueForKey($classes, "NetKit_Autoloader_Classes");
			
			// if this happens we failed
			if( !$classes )
			{
				die("The NetKit autoloader is a required subsystem");
			}
		}
		$GLOBALS['classes'] = $classes;
		
		
		// all finished, handle the request:
		NKWebsite::sharedWebsite()->handleRequest();
	}
	
	/**
	 * Creates a new NKRequest instance and handles
	 * the current incoming request. Creates the controller
	 * calls its action and renders the new view
	 */
	public function handleRequest()
	{
		// Handle the incoming request
		// the basic pattern we use is:
		// /controller/action/id/varName/varValue/var2Name/var2Value
		$this->request = new NKRequest();
		
		// NKRequest parsed the request we have, now instantiate the controller class
		// and call its action
		$controllerClass 	= ucfirst($this->request->controllerName).'Controller';
		$action 			= $this->request->actionName.'Action';
		$this->_controller 	= new $controllerClass();
		
		
		// In handle request the controller will create a view we will use
		// later on when rendering the page
		if( !$this->_controller->handleRequest($this->request) || !method_exists($this->_controller, $action) )
		{
			throw new PageNotFoundException();
		}
		
		// call the action ( populates the controller's view )
		$this->_controller->$action();
		
		
		// Done with pre-generation, time to render the website and send the buffer
		$view = new NKMainView($this->_controller->layout, $this->_controller->view);
		$view->render();
	}
	
	public function getTitle()
	{
		if( strlen($this->_controller->name) > 0 )
		{
			return Config::title . ' - ' . $this->_controller->name;
		}
		return Config::title;
	}
}