<?php
class NewsItem extends NKTableRow {
	public $tableName = "News";
	
	public function getBackground() {
		if( $this->backgroundImage ) {
			return 'background-image:url(\''.trim($this->backgroundImage).'\');';
		}
		return "background-color:".$this->getRandomColor().";background-image:url('".Config::resourceFolderPath."images/logo.banner.png');";
	}
	
	protected function getRandomColor() {
		return "rgb(".rand(100,255).",".rand(100,255).",".rand(100,255).")";
	}
}
