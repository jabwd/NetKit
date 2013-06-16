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
		
		if( $_SESSION['userID'] > 0 )
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
		return NULL;
	}
	
	private static function userRetainCookieHash($user)
	{
		return hash("sha512", NKRequest::getRequestIP().$user->username.$user->password.Config::rememberMeHash);
	}
	
	
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
	
	public static function notifications()
	{
		$userID = self::currentUser()->id;
		if( $userID > 0 )
		{
			return Notifications::defaultTable()->findWhere("userID = ? AND viewed=0", $userID);
		}
		return NULL;
	}
	
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
			
			if( !$_COOKIE['createdAccount'] )
			{
				self::setPersistentCookie('createdAccount', time());
			}
		}
		return $user;
	}
	
	public static function logout()
	{
		unset($GLOBALS['user']);
		unset($_SESSION['userID']);
		self::unsetPersistentCookie(self::CookieLoginHashKey);
		self::unsetPersistentCookie(self::CookieUserIDKey);
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
	
	/**
	 * Unsets a persistent cookie set under the given key
	 *
	 * @param string $key
	 */
	public static function unsetPersistentCookie($key)
	{
		setcookie($key, "", time()-5, "/", Config::domainName);
	}
}