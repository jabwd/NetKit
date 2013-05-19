<?php
class NKMenu extends NKView
{
	public $items;
	
	public function NKMenu()
	{
	}
	
	public function render()
	{
		echo '<div class="toolbar">';
		foreach($this->items as $item)
		{
			$item->render();
		}
		echo '</div>';
	}
	
	public function addItem($item)
	{
		$this->items[] 	= $item;
		$item->menu 	= $this;
	}
	
	public function addItems($array)
	{
		if( !is_array($array) )
		{
			throw new Exception("addItems() expects an array as input",500);
		}
		foreach($array as $item)
		{
			$this->addItem($item);
		}
	}
	
	public function removeItem($item)
	{
		for($i=0;$i<count($this->items);$i++)
		{
			if( $this->items[$i] === $item )
			{
				unset($this->items[$i]);
			}
		}
		$item->menu = null;
	}
	
	/*
	 * Description:	This method is supposed to be subclassed by your menu in question. Its purpose
	 *				is to provide a menuItem a way of figuring out whether it should draw itself in
	 *				a 'current' state
	 *
	 * Returns:		a NKMenuItem instance that is the current item or null if there isn't any
	 */
	public function currentItem()
	{
		return null;
	}
}