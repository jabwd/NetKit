<?php
class NKMainView extends NKView
{
	public $contentView = null;

	public function __construct($path = null, $contentView = null)
	{
		if( ! $path ) {
			$path = Config::layoutPath;
		}
		if( file_exists($path) ) {
			$this->_templatePath = $path;
		} else {
			throw new Exception("Unable to find main template path", 500);
		}
		
		$this->contentView = $contentView;
	}
	
	/*
	 * This function is subclassed in order to render stuff
	 * on the screen
	 */
	public function render()
	{
		$pageView = $this->contentView;
		
		// if we have an ajax request we only render the base view
		if( NKWebsite::sharedWebsite()->request->isAjaxRequest() ) {
			$pageView->render();
			return; // done here.
		}
		
		// this happens when we do not have a view to render
		if( !$pageView ) {
			throw new PageNotFoundException();
		}
		
		if( $this->_templatePath ) {
			NKWebsite::sharedWebsite()->didRender = true;
			include($this->_templatePath);
		}
	}
}