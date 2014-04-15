<?php
class NKView
{
	protected 	$_templatePath;
	public 		$controller;

	public function __construct($templateFile = "", $controller = NULL, $templateBasePath = NULL)
	{
		$this->controller = $controller;
		if( !$templateBasePath )
		{
			$templateBasePath = Config::templatesPath;
		}
		
		// Generate the template path and verify whether it exists
		// or not, if it doesnt we throw an exception for a 404 error
		$path = $templateBasePath.$templateFile.'.php';
		if( file_exists($path) && strlen($templateFile) > 0 )
		{
			$this->_templatePath = $path;
		}
		else
		{
			throw new PageNotFoundException();
		}
	}
	
	/*
	 * Generates the HTML output for the current NKView instance
	 *
	 * @return void
	 */
	public function render()
	{
		// include and 'draw' the template file if we have one
		if( $this->_templatePath ) {
			include($this->_templatePath);
		}
	}
	
	public function widget($path = null, $otherPrefix = null) {
		$prefix = Config::widgetPath;
		if( $otherPrefix ) {
			$prefix = $otherPrefix;
		}
		$path = $prefix.$path;
		if( file_exists($path) ) {
			include $path;
		}
	}
	
	/**
	 * Description:	this method should be overridden by a subclass if it has situation
	 *				at which it should report that the page does not exist
	 *
	 * Returns:		true when page exists, false when it doesn't [insert you-dont-say]
	 */
	public function pageExists() {
		if( $this->_templatePath ) {
			return true;
		}
		return false;
	}
}