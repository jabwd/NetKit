<?php
class NKAdminController extends NKActionController
{
	public function __construct()
	{
		$this->name = "Admin panel";
	}
	
	public function indexAction()
	{
		if( !NKSession::access("pages.manage") )
		{
			throw new NotAllowedException();
		}
		// list the menu, should be implemented in the view
	}
}