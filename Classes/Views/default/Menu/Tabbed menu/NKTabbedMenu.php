<?php
class NKTabbedMenu extends NKMenu
{
	private $defaultTab;
	
	public function __construct($defaultTab = '')
	{
		$this->defaultTab = $defaultTab;
	}
	
	public function currentItem($tab = '')
	{
		if( ! $tab )
		{
			$tab = NKWebsite::sharedWebsite()->request->valueForKey("tab");
		}
		foreach($this->items as $item)
		{
			if( $item->tag == null )
			{
				continue; // not of interest here
			}
			if( $item->tag === $tab )
			{
				return $item;
			}
			else if( $item->tag === $this->defaultTab )
			{
				$default = $item;
			}
		}
		
		if( $default )
		{
			return $default;
		}
		
		return null;
	}
}