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
		include($this->_templatePath);
	}
	
	/**
	 * Used to determine whether the view thinks it can render
	 * itself without any issues ornot
	 * A use case could be when you have a custom NKView that does
	 * not require a template file, so you override this method
	 * to always return true
	 *
	 * @return boolean
	 */
	public function pageExists()
	{
		if( file_exists($this->_templatePath) )
		{
			return true;
		}
		return false;
	}
	
	/**
	 * A widget is a snippet script to be used in several different
	 * template files. It allows you to write more easy to maintain
	 * code by not writing the same thing several times over
	 *
	 * @return void
	 */
	public function widget($path = null, $otherPrefix = null)
	{
		$prefix = Config::widgetPath;
		if( $otherPrefix )
		{
			$prefix = $otherPrefix;
		}
		$path = $prefix.$path.'.php';
		if( file_exists($path) )
		{
			include $path;
		}
	}
}