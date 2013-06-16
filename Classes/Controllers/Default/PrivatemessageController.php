<?php
class PrivatemessageController extends NKActionController
{
	public function __construct()
	{
		$this->name = "Inbox";
		//$this->layout = "Website/Classes/Views/layout/test.php";
	}
	
	public function handleRequest($request = null)
	{
		$this->_request = $request;
		$this->view 	= new NKView('privatemessage/'.$request->actionName, $this, 'NetKit/Classes/Views/templates/');
		return $this->view->pageExists();
	}
	
	public function indexAction()
	{
		$user = NKSession::currentUser();
		if( !$user )
		{
			redirect('/user/login');
		}
		
		$this->view->messages = Messages::defaultTable()->fetchAll("recipientID=".(int)$user->id, "ORDER BY messages.sent DESC");
	}
	
	public function inboxAction()
	{
		$this->indexAction();
	}
	
	public function createAction()
	{
		$messageID = $this->request()->ID;
		$message   = Messages::defaultTable()->find($messageID);
		$user = NKSession::currentUser();
		if( !$user )
		{
			redirect('/user/login');
		}
		
		if( $_POST['create'] )
		{
			$recipient = Users::defaultTable()->findWhere('username = ?', $_POST['username']);
			if( !$recipient )
			{
				$errors[] = 'The username you filled in does not exist';
			}
			else
			{
				$recipient = $recipient[0];
				if( $recipient->id < 1 )
				{
					$errors[] = 'The username you filled in does not exist';
				}
			}
			if( !checkStringLength($_POST['title'], 0, 40) )
			{
				$errors[] = 'The title of the message needs to be in between 0 and 40 characters';
			}
			if( !checkStringLength($_POST['content'], 0, 6000) )
			{
				$errors[] = 'The message needs to be in between 0 and 6000 characters';
			}
			
			if( count($errors) == 0 )
			{
				$createdMessage = new Message;
				$createdMessage->title = $_POST['title'];
				$createdMessage->content = $_POST['content'];
				$createdMessage->recipientID = $recipient->id;
				$createdMessage->authorID	= (int)$user->id;
				
				$PMID = $createdMessage->save();
				$format = "%s has sent you a private message";
				$message = sprintf($format, $user->displayString(false));
				Notification::sendNotification($recipient->id, $message, '/privatemessage/view/'.$PMID);
				$this->view->success = true;
			}
			else
			{
				$this->view->errors = $errors;
			}
		}
		
		$this->view->message = $message;
	}
	
	public function deleteAction()
	{
		$message 	= Messages::defaultTable()->findMain();
		$user		= NKSession::currentUser();
		if( $message->recipientID != $user->id && $user->id > 0 )
		{
			throw new NotAllowedException();
		}
		
		if( $_POST['delete'] )
		{
			$message->delete();
			redirect('/privatemessage/');
		}
		else if( $_POST['cancel'] )
		{
			redirect('/privatemessage/view/'.$message->id);
		}
		
		$this->view->message = $message;
	}
	
	public function viewAction()
	{
		$message = Messages::defaultTable()->findMain();
		$user		= NKSession::currentUser();
		if( $message->recipientID != $user->id && $user->id > 0 )
		{
			throw new NotAllowedException();
		}
		
		$this->view->message = $message;
	}
}