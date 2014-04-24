<?php
class NKActionController
{
	public $name 		= NULL;
	public $description = NULL;
	public $layout 		= NULL;
	public $view;
	
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
	 * Returns the request currently associated with this action controller. 
	 * If it does not have one it will return the one found on NKWebsite
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