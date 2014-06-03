<?hh // decl
class NKActionController
{
	public string $name;
	public string $description;
	public string $layout;
	public NKView $view;
	
	protected $_request;
	
	/**
	 * Handles the given NKRequest instance and creates a view
	 * for the given request. Returns false when the view
	 * cannot be created properly.
	 */
	public function handleRequest(NKRequest $request): bool
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
	public function request(): NKRequest
	{
		if( $this->_request )
		{
			return $this->_request;
		}
		return NKWebsite::sharedWebsite()->request;
	}
}