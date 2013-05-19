<?php
class NKMenuItem
{
	public $title;
	public $URL;
	public $class;
	public $tag;
	
	public $menu;
	
	public function __construct($title = 'MenuItem', $URL = '', $class = '', $tag = 'MenuItem')
	{
		$this->title 	= $title;
		$this->URL 		= $URL;
		$this->class 	= $class;
		$this->tag 		= $tag;
	}
	
	public function render()
	{
		echo '<a href="'.$this->URL.'" class="'.$this->class.'">'.$this->title.'</a>';
	}
}