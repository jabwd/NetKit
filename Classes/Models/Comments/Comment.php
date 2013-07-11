<?php
class Comment extends NKTableRow
{
	public $tableName = "Comments";
	
	/**
	 * Executes some user specific actions before
	 * inserting the new row into the table
	 */
	public function save()
	{
		// determine whether this is actually a comment instance that is going
		// to be inserted no save()
		if( $this->shouldInsert() )
		{
			if( !self::shouldPostComment() )
			{
				return; // stop here
			}
			$count = (int)$_SESSION['userCommentCount'];
			$count++;
			$_SESSION['userCommentCount'] = $count;
			$_SESSION['userLastCommentStamp'] = time();
		}
		return parent::save();
	}
	
	/**
	 * Determines whether the current user is allowed
	 * to post any more comments. This is an anti spam
	 * feature of the website
	 *
	 * @return boolean
	 */
	public static function shouldPostComment()
	{
		$user = NKSession::currentUser();
		if( $user )
		{
			// certain users are always allowed to post comments
			if(	NKSession::access('comments.manage') )
			{
				return true;
			}
			
			// for the average joes, they are limited to a certain amount
			// per time and max amount per session
			$time = (int)$_SESSION['userLastCommentStamp'];
			if( (time()-$time) > Config::UserMinCommentCooldown )
			{
				if( $_SESSION['userCommentCount'] < Config::UserMaxComments )
				{
					return 2;
				}
				else
				{
					return -2;
				}
			}
			else
			{
				return -1;
			}
		}
		return 0;
	}
}