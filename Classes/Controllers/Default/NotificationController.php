<?php
class NotificationController extends NKActionController
{
	public function handleRequest($request = null)
	{
		$this->_request = $request;
		$this->view = new NKView('systeminfo/index', $this, 'NetKit/Classes/Views/templates/');
		return $this->view->pageExists();
	}

	public function viewAction()
	{
		$currentUser = NKSession::currentUser();
		
		$notification 			= Notifications::defaultTable()->findMain();
		$notification->viewed 	= true;
		
		// check for permissions
		if( !$currentUser || $currentUser->id != $notification->userID )
		{
			throw new NotAllowedException();
		}
		
		$notification->save();
		
		$notification->goToPage();
	}
	
	public function markAction()
	{
		$currentUser = NKSession::currentUser();
		
		$notification = Notifications::defaultTable()->findMain();
		$notification->viewed = true;
		
		if( !$currentUser || $currentUser->id != $notification->userID )
		{
			throw new NotAllowedException();
		}
		
		$notification->save();
	}
}