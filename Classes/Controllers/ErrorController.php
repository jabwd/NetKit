<?php
class ErrorController extends NKActionController
{
	protected $exception;
	
	public function __construct($exception = NULL)
	{
		$this->exception = $exception;
	}
	
	public function handleRequest($request = NULL)
	{
		$this->_request = $request;
		$this->view 	= new NKView('error/index', $this, 'NetKit/Classes/Views/templates/');
		return $this->view->pageExists();
	}
	
	public function indexAction()
	{
		if( $_SESSION['exception'] )
		{
			$exception = unserialize($_SESSION['exception']);
			
			// destroy for the next go
			unset($_SESSION['exception']);
		}
		else
		{
			$code = NKWebsite::sharedWebsite()->request->ID;
			if( $code <= 0 )
			{
				$code = 500;
			}
			$message = $this->codeToMessage($code);
			
			$exception = new Exception($message, $code);
		}
		$this->view->exception = $exception;
		$this->view->message = '<b>'.$exception->getCode().':</b> '.$exception->getMessage();
	}
	
	/**
	 * Accetps an HTTP response code
	 * and turns it into a text based message
	 * for outputting on the view
	 *
	 * Will return a standard message of the code
	 * is not recognized
	 *
	 * @param int $HTTPCode
	 * @return String the message related  to the code
	 */
	protected function codeToMessage($code = 500)
	{
		switch($code)
		{
			case 500:
				return 'An error occurred';
			case 404:
				return "Page not found";
			case 403:
				return "You are not allowed to visit that page";
			default:
				return "An error occurred";
		}
	}
}