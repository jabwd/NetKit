<?php
class CommentController extends NKDefaultController
{
	public function createAction()
	{
		if( NKSession::currentUser() && 
			$_POST['postComment'] && 
			strlen($_POST['content']) > 1 && 
			strlen($_POST['content']) < 2000 &&
			Comment::shouldPostComment() > 0 ) 
		{
			// create the new comment
			$comment = new Comment;
			$comment->authorID 		= NKSession::currentUser()->id;
			$comment->contentType 	= (int)$_POST['contentType'];
			$comment->contentID		= (int)$_POST['contentID'];
			$comment->content		= strip_tags($_POST['content']);
			$comment->replyTo		= (int)$_POST['replyTo'];
			
			// the comment automatically ups the user' reputation
			$commentID = $comment->save();
			
			// notify the user we replied to
			$userID = (int)$_POST['replyToUser'];
			if( $userID > 0 )
			{
				$format = "%s has replied to your comment";
				$message = sprintf($format, NKSession::currentUser()->displayString(false));
				Notification::sendNotification($userID, $message, $_POST['requestURI'].'#comment_'.$commentID);
			}
			
			// update the comment count
			$contentItem = ContentMapper::contentForContentTypeAndID($comment->contentType, $comment->contentID);
			if( $contentItem )
			{
				$count = (int)$contentItem->commentCount;
				$count++;
				$contentItem->commentCount 	= $count;
				$contentItem->lastActive	= time();
				$contentItem->save();
			}
			redirect($_POST['requestURI'].'#comment_'.$commentID);
		}
		else
		{
			// report the error to the user somehow
			$result = Comment::shouldPostComment();
			if( $result === -1 )
			{
				$this->view->errors[] = 'Please keep in mind that you can only post 1 comment every '.Config::UserMinCommentCooldown.' seconds';
			}
			else if( $result === -2 )
			{
				$this->view->errors[] = 'A normal user can only post '.Config::UserMaxComments.' comments every session';
			}
			else
			{
				$this->view->errors[] = 'An unknown error occured, please try posting your comment again later. If you want to save the stuff you typed just hit back on your web browser and it will make sure that your post is still there. (0x'.$result.')';
			}
		}
	}

	public function deleteAction()
	{
		$comment 		= Comments::defaultTable()->findMain();
		$contentType 	= (int)$comment->contentType;
		$contentID 		= (int)$comment->contentID;
		if( !$comment )
		{
			throw new PageNotFoundException();
		}
		
		if( $this->hasAdmin($comment) && $_POST['delete'] )
		{			
			// notify the author that his comment was deleted
			$isAuthor = $this->isAuthor($comment);
			if( !$isAuthor && $comment->authorID > 0 )
			{
				$contentTitle 	= ContentMapper::titleForContentTypeAndID($contentType, $contentID);
				$URL			= ContentMapper::URLForContentTypeAndID($contentType, $contentID);
				$format 		= "%s has deleted your comment on %s";
				$message = sprintf($format, NKSession::currentUser()->displayString(false), $contentTitle);
				Notification::sendNotification($comment->authorID, $message, $URL);
			}
			
			
			// finally, delete the comment
			// this also takes care of removing the appropriate amount of reputation 
			// of the author of the comment
			// just like how save() does for adding it
			$comment->delete();
			
			// update the comment count
			$contentItem = ContentMapper::contentForContentTypeAndID($contentType, $contentID);
			if( $contentItem )
			{
				$count = (int)$contentItem->commentCount;
				$count--;
				$contentItem->commentCount = $count;
				$contentItem->save();
			}
			
			// redirect the user back from where he came from
			redirect(ContentMapper::URLForContentTypeAndID($contentType, $contentID));
		}
		else if( $_POST['cancel'] )
		{
			redirect(ContentMapper::URLForContentTypeAndID($contentType, $contentID));
		}
		
		$this->view->comment = $comment;
	}
	
	public function editAction()
	{
		$comment = Comments::defaultTable()->findMain();
		if( !$comment )
		{
			throw new PageNotFoundException();
		}
		if( !$this->hasAdmin($comment) )
		{
			throw new NotAllowedException();
		}
		
		if( $_POST['save'] )
		{
			// we should notify the author 
			// if an admin has modified his comment
			if( !$this->isAuthor($comment) )
			{
				$contentTitle 	= ContentMapper::titleForContentTypeAndID($comment->contentType, $comment->contentID);
				$URL			= ContentMapper::URLForContentTypeAndID($comment->contentType, $comment->contentID);
				$format 		= "%s has edited your comment on %s";
				$message = sprintf($format, NKSession::currentUser()->displayString(false), $contentTitle);
				Notification::sendNotification($comment->authorID, $message, $URL);
			}
			
			if( strlen($_POST['content']) > 1 && strlen($_POST['content']) < 2000 )
			{
				$comment->content = strip_tags($_POST['content']);
				$comment->save();
				redirect(ContentMapper::URLForContentTypeAndID($comment->contentType, $comment->contentID));
			}
			else
			{
				$this->view->errors[] = 'The comment needs to smaller than 400 and at least bigger than 3 characters ( '.
										strlen($_POST['content']).
										' current )';
			}
		}
		else if( $_POST['cancel'] )
		{
			redirect(ContentMapper::URLForContentTypeAndID($comment->contentType, $comment->contentID));
		}
		
		$this->view->comment = $comment;
	}
	
	/**
	 * Determines whether the current user has admin rights
	 * of the given comment. Authors should be able to delete
	 * their own comments etc.
	 *
	 * @param Comment the comment to test
	 * @return boolean true or false depending on being having rights
	 */
	public function hasAdmin(Comment $comment)
	{
		if( NKSession::access("comments.manage") )
		{
			return true;
		}
		return $this->isAuthor($comment);
	}
	
	/**
	 * This function determines whether the current user
	 * is the author of the given comment item
	 *
	 * @param Commment comment item to test for author status
	 * @return boolean
	 */
	public function isAuthor(Comment $comment)
	{
		if( $comment->authorID === NKSession::currentUser()->id &&
			$comment->authorID > 0 )
		{
			return true;
		}
		return false;
	}
}