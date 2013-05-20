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
			
			NKSession::currentUser()->addReputation(Config::CommentCreateReputation);
			
			// limit the amount of comments a user can create
			$count = (int)$_SESSION['userCommentCount'];
			$count++;
			$_SESSION['userCommentCount'] = $count;
			$_SESSION['userLastCommentStamp'] = time();
		}
		parent::save();
	}
	
	/**
	 * Makes sure that the author's reputation is decreased properly
	 * for the deleting of the comment ( either by himself or an admin )
	 */ 
	public function delete()
	{
		$currentUser = NKSession::currentUser();
		if( $currentUser )
		{
			$currentUser->removeReputation(Config::CommentCreateReputation);
		}
		parent::delete();
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
			if( $user->reputation > Config::CommentUnlimitedReputation ||
				NKSession::access('comments.manage') )
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