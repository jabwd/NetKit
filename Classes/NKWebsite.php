<?php
/**
 * NKWebsite is the main class of NetKit. It boots the system
 * starts handling the requests, checks for caches etc.
 *
 * Version: 0.1
 * Author: 	Antwan van Houdt
 * Created: 18-12-2012
 */
require_once 'NetKit/lib/libnetkit.php';
require_once 'Website/Config.php';


require_once 'NetKit/Classes/Models/Requests/NKRequest.php';
require_once 'NetKit/Classes/Models/Session/NKSession.php';

require_once 'NetKit/Classes/Models/Caching/NKCacheManager.php';

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
		// No implementation
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
		// Verify whether we have a cache list of all the available classes
		// in our project so the autoloader can do its job.
		$cacheManager = NKCacheManager::defaultManager();
		$classes = $cacheManager->valueForKey("NetKit.Classes");
		if( !$classes || Config::debugMode )
		{
			$classes = cacheForDirectory(".");
			$cacheManager->setValueForKey($classes, "NetKit.Classes");
			
			if( !$classes )
			{
				die("The NetKit autoloader is a required subsystem");
			}
		}
		
		// Make the class list available to the autoloader
		$GLOBALS['classes'] = $classes;
		
		// in case of a bootstrap controller ( some code that HAS to be executed
		// before anything else ) create it. Whatever happens next is up to the
		// implementation of the bootstrap controller. NetKit doesn't call any methods
		// of bootstrap at this point in time.
		if( isset($classes['BootstrapController']) )
		{
			$bootstrap = new BootstrapController();
		}
		
		// Setup done, handle the request.
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
		$this->request 		= new NKRequest();
		$controllerClass 	= ucfirst($this->request->controllerName).'Controller';
		$action 			= $this->request->actionName.'Action';
		$this->_controller 	= new $controllerClass();
		
		// figure out whether the controller has a view and action method
		// for the current request, 404 if it doesn't otherwise call it
		if( !$this->_controller->handleRequest($this->request) || !method_exists($this->_controller, $action) )
		{
			throw new PageNotFoundException();
		}
		$this->_controller->$action();
		
		// render the view of the current controller
		$view = new NKMainView($this->_controller->layout, $this->_controller->view);
		$view->render();
		
		// bread crumb
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
	
	public function description()
	{
		if( $this->_controller->description )
		{
			return $this->_controller->description;
		}
		return Config::description;
	}
}