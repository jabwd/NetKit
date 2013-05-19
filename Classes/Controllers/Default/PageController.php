<?php
/*
 * Author: Antwan van Houdt
 * Created: 12-21-2012
 * Version:	1.0
 *
 * The NKPageController is a controller that makes use of 
 * a certain backing store in order to fetch 
 * and display a page. It is very useful for allowing non-geeks
 * access to the looks of pages, simply modify the data in the backing store
 * and refresh the page..
 */
class PageController extends NKActionController
{
	public function handleRequest($request = null)
	{
		$this->view = new NKView('page/'.$request->actionName, $this, 'NetKit/Classes/Views/templates/');
		return $this->view->pageExists();
	}
	
	/**
	 * Fetches the current page object
	 * if an id is given in the current NKRequest
	 *
	 * @return Page the Page instance
	 */
	protected function getPage()
	{
		$pages = new Pages();
		$page = $pages->findMain();
		if( ! $page )
		{
			throw new PageNotFoundException();
		}
		return $page;
	}
	
	

	public function indexAction()
	{
		$page = $this->getPage();
		
		$this->name 		= $page->title;
		$this->view->page 	= $page;
	}
	
	public function viewAction()
	{
		$this->indexAction();
	}
	
	public function listAction()
	{
		if( !NKSession::access("pages.manage") )
		{
			throw new NotAllowedException();
		}
		
		$this->name = "Pages list";
		$pages = new Pages();
		
		
		$this->view->pagesList = $pages->fetchAll();
	}
	
	public function createAction()
	{
		if( !NKSession::access("pages.manage") )
		{
			throw new NotAllowedException();
		}
		if( $_POST['save'] )
		{
			$title 		= $_POST['title'];
			$content 	= $_POST['content'];
			
			$page 			= new Page();
			$page->title 	= $title;
			$page->content 	= $content;
			$pageID 		= $page->save();
			
			$this->view->success = true;
			redirect("/page/view/".$pageID);
		}
		else if( $_POST['cancel'] )
		{
			redirect('/page/list/');
		}
	}
	
	public function editAction()
	{
		if( !NKSession::access("pages.manage") )
		{
			throw new NotAllowedException();
		}
		
		$page = $this->getPage();
		
		if( $_POST['save'] )
		{
			$title 		= $_POST['title'];
			$content 	= $_POST['content'];
			
			$content = str_replace("<br />", "\n", $content);
			
			$page->title 	= $title;
			$page->content 	= $content;
			$page->save();
			
			$this->view->success = true;
		}
		else if( $_POST['cancel'] )
		{
			redirect('/page/list/');
		}
		
		$this->view->page = $page;
	}
	
	public function deleteAction()
	{
		if( !NKSession::access("pages.manage") )
		{
			throw new NotAllowedException();
		}
		
		$page = $this->getPage();
		
		if( $_POST['confirm'] )
		{
			$page->delete();
			redirect('/page/list/');
		}
		else if( $_POST['cancel'] )
		{
			redirect('/page/list/');
		}
		
		$this->view->page = $page;
	}
}