<?php
class Comments extends NKTable
{
	public $tableName 	= "comments";
	public $rowClass	= "Comment";
	
	public $extraTable = array(
		'authorID' => array(
			'Users',
			'author'
		)
	);
	
	/**
	 * Returns comments for the given contentType and contentID
	 *
	 * @param int $contentType
	 * @param int $contentID
	 * @return array list of comments
	 */
	public static function commentsForContent($type, $id)
	{
		if( $type > 0 && $id > 0 )
		{
			$limit = "LIMIT 0,".Config::CommentsPerPage	;
			$page = (int)NKWebsite::sharedWebsite()->request->valueForKey("page");
			if( $page > 0 )
			{
				$page--;
				$limit = "LIMIT ".($page*Config::CommentsPerPage).",".Config::CommentsPerPage;
			}
			return self::defaultTable()->fetchAll("contentType=".(int)$type." AND contentID=".(int)$id, $limit);
		}
		return NULL;
	}
}