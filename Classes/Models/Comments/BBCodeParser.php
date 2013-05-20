<?php
/**
 * BB-Code compiler written by Antwan van Houdt
 *
 * Exceptions that can be thrown:
 * - Incorrect input given:
 *		no content given to parse while the parse() function is called
 *
 */

class BBCodeParser
{
	protected $_raw;
	protected $_parsed;
	
	protected $_codeToHTML = array(
		'b' 			=> '<b>',
		'/b' 			=> '</b>',
		'i' 			=> '<i>',
		'/i' 			=> '</i>',
		'u' 			=> '<u>',
		'/u' 			=> '</u>',
		'subheading' 	=> '<span class="subheading">',
		'/subheading' 	=> '</span>',
		'a'				=> '<a href="%s">',
		'/a'			=> '</a>',
		'img'			=> '<img src="',
		'/img'			=> '"/>',
		'youtube'		=> '<iframe width="560" height="315" src="http://www.youtube.com/embed/%s" style="border:none;" allowfullscreen></iframe>',
	);
	
	public function __construct($content = '')
	{
		$this->_raw = $content;
		if( $this->shouldParse() )
		{
			$this->parse();
		}
	}
	
	/**
	 * Determines whether the raw content shoudl be (re)parsed
	 * can be used in order to speed up the result() function
	 * if called more than once
	 */
	public function shouldParse()
	{
		if( $this->_raw && !$this->_parsed )
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Parses the raw content into HTML output
	 */
	public function parse()
	{
		$this->_parsed = ''; // truncate existing output
		
		if( !$this->_raw )
		{
			throw new Exception('BB-Code compiler: incorrect input given', 500);
		}
		
		$buffer = $this->_raw;
		$offset = 0;
		$endPosition = strpos($buffer, ']');
		$cnt = 0;
		while( strlen($buffer) > 0 && $endPosition >= 0 && $endPosition !== FALSE && $cnt < 500 )
		{			
			$previous = substr($buffer, 0, $endPosition);
			$position = strrpos($previous, '[');
			
			// no bb found, skip
			if( $position === FALSE )
			{
				$this->_parsed 	.= substr($buffer, 0, $endPosition+1);
				$buffer 		= substr($buffer, $endPosition+1);
			}
			else
			{
				// bb found ?
				$code = substr($previous, $position+1, $endPosition-1);
				
				// check for parameters of the bb element
				// format: 'a="test","bla"'
				$prm = strpos($code, "=");
				
				// check for any paremeters, store them in $params
				$params = NULL; // unset, coz of previous loops
				if( $prm > 0 )
				{
					$complete = $code;
					$code = substr($code, 0, $prm);
					$complete = str_ireplace($code.'="', "", $complete);
					$complete = substr($complete, 0, strlen($complete)-1);
					$params = explode('","', $complete);
					
					// in this case its just 1 parameter
					if( count($params) < 1 )
					{
						$params[0] = $complete;
					}
				}
				
				$HTML = $this->_codeToHTML[$code];
				if( $HTML )
				{
					$tail = '';
					if( $position > 0 )
					{
						$tail = substr($buffer, 0, $position);
					}
					
					if( count($params) > 0 )
					{
						$HTML = vsprintf($HTML, $params);
					}
					
					$this->_parsed .= $tail.$HTML;
					$buffer	= substr($buffer, $endPosition+1);
				}
				else
				{
					$this->_parsed 	.= substr($buffer, 0, $endPosition+1);
					$buffer 			= substr($buffer, $endPosition+1);
				}
			}
			$endPosition = strpos($buffer, ']');
			$cnt++;
		}
		
		if( strlen($buffer) > 0 )
		{
			$this->_parsed .= $buffer;
		}
	}
	
	/**
	 * This method figures out whether the content is already parsed or not
	 * parses it otherwise and then returns the result
	 */
	public function result()
	{
		if( $this->shouldParse() )
		{
			$this->parse();
		}
		return $this->_parsed;
	}
}