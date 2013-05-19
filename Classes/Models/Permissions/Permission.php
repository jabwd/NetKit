<?php
class Permission extends NKTableRow
{
	public $tableName = "Permissions";
	
	public function save()
	{
		// validate the current user's access
		// this is very low level and is a last line of defense
		if( NKSession::access("permissions.manage") )
		{
			parent::save();
		}
		else
		{
			throw new Exception("User tried to modify permissions without the right permissions set himself",403);
		}
	}
}
?>