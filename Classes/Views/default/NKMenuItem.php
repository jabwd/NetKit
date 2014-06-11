<?php
class NKMenuItem
{
	public $title; 			// the display title of the menu Item
	public $extraClass;		// extra classes for custom css styles per item
	public $link;			// the href
	public $tail;			// something that comes after the menu item for some reason
	
	private $controllers;	// list of controller names
	private $action;		// action required for the list of controllers ( extra constraint, not standalone )
	private $paths;			// list of controller/actions/key/value associated with the menu item
	
	public function __construct($title = "Untitled Menu Item", $link = "/", $defaultControllerName = NULL, $defaultAction = NULL)
	{
		$this->title 	= $title;
		$this->link		= $link;
		$this->action	= $defaultAction;
		
		if( $defaultControllerName != NULL )
		{
			$this->controllers 							= array();
			$this->controllers[$defaultControllerName] 	= true;
		}
	}
	
	
	/**
	 * Adds a given controller name as a host controller name to this
	 * menu item. Any request with the specified controller name
	 * as the controller of the requset will be marked as belonging
	 * to this menu item
	 *
	 * @param string $controllerName
	 *
	 * @return void
	 */
	public function addHostController($controllerName)
	{
		if( !$this->controllers )
		{
			$this->controllers = array();
		}
		$this->controllers[$controllerName] = true;
	}
	
	/**
	 * Removes the given controllerName from the list of 
	 * host controllers associated with this menu item
	 *
	 * @param string $controllerName
	 *
	 * @return void
	 */
	public function removeHostController($controllerName)
	{
		if( isset($this->controllers[$controllerName]) )
		{
			unset($this->controllers[$controllerName]);
		}
	}
	
	/**
	 * Marks the specified menu item as the current menu item
	 * This will override
	 *
	 * @return void
	 */
	public function markAsCurrent()
	{
		$_SESSION['menu']['current'] = $this->title;
	}
	
	/**
	 * Determines whether the current menu item
	 * is in any way connected to the current request
	 *
	 * @return boolean
	 */
	public function isCurrent()
	{
		$request = NKWebsite::sharedWebsite()->request;
		
		// this menu item is constrained to an entire controller
		if( count($this->controllers) > 0 )
		{
			foreach($this->controllers as $key=>$value)
			{
				if( $key == $request->controllerName )
				{
					// if we have an action defined we can only be the current if that action
					// is also called on the current controller
					if( isset($this->action) )
					{
						if( $this->action == $request->actionName )
						{
							return true;
						}
					}
					else
					{
						// no action constraint, but the host controller matches so we're current.
						return true;
					}
				}
			}
		}
		
		// determine whether we got marked as 'current'
		if( isset($_SESSION['menu']['current']) && $_SESSION['menu']['current'] === $this->title )
		{
			return true;
		}
		return false;
	}
}