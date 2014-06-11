<?php
class NKMenu
{
	private $menuItems = array();

	public function __construct($menuItems = NULL)
	{
		if( $menuItems )
		{
			$this->menuItems = $menuItems;
		}
	}

	public function render()
	{
		foreach($this->menuItems as $menuItem)
		{
			$extra = '';
			if( $menuItem->isCurrent() )
			{
				$extra = 'current ';
			}
			echo '<a href="'.$menuItem->link.'"';
			echo ' class="'.$extra.$menuItem->extraClass.'"';
			echo '>'.$menuItem->title."</a>".$menuItem->tail."\n\t\t\t";
		}
	}
}