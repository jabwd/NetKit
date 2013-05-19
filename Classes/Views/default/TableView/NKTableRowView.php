<?php
/*
 * Description: NKTableRow is a subclass  of NKView supposed to make it easier to render single rows
 *				for NKTableView. See NKTableView for more information.
 */
class NKTableRowView extends NKView
{
	public $columns		= null;
	public $alternate	= false;
	
	// if set this makes the row clickable
	public $URL			= null;

	public function NKTableRowView()
	{
		
	}
	
	public function render()
	{
		// just make sure our backing data store is valid
		// we do not need to check the count. If the columns
		// are not there or are of an invalid count that will me visible anyways..
		if( !$this->columns )
		{
			throw new Exception("No columns for table row",500);
		}
		
		// render the row
		echo '<tr';
		if( $this->URL )
		{
			if( $this->alternate )
			{
				echo ' class="alternate clickable"';
			}
			else
			{
				echo ' class="clickable"';
			}
			echo ' onclick="goToPage(\''.$this->URL.'\');"';
		}
		else if( $this->alternate )
		{
			echo ' class="alternate"';
		}
		echo '>';
		foreach($this->columns as $column)
		{
			// this allows us to not need an extra class to just wrap the extra options 1 column might have
			$extra = "";
			if( is_array($column) )
			{
				$extra 		= ' style="'.$column['style'].'"';
				$column 	= $column['value'];
			}
			echo '<td'.$extra.'>'.$column.'</td>';
			echo "\n"; // unix readability
		}
		echo '</tr>';
	}
}