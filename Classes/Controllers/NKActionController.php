<?php
class NKActionController {
	// the name is used for generating the page title
	public $name 	= null;
	
	// define the main layout to use for the current page / view
	// can be left null if you want to use default
	// which is defined in your website's config
	public $layout 	= null;
	
	// an instantiated NKView object that is used to draw the current page
	// and handle the action's output
	public $view;
	
	/**
	 * When handleRequest is called this one is populated with the request
	 * so that it can be accessed later by calling request
	 */
	protected $_request;
	
	/**
	 * Handles the given NKRequest instance and creates a view
	 * for the given request. Returns false when the view
	 * cannot be created properly.
	 *
	 * @return boolean returns whether the page exists or not
	 */
	public function handleRequest($request = NULL)
	{
		$this->_request 	= $request;
		$this->view 		= new NKView($request->controllerName.'/'.$request->actionName, $this);
		return $this->view->pageExists();
	}
	
	/**
	 * Returns the request currently associated with this action controller. If it does not have
	 * one it will return the one found on NKWebsite
	 *
	 * @return NKRequest $currentRequest
	 */
	public function request()
	{
		if( $this->_request )
		{
			return $this->_request;
		}
		return NKWebsite::sharedWebsite()->request;
	}
}