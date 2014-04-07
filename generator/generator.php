<?php
require "commandLine.php";

$arguments = getopt("p:t:m:r:");

/**
 * Generator for a page set
 */
if( isset($arguments['p']) )
{
	printLine("Generating new controller + view");
	
	$name 		= strtolower($arguments['p']);
	if( strlen($name) < 1 || strlen($name) > 20 )
	{
		printLine("[Error] Incorrect page name provided: ".$name);
		exit;
	}
	$controller = file_get_contents("base/Controllers/DefaultController.php");
	$controller = str_replace("__CLASSNAME__", ucfirst($name)."Controller", $controller);
	
	$directory = "../../Website/Classes/";
	$controllerDir = $directory."Controllers/".ucfirst($name)."Controller.php";
	
	if( file_exists($controllerDir) )
	{
		printLine("[Error] controller already exists");
		exit;
	}
	else
	{
		file_put_contents($controllerDir, $controller);
		printLine("Generated and created the controller");
	}
	
	if( !is_dir($directory.'Views/templates/'.$name) )
	{
		mkdir($directory.'Views/templates/'.$name);
		file_put_contents($directory.'Views/templates/'.$name.'/index.php', '');
		printLine("Generated and created the view directory + view");
	}
}

/**
 * Geerator for a table set
 */
if( isset($arguments['m']) && isset($arguments['t']) && isset($arguments['r']) )
{
	printLine("Generating new tableset models");
	
	$className 	= ucfirst(($arguments['m']));
	$rowName 	= ucfirst(($arguments['r']));
	$tableName	= $arguments['t'];
	
	$class 		= file_get_contents('base/Models/Table.php');
	$rowClass 	= file_get_contents('base/Models/TableRow.php');
	
	$rowClass 	= str_replace("__ROWCLASS__", $rowName, $rowClass);
	$rowClass	= str_replace("__CLASSNAME__", $className, $rowClass);
	$class 		= str_replace("__CLASSNAME__", $className, $class);
	$class 		= str_replace("__ROWCLASS__", $rowName, $class);
	$class 		= str_replace("__TABLENAME__", $tableName, $class);
	
	$basePath = '../../Website/Classes/Models/';
	$rowPath = $basePath.$rowName.'.php';
	$classPath = $basePath.$className.'.php';
	
	file_put_contents($rowPath, $rowClass);
	file_put_contents($classPath, $class);
	
	printLine("Created table + tableRow classes");
}

/**
 * Generator for a CRUD set
 */
if( isset($arguments['c']) )
{
}

printLine("Generator version 1.0");