<?php
class PageNotFoundException extends Exception
{	
	public function PageNotFoundException($extraMessage = "")
	{
		parent::__construct("Page not found".$extraMessage, 404);
	}
}
?>