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
		$notifications = new Notifications();
		$notification = $notifications->find($this->request()->ID);
		$notification->viewed = true;
		$notification->save();
		
		$notification->goToPage();
	}
}