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
	 * @param Reference $errors, if you want to fully describe what went
	 *					wrong to the user
	 *
	 * @returns Boolean true or false		
	 */
	public function validate(&$errors = NULL)
	{
		$errors = array();
		$tableName = $this->tableName;
		if( $tableName )
		{
			$table = $tableName::defaultTable();
			if( $table )
			{
				$comments = $table->comments;
				foreach($comments as $comment)
				{
					$value = NULL;
					if( isset($this->$comment['column']) )
					{
						$value = $this->$comment['column'];
					}
					$constraint 	= $comment['comment'];
					
					if( $value && strlen($constraint) > 0 )
					{	
						// detect constraint method
						$pos = strpos($constraint, "[");
						$key = substr($constraint, 0, $pos);
						
						// validate an int constraint
						if( $key === "int" )
						{
							$args = substr($constraint, $pos+1, strlen($constraint)-$pos-2);
							if( strlen($args) < 1 )
							{
								$this->$comment['column'] = (int)$value;
								echo 'just casted to an int';
							}
							else
							{
								// detect how we want to validate our integer
								$first = substr($args, 0, 1);
								if( $first == ">" )
								{
									$argVal = substr($args, 1, strlen($args)-1);
									if( !($value > $argVal) )
									{
										$errors[] = $comment['column'].' should be bigger than '.$argVal;
									}
								}
								else if( $first == "<" )
								{
									$argVal = substr($args, 1, strlen($args)-1);
									if( !($value < $argVal) )
									{
										$errors[] = $comment['column'].' should be smaller than '.$argVal;
									}
								}
								else
								{
									// should be a 'in between' value
									$parts 	= explode(",", $args);
									$min 	= (int)$parts[0];
									$max 	= (int)$parts[1];
									
									if( !($value > $min && $value < $max) )
									{
										$errors[] = $comment['column'].' should be in between '.($min).' and '.($max).' is '.$value;
									}
								}
							}
						}
						else if( $key == 'regex' )
						{
							$pattern = substr($constraint, $pos+2, strlen($constraint)-$pos-4);
							$pattern = '/'.$pattern.'/';
							if( !preg_match($pattern, $value) )
							{
								$errors[] = $comment['column'].' is not valid according to our pattern';
							}
						}
						else if( $key == 'strlen' )
						{
							$args = substr($constraint, $pos+1, strlen($constraint)-$pos-2);
							// should be a 'in between' value
							$parts 	= explode(",", $args);
							$min 	= (int)$parts[0];
							$max 	= (int)$parts[1];
							
							$len = strlen($value);
							
							if( !($len > $min && $len < $max) )
							{
								$errors[] = $comment['column']. ' should be in between '.($min).' and '.($max).' is '.$len;
							}
						}
					}
				}
			}
		}
		
		// return the correct result
		// and flag whether this row
		// is properly validated or not
		if( count($errors) == 0 )
		{
			$this->validated = true;
			return true;	
		}
		else
		{
			$this->validated = false;
			return false;
		}
	}
}