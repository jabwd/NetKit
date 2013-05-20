<?php
class Comments extends NKTable
{
	public $tableName 	= "comments";
	public $rowClass	= "Comment";
	
	public $extraTable = array(
		'Users'=>'authorID'
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
			self::defaultTable()->setSingleSort('created ASC');
			return self::defaultTable()->findWhere("contentType = ? AND contentID = ?", $type, $id);
		}
	}
}