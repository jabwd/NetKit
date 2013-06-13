<?php
class Messages extends NKTable
{
	public $tableName 	= "messages";
	public $rowClass 	= "Message";
	
	public $extraTable = array(
		'authorID' => array(
			'Users',
			'author'
		),
		'recipientID'=> array(
			'Users',
			'recipient'
		)
	);
}