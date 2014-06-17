<?php
class User extends NKTableRow
{
	public $tableName = "Users";
	
	
	/*
	 * Description: calls NKSession in order to return the current User session instance
	 *
	 * Returns:		an User instance associated with the current Session. null on failure
	 *				or null when no user is logged in
	 */
	public static function currentUser()
	{
		return NKSession::currentUser();
	}	
	
	/*
	 * Description: generates a random salt string to be used with password validation
	 *
	 * Returns:		a string containing the random salt, length 22 charecters
	 */
	private static function generateSalt()
	{
		return substr(sha1(mt_rand()),0,22);  
	}
	
	
	/*
	 * Description: This is basically the setter method for a password of a user
	 *				though it doesn't actually set the new password it does
	 *				calculate a new salt ( if none is available ) and returns
	 *				a hashed version of the password
	 */
	public function newHashedPassword($password)
	{
		if( !isset($this->salt) || strlen($this->salt) < 2 ) 
		{
			$this->salt = self::generateSalt();
		}
		return hash("sha512", $password.$this->salt."Aab12021XH");
	}
	
	/*
	 * Description:	validates a username/password combination
	 *
	 * Returns:		a User instance connected to this username/password combination
	 *				returns null on failure
	 */
	public static function login($username, $password)
	{
		if( strlen($username) > 0 && strlen($password) > 0 )
		{
			$users = Users::defaultTable();
			$result = $users->findWhere("username = ?",$username);
			if( count($result) < 1 )
			{
				return NULL;
			}
			$user = $result[0];
			if( $user->salt && strlen($user->salt) > 2 )
			{
				if( $user->password == hash("sha512", $password.$user->salt."Aab12021XH") )
				{
					return $user;
				}
			}
		}
		return NULL;
	}
	
	public function changePassword($oldPassword,$newPassword)
	{
		if( User::login($this->username,$oldPassword) )
		{
			$this->password = $this->newHashedPassword($newPassword);
			$this->save();
			return true;
		}
		return false;
	}
	
	public function addReputation($delta)
	{
		$reputation = (int)$this->reputation;
		$reputation += $delta;
		$this->reputation = $reputation;
		$this->save();
	}
	
	public function removeReputation($delta)
	{
		$reputation = (int)$this->reputation;
		$reputation -= $delta;
		$this->reputation = $reputation;
		$this->save();
	}
	
	/*
	 * Description: returns a string representation of this object
	 *				either the user's nickname or the user's username depedning on user preference and
	 *				avalailability of the nickname
	 */
	public function displayString($showFlag = false)
	{
		$prefix = '';
		$suffix = '';
		if( $showFlag )
		{
			if( ! $this->flag )
			{
				$this->flag = 'nl';
			}
			$prefix = '<span class="flag" style="background-image:url(\''.Config::resourceFolderPath.'images/flags/'.$this->flag.'.png\');">';
			$suffix = '</span>';
		}
		if( $this->honorary )
		{
			$prefix = $prefix . 'Honorary^';
		}
		if( strlen($this->nickname) > 0 )
		{
			return $prefix.$this->nickname.$suffix;
		}
		return $prefix.$this->username.$suffix;
	}
	
	// legacy
	public function getDisplayString()
	{
		return $this->displayString();
	}
	
	/**
	 * Returns the current user avatar
	 * or the default avatar if the user has not set any
	 */
	public function avatarURL()
	{
		if( strlen($this->avatarURL) > 3 )
		{
			return $this->avatarURL;
		}
		return Config::defaultAvatar;
	}
	
	/**
	 * Returns the current user banner image URL
	 * or the default banner image
	 */
	public function bannerURL()
	{
		if( strlen($this->bannerURL) > 3 )
		{
			return $this->bannerURL;
		}
		return Config::defaultUserBanner;
	}
	
	
}