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
		static $user = NULL;
		
		if( $user )
		{
			return $user;
		}
		
		// get a new user instance
		if( array_key_exists('userID', $_SESSION) && $_SESSION['userID'] > 0 )
		{
			$user = Users::defaultTable()->find($_SESSION['userID']);
			
			// mark this user as 'online'
			NKDatabase::sharedDatabase()->query("INSERT INTO online (userID, time) VALUES (".$user->id.", ".time().") ON DUPLICATE KEY UPDATE time=VALUES(time)");
			
			return $user;
		}
		
		// at this point either the user is not logged in OR we still have to
		// re-build the session using the rememberMe cookie
		$userID = (int)$_COOKIE[self::CookieUserIDKey];
		if( $userID > 0 )
		{
			$user = Users::defaultTable()->find($userID);
			$hash = hash("sha512", NKRequest::getRequestIP().$user->username.$user->password.Config::rememberMeHash);
			
			// verify the cookie
			if( $hash === $_COOKIE[self::CookieLoginHashKey] )
			{
				// restore the session
				$_SESSION['userID'] 	= (int)$user->id;
				
				// renew the cookie
				$time = time()+60*60*24*30;
				setcookie(self::CookieLoginHashKey, $hash, $time, "/", Config::domainName);
				setcookie(self::CookieUserIDKey, $user->id, $time, "/", Config::domainName);
				
				// return the instance, done here
				return $user;
			}
		}
		
		// user is not logged in
		return NULL;
	}
	
	/*
	 * Description:	this method will return true when the current user instance exists and either is an
	 *				highAdmin or has the given $permission
	 *				For important permissions, like banning users, cache should be turned off by passing false
	 *				as a second parameter. This way it will directly query the database in order to figure out
	 *				whether the rights of the user haven't been revoked during his current session
	 *
	 * Returns:		true when the user has the permission, false when he doesn't
	 */
	public static function access($permission, $useCache = true)
	{
		$currentUser = self::currentUser();
		if( ! $currentUser )
		{
			// no user session so we can stop here already
			return;
		}
		
		$permissions = $_SESSION['permissions'];
		
		if( $useCache == true && $permissions[$permission] === $currentUser->id )
		{
			// at this point we know we have the cached permission
			return true;
		}
		
		// if we get to here we got something uncached.
		$p = new Permissions();
		if( $p->permissionForUserID($permission,$currentUser->id) )
		{
			$permissions[$permission] = $currentUser->id;
			$_SESSION['permissions'] = $permissions;
			return true;
		}
		else
		{
			// TODO: Cache this output?
		}
		
		// the user does not have any permissions, return false ( the most likely case of all )
		return false;
	}
	
	/*
	 * Description: get a list of notifications for the current user
	 *
	 * Returns:		an array containing Notification instances
	 */
	public static function notifications()
	{
		if( NKSession::currentUser() )
		{
			$userID 		= NKSession::currentUser()->id;
			$notifications 	= new Notifications();
			return $notifications->findWhere("userID = ? AND viewed=0",$userID);
		}
		return null;
	}
	
	
	/*
	 * Description: logs in a user with the username / password combination
	 *				returns null on failure, user on success.
	 */
	public static function login($username, $password, $retain = false)
	{
		$user = User::login($username, $password);
		if( $user != NULL )
		{
			$_SESSION['userID'] 	= $user->id;
			$GLOBALS['user'] 		= $user;
			
			if( $retain )
			{
				// create a cookie for a retained session
				$hash = hash("sha512", NKRequest::getRequestIP().$user->username.$user->password.Config::rememberMeHash);
				setcookie(self::CookieLoginHashKey, $hash, time()+60*60*24*30, "/", Config::domainName);
				setcookie(self::CookieUserIDKey, $user->id, time()+60*60*24*30, "/", Config::domainName);
			}
			
			// protect against duplicate accounts
			if( !$_COOKIE['createdAccount'] )
			{
				NKSession::setPersistentCookie('createdAccount', time());
			}
		}
		return $user;
	}
	
	
	
	/*
	 * Description: destroys the current user session by unsetting the userID value
	 *				redirecting the user to a new page should be done by whatever is calling
	 *				the logout method.
	 */
	public static function logout()
	{
		$_SESSION['userID'] = -1;
		unset($GLOBALS['user']);
		setcookie(self::CookieLoginHashKey, "", time()-1, "/", Config::domainName);
		setcookie(self::CookieUserIDKey, "", time()-1, "/", Config::domainName);
	}
	
	public static function updatePreviousPage()
	{
		$_SESSION['previousURI'] 	= $_SESSION['current'];
		$_SESSION['current'] 		= $_SERVER['REQUEST_URI'];
	}
	
	public static function previousPage()
	{
		return $_SESSION['previousURI'];
	}
	
	public static function toPreviousPage()
	{
		redirect(self::previousPage());
	}
	
	/**
	 * Easy way of setting a persistent cookie for the current website
	 * 
	 * @param string key of the cookie
	 * @param string value of the cookie
	 */
	public static function setPersistentCookie($key, $value)
	{
		setcookie($key, $value, time()+60*60*24*30, "/", Config::domainName);
	}
}