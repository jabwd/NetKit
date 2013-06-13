<?php
class PrivatemessageController extends NKActionController
{
	public function __construct()
	{
		$this->name = "Inbox";
	}
	
	public function handleRequest($request = null)
	{
		$this->_request = $request;
		$this->view 	= new NKView('privatemessage/'.$request->actionName, $this, 'NetKit/Classes/Views/templates/');
		return $this->view->pageExists();
	}
	
	public function indexAction()
	{
	}
	
	public function inboxAction()
	{
		$this->indexAction();
	}
	
	public function createAction()
	{
		
	}
	
	public function deleteAction()
	{
		
	}
	
	public function viewAction()
	{
		
	}
}