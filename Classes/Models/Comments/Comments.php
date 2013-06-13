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
			return self::defaultTable()->fetchAll("contentType=".(int)$type." AND contentID=".(int)$id);
		}
		return NULL;
	}
}