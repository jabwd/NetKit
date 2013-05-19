<?php
class NKTabbedMenuItem extends NKMenuItem
{
	public function render()
	{
		if( !$this->menu )
		{
			throw new Exception("Cannot draw an NKMenuItem without a parent menu",500);
		}
		
		// edit the values to fit our 'needs'
		$oldURL 	= $this->URL;
		if( $this->URL && $this->tag )
		{
			$this->URL 	.= '/tab/'.$this->tag; 
		}
		$oldClass 	= $this->class;
		
		// figure out whether we are the current item
		if( $this->menu->currentItem() == $this )
		{
			if( strlen($this->class) > 0 )
			{
				$this->class .= ' current';
			}
			else
			{
				$this->class = 'current';
			}
		}
		
		// call the parent rendering function
		parent::render();
		
		// restore the values
		$this->URL 		= $oldURL;
		$this->class 	= $oldClass;
	}
}