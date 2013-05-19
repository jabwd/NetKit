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
	
	// this one is made private in favor of the singleton NKWebsite
	// making it private makes it easy to spot usage in code
	private $website;
	
	/**
	 * Description:	this method handles an incoming NKRequset, creates
	 *				a view for this controller using the given controllerName
	 *				and action name.
	 *
	 * Returns:		false when we cannot find / create the view
	 */
	public function handleRequest($request = null) {
		$this->view = new NKView($request->controllerName.'/'.$request->actionName, $this);
		return $this->view->pageExists();
	}
	
	/**
	 * Description:	this method gives easy access to the current request object
	 *				to make it easier to get certain values out of the request
	 */
	public function request() {
		return NKWebsite::sharedWebsite()->request;
	}
}