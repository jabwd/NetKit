<?php
session_start();

/**
 * PHP $_SESSION manager for NetKit
 */
class NKSession
{
	const CookieUserIDKey 		= "UserID";
	const CookieLoginHashKey 	= "LoginHash";
	
	public static function currentUser()
	{
		static $user;
		if( $user )
		{
			return $user;
		}
		
		if( isset($_SESSION['userID']) )
		{
			$user = Users::defaultTable()->find($_SESSION['userID']);
			if( $user )
			{
				NKDatabase::exec("INSERT INTO online (userID, time, IP) VALUES (".$user->id.", ".time().", \"".NKRequest::getRequestIP()."\") ON DUPLICATE KEY UPDATE time=VALUES(time), IP=VALUES(IP)");
				
				return $user;
			}
			else
			{
				unset($_SESSION['userID']);
			}
		}
		
		if( isset($_COOKIE[self::CookieUserIDKey]) )
		{
			$userID = (int)$_COOKIE[self::CookieUserIDKey];
			if( $userID > 0 )
			{
				$user = Users::defaultTable()->find($userID);
				$hash = self::userRetainCookieHash($user);
				
				if( $hash === $_COOKIE[self::CookieLoginHashKey] )
				{
					$_SESSION['userID'] = (int)$user->id;
					self::setPersistentCookie(self::CookieLoginHashKey, $hash);
					self::setPersistentCookie(self::CookieUserIDKey, $user->id);
					return $user;
				}
				else
				{
					// When a different IP is used the above will fail
					// but when we leave user set recalling currentUser()
					// will actually return a user instance, this prevents that
					$user = NULL;
				}
			}
		}
		return NULL;
	}
	
	private static function userRetainCookieHash($user)
	{
		return hash("sha512", NKRequest::getRequestIP().$user->username.$user->password.Config::rememberMeHash);
	}
	
	/**
	 * Determines whether the current logged in user has access
	 * to the given permission string
	 *
	 * @param String $permission
	 * @param Boolean $useCache whether to re-verify with the db
	 
	 * @return Boolean $permission
	 */
	public static function access($permission, $useCache = true)
	{
		$currentUser = self::currentUser();
		if( !$currentUser )
		{
			return;
		}
		
		$permissions = $_SESSION['permissions'];
		
		if( $useCache == true && $permissions[$permission] === $currentUser->id )
		{
			return true;
		}
		
		$p = new Permissions();
		if( $p->permissionForUserID($permission, $currentUser->id) )
		{
			$permissions[$permission] = $currentUser->id;
			$_SESSION['permissions'] = $permissions;
			return true;
		}
		return false;
	}
	
	/**
	 * Creates a new user session using the
	 * provided username and password combination
	 *
	 * @param String $username
	 * @param String $password
	 * @param Boolean $retain whether to use persistence
	 
	 * @return NKUser $user
	 */
	public static function login($username, $password, $retain = false)
	{
		$user = User::login($username, $password);
		if( $user != NULL )
		{
			$_SESSION['userID'] = $user->id;
			
			if( $retain )
			{
				$hash = self::userRetainCookieHash($user);
				self::setPersistentCookie(self::CookieLoginHashKey, $hash);
				self::setPersistentCookie(self::CookieUserIDKey, $user->id);
			}
			
			// a simple mechanic in order to try and make it more difficult for people
			// to create multiple accounts
			if( !$_COOKIE['createdAccount'] )
			{
				self::setPersistentCookie('createdAccount', time());
			}
		}
		return $user;
	}
	
	/**
	 * Destroys the current user session
	 *
	 * @return void
	 */
	public static function logout()
	{
		unset($GLOBALS['user']);
		unset($_SESSION['userID']);
		self::unsetPersistentCookie(self::CookieLoginHashKey);
		self::unsetPersistentCookie(self::CookieUserIDKey);
	}
	
	/**
	 * Gets called at the end of the NKWebsite runtime
	 * in order to update the session state in such a way that
	 * we can both store the previous and the one before that
	 * In some cases you need to go back twice
	 *
	 * @return void
	 */
	public static function updatePreviousPage()
	{
		if( !isset($_SESSION['current']) )
		{
			$_SESSION['current'] = '/';
		}
		$_SESSION['previousURI'] 	= $_SESSION['current'];
		$_SESSION['current'] 		= $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Navigates back to the previous page
	 * Code execution *should* stop when you call
	 * this method, do not expect it to return
	 *
	 * @return void
	 */
	public static function navigateBack()
	{
		if( !isset($_SESSION['current']) )
		{
			$_SESSION['current'] = '/';
			redirect('/');
		}
		redirect($_SESSION['current']);
	}
	
	/**
	 * Navigates back to the page before the previous page
	 * This is especiallyl handy when you want to redirect to the page
	 * a user was one after he logged in, as the login page it self
	 * is also a page and therefore seen as the 'previous' page
	 *
	 * @return void
	 */
	public static function navigateBackTwice()
	{
		redirect($_SESSION['previousURI']);
	}
	
	/**
	 * Easy way of setting a persistent cookie for the current website
	 * 
	 * @param string key of the cookie
	 * @param string value of the cookie
	 *
	 * @return void
	 */
	public static function setPersistentCookie($key, $value)
	{
		// a cookie for 1 month ( 60*60*24*30 )
		setcookie($key, $value, time()+2592000, "/", Config::domainName);
	}
	
	/**
	 * Unsets a persistent cookie set under the given key
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public static function unsetPersistentCookie($key)
	{
		setcookie($key, "", time()-5, "/", Config::domainName);
	}
}