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


require_once 'NetKit/Classes/Models/Requests/NKRequest.php';
require_once 'NetKit/Classes/Models/Session/NKSession.php';

require_once 'NetKit/Classes/Controllers/NKCacheManager.php';

require_once 'NetKit/Classes/Views/default/NKView.php';
require_once 'NetKit/Classes/Views/default/NKMainView.php';

class NKWebsite
{
	const NetKitVersion = "0.23.0";
	
	private static $_sharedInstance;
	private $_controller;
	
	public 	$request;
	
	private function __construct()
	{
	}
	
	public static function sharedWebsite()
	{
		if( !self::$_sharedInstance )
		{
			self::$_sharedInstance = new self;
		}
		return self::$_sharedInstance;
	}
	
	/**
	 * Starts up the website, only method you need to call
	 * in your index.php
	 *
	 * @return void
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
		if( $GLOBALS['classes']['BootstrapController'] )
		{
			$bootstrap = new BootstrapController();
		}
		
		self::sharedWebsite()->handleRequest();
	}
	
	/**
	 * Creates a new NKRequest instance and handles
	 * the current incoming request. Creates the controller
	 * calls its action and renders the new view
	 *
	 * @return void
	 */
	public function handleRequest()
	{
		if( Config::forceDomain )
		{
			if( $_SERVER['HTTP_HOST'] != Config::domainName )
			{
				redirect('http://'.Config::domainName);
			}
		}
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
	
	/**
	 * Returns the title that should be inside the
	 * <title> tag on the main layout view
	 *
	 * @return String title string
	 */
	public function title()
	{
		if( $this->_controller->name )
		{
			return $this->_controller->name.' - '.Config::title;
		}
		return Config::title;
	}
	
	/**
	 * Deprecated: use "title()" instead
	 */
	public function getTitle()
	{
		return $this->title();
	}
	
	public function description()
	{
		if( $this->_controller->description )
		{
			return $this->_controller->description;
		}
		return Config::description;
	}
}