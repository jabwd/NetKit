<?php
class NotAllowedException extends Exception
{
	public function __construct()
	{
		parent::__construct("You are not allowed to visit this page", 403);
	}
}
?>