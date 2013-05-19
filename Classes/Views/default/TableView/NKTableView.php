<?php
/*
 * Description: NKTableView is a subclass of NKView providing support of rendering lists ( tables ).
 *				Its purpose is to make it less of a hassle to write the list rendering code
 *				snice its mostly the same anyways and implementing nice features can take time / lots of copy
 *				pasting which we don't want.
 *
 *				Keep in mind that using NKTableView does add some minor overhead and that if you want to go
 *				for pure 100% performance you might want to think twice using it ( every row has its own object )
 */
class NKTableView extends NKView
{
	public $alternatingRows = true;
	public $rows			= null;
	
	public function NKTableView()
	{
		
	}
	
	public function render()
	{
		echo '<table class="list" style="border-top:1px solid rgb(152,16,8);">';
		$alternate = false;
		foreach($this->rows as $row)
		{
			// render the single row
			$row->alternate = $alternate;
			$row->render();
			
			// alternate the value
			$alternate = !($alternate);
		}
		echo '</table>';
	}
	
	public function addRow($row = null)
	{
		if( ! $this->rows )
		{
			// initialize
			$this->rows = array();
		}
		
		// add the row
		if( $row )
		{
			$this->rows[] = $row;
		}
	}
}