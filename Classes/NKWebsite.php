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
	const NetKitVersion = "0.16.1";
	
	private static $_sharedInstance;
	private $_controller;
	
	public 	$request;
	
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
	}
	
	/**
	 * Starts up the website, only method you need to call
	 * in your index.php
	 */
	public static function start()
	{
		$cacheManager = NKCacheManager::defaultManager();
		$classes = $cacheManager->valueForKey("NetKit.Classes.".Config::domainName);
		if( !$classes || Config::debugMode )
		{
			$classes = cacheForDirectory(".");
			$cacheManager->setValueForKey($classes, "NetKit.Classes.".Config::domainName);
			
			if( !$classes )
			{
				die("The NetKit autoloader is a required subsystem");
			}
		}
		$GLOBALS['classes'] = $classes;
		
		self::sharedWebsite()->handleRequest();
	}
	
	/**
	 * Creates a new NKRequest instance and handles
	 * the current incoming request. Creates the controller
	 * calls its action and renders the new view
	 */
	public function handleRequest()
	{
		$this->request = new NKRequest();
		
		$controllerClass 	= ucfirst($this->request->controllerName).'Controller';
		$action 			= $this->request->actionName.'Action';
		$this->_controller 	= new $controllerClass();
		
		if( !$this->_controller->handleRequest($this->request) || !method_exists($this->_controller, $action) )
		{
			throw new PageNotFoundException();
		}
		$this->_controller->$action();
		
		$view = new NKMainView($this->_controller->layout, $this->_controller->view);
		$view->render();
		
		NKSession::updatePreviousPage();
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