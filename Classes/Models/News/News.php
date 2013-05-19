<?php
class News extends NKTable {
	const  ContentType	= 1;
	public $tableName 	= "news";
	public $primaryKey	= "id";
	public $rowClass 	= "NewsItem";
	
	public $extraTable = array(
		'Users'=>'authorID'
	);
}