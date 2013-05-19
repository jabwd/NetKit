<?php
class Permissions extends NKTable
{
	public $tableName 	= "permissions";
	public $rowClass 	= "Permission";
	
	private $rows = NULL;
	
	
	/*
	 * This function determines whether the user has permissions to access a certain
	 * part of the website.
	 */ 
	public function permissionForUserID($permission,$userID)
	{
		// internally we also cache this, we could also fetch it on a per row basis
		// but storing this in memory is easier especially if we have to check for multiple
		// permissions in 1 request
		if( !$this->rows )
		{
			$this->rows = $this->findWhere("userID = ?",$userID);
		}
		if( count($this->rows) > 0 )
		{
			foreach($this->rows as $row)
			{
				if( $row->userID === $userID && ($row->permission === $permission || $row->permission == 'highAdmin') )
				{
					return true;
				}
			}
		}
		return false;
	}
}
?>