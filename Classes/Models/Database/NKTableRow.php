<?php
class NKTableRow
{
	private $table;
	private $validated;
	
	public $tableName;
	
	public function __construct($table = null)
	{
		$this->table = $table;
	}
	
	/**
	 * Returns: inserted row ID or null when its just saving the row
	 *			throws an error when there is no possibility of saving
	 */
	public function save()
	{
		if( !$this->validated )
		{
			if( !$this->validate() )
			{
				throw new Exception("Cannot save a tablerow with incorrect values!", 500);
			}
		}
		
		// determine whether we should insert or not
		if( !$this->shouldInsert() )
		{
			$this->table->update($this);
			return;
		}
		else if( $this->tableName && strlen($this->tableName) > 1 )
		{
			// retry, but then try to instantiate the table class yourself
			// and save the row after
			$tableName = $this->tableName;
			$this->table = new $tableName();
			
			// try to insert the row
			if( $this->table ) {
				return $this->table->insert($this);
			}
		}
		
		// at this point we neither saved nor inserted and should call in an error
		throw new Exception("Cannot sove row when there is no table nor tableName given", 500);
	}
	
	/**
	 * Returns: void -- deletes the current row in the given table
	 *			throws an exception if it can't delete
	 */
	public function delete()
	{
		// delete the table if we have a given table instance
		// otherwise we don't know to whom we belong, so fail.
		if( $this->table )
		{
			$this->table->delete($this);
		}
		else
		{
			throw new Exception("Cannot delete table row as no table instance is known", 500);
		}
	}
	
	/**
	 * Returns:	true when the current row is going to be inserted rather than saved
	 *			when save() is going to be called.
	 *			The reason to use this method is mostly in subclasses
	 *			to apply some kind of low level blocking mechanisms before inserting rows
	 *			( like last line of defense against malicious input )
	 */
	public function shouldInsert()
	{
		return ($this->table == null);
	}
	
	/**
	 * Validates the current instance according to the standards
	 * described in the comment section of the table.
	 *
	 * @param Reference $errorMessage, if you want to fully describe what went
	 *					wrong to the user
	 *
	 * @returns Boolean true or false		
	 */
	public function validate(&$errorMessage = NULL)
	{
		$tableName = $this->tableName;
		if( $tableName )
		{
			$table = $tableName::defaultTable();
			if( $table )
			{
				$comments = $table->comments;
				
			}
		}
		$this->validated = true;
		return true;
	}
}