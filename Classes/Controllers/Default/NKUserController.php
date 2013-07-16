<?php
class NKUserController extends NKActionController
{
	private $_user;

	public function __construct()
	{
		$this->name 	= "User";
		$this->_user 	= NKSession::currentUser();
	}
	
	public function indexAction()
	{
		$users	= Users::defaultTable();
		$list = $users->fetchAll(NULL, "ORDER BY created DESC LIMIT 0,30");
		
		$this->view->userList 	= $list;
		$this->view->userCount 	= $users->count();
	}
	
	public function viewAction()
	{
		$userID = $this->request()->ID;
		$profileUser = NULL;
		if( $this->_user && $userID == $this->_user->id )
		{
			$profileUser = $this->_user;
			$this->view->isSelf = true;
		}
		else
		{
			$profileUser = Users::defaultTable()->find($userID);
			
			if( $profileUser && !$_SESSION['viewed'][$profileUser->id] && $this->_user != NULL )
			{
				$hits = (int)$profileUser->profileHits;
				$hits++;
				$profileUser->profileHits	= $hits;
				$profileUser->save();
				$_SESSION['viewed'][$profileUser->id] = true;
			}
			$this->view->isSelf = false;
		}
		
		$this->view->user = $profileUser;
	}
	
	public function editAction()
	{
	}
	
	public function loginAction()
	{
		if( NKSession::currentUser() )
		{
			redirect('/');
		}
		
		if( $_POST['login'] && !$this->_user )
		{
			$username 	= $_POST['username'];
			$password 	= $_POST['password'];
			$retain		= ($_POST['rememberMe'] != NULL);
			
			if( NKSession::login($username, $password, $retain) )
			{
				$this->view->loginSuccess = true;
				NKSession::navigateBackTwice();
			}
			else
			{
				$this->view->loginSuccess = false;
				$this->view->loginFailed = true;
			}
		}
		else if( $_POST['register'] )
		{
			redirect("/user/register");
		}
		else if( $this->_user )
		{
			NKSession::navigateBackTwice();
		}
	}
	
	public function logoutAction()
	{
		NKSession::logout();
		redirect("/");
	}
	
	public function registerAction($login = true)
	{
		if( $_POST['register'] )
		{
			$username = $_POST['username'];
			
			// validate the username
			
			$password 			= $_POST['password'];
			$passwordRepeat 	= $_POST['password2'];
			
			$nickname = $_POST['nickname'];
			
			// nicknames are optional
			if( strlen($nickname) < 1 )
			{
				$nickname = NULL;
			}
			
			// nicknames can  however, be too long
			if( strlen($nickname) > 50 )
			{
				$errors[] = 'A nickname needs to be shorter than 50 characters';
			}
			
			if( strlen($username) < 3 || strlen($username) > 40 )
			{
				$errors[] = "A username needs to be in between 3 and 40 letters";
			}
			
			if( strlen($password) < 1 )
			{
				$errors[] = "A password is required";
			}
			else if( $password !== $passwordRepeat )
			{
				$errors[] = "The 2 passwords you entered do not match. In order to make sure 
							 you know your password we require you to type it twice";
			}
			
			// make sure that the account does not already exist
			$users = new Users();
			$rows = $users->findWhere("username = ?",$username);
			if( count($rows) > 0 )
			{
				$errors[] = "The username <b>".$username."</b> is already taken, you have to pick another one.
							 Do not worry about your name, we have nicknames that will be displayed on the site itself!";
			}
			
			if( count($errors) < 1 )
			{
				// create an account
				$user 				= new User();
				$user->username 	= $username;
				$user->password 	= $user->newHashedPassword($password);
				$user->nickname 	= $nickname;
				$user->reputation 	= 5;
				$user->profileHits	= 0;
				$user->save();
				
				if( $login )
				{
					// log the user in
					$retain = false;
					if( $_POST['rememberMe'] )
					{
						$retain = true;
					}
					NKSession::login($username, $password, $retain);
				}
				
				$this->view->user 		= $user;
				$this->view->success 	= true;
			}
			else
			{
				$this->view->errors = $errors;
			}
		}
	}
}