<?php
/**
 * The NKDefaultController is a class to be subclassed if you
 * are planning on adding a default service to the base of NetKit.
 */
class NKDefaultController extends NKActionController
{
	public function handleRequest($request = null)
	{
		$this->_request	= $request;
		
		// Detect whether the coder has created a custom version of the template
		// for this controller
		$filePath = Config::templatesPath.$request->controllerName.'/'.$request->actionName.'.php';
		if( file_exists($filePath) )
		{
			$this->view = new NKView($request->controllerName.'/'.$request->actionName, $this);
		}
		else
		{
			$this->view = new NKView(
						$request->controllerName.'/'.$request->actionName, 
						$this, 
						'NetKit/Classes/Views/templates/'
			);	
		}
		return $this->view->pageExists();
	}
}