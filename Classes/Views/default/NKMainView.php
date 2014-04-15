<?php
class NKMainView extends NKView
{
	public $contentView = NULL;

	public function __construct($path = NULL, $contentView = NULL)
	{
		$this->contentView = $contentView;
		if( !$path )
		{
			$path = Config::layoutPath;
		}
		if( file_exists($path) )
		{
			$this->_templatePath = $path;
		}
		else
		{
			throw new Exception("Unable to find main template path", 500);
		}
	}
	
	public function render()
	{
		$pageView = $this->contentView;
		
		// if we have an ajax request we only render the base view
		if( NKWebsite::sharedWebsite()->request->isAjaxRequest() )
		{
			$pageView->render();
			return;
		}
		
		// the layout file SHOULD takes care of rendering the page view
		if( $this->_templatePath )
		{
			include($this->_templatePath);
		}
	}
}