<?php
spl_autoload_register(function($className)
{
	if( isset($GLOBALS['classes'][$className]) )
	{
		require_once $GLOBALS['classes'][$className];
		return;
	}
	else
	{
		if( Config::debugMode )
		{
			throw new Exception("Cannot find class ".$className, 500);
		}
		throw new Exception('Page not found', 404);
	}
});

function handleException($exception)
{
	$code = $exception->getCode();
	if( $code < 400 || $code > 410 )
	{
		// more advanced 'graphical' error handling
		$code = 500;
	}
	echo '<!DOCTYPE html><html>';
	echo '<head><style type="text/css">
	body
	{
		padding-left:20px;
		color:rgb(100, 100, 100);
		font-family:"Lucida Grande", "Helvetica";
	}
	</style>';
	echo '</head><body>';
	echo '<h1>',$code,'</h1>';
	echo $exception->getMessage().'<br /><br />';
	if( Config::debugMode )
	{
		$trace = $exception->getTrace();
		foreach($trace as $line)
		{
			$class = '';
			if( isset($line['class']) )
			{
				$class = $line['class'];
			}
			echo '[',$line['line'],'] <b>',$class,'::',$line['function'],'</b> in (',$line['file'],')<br />';
		}	
	}
	echo '<a href="/">Home</a></body></html>';
	exit;
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno))
	{
		return; // this error code is not included in error_reporting
	}
	throw new Exception($errstr . " (file $errfile, line $errline)", 0);
	return false; // let PHP deal with anything not handled here
}
set_exception_handler('handleException');
set_error_handler("myErrorHandler");

function cacheForDirectory($dir)
{
	$files 	= scandir($dir);
	$output = array();
	foreach($files as $file)
	{
		$filePath = $dir.'/'.$file;
		
		// don't make us go back in the file system :P
		if( $file === "." || $file === ".." )
		{
			continue;
		}
			
		$pathParts = pathinfo($filePath);
		
		// ignore non-php files
		if( !is_dir($filePath) && $pathParts['extension'] != 'php' )
		{
			continue;
		}
			
		if( is_dir($filePath) )
		{
			// scan next directory
			// skip the folders we're not interested in ( where classes
			// should not be in in the first place )
			if( $filePath === "./.git" 								||
				$filePath === "./NetKit/generator" 					||
				$filePath === "./Website/Classes/Views/templates" 	||
				$filePath === './Website/Classes/Views/layout' 		||
				$filePath === "./NetKit/Classes/Views/templates"	||
				$filePath === './public' )
			{
				continue;
			}
			$output = array_merge($output, cacheForDirectory($filePath));
		}
		else if( file_exists($filePath) )
		{
			// Add the file to our array, its name without the extension
			// as key and the filepath as value
			$output[basename($file, ".php")] = $filePath;
		}
	}
	return $output;
}


/**
 * Wraps in the redirecting stuff of PHP
 * in a handy small function you can call
 */
function redirect($redirect)
{
	header("location: ".$redirect);
	exit; // done here, save the server some time.
}

/**
 * Similar to str_replace but then makes sure
 * it is only executed once
 *
 * @return string the resulting string after the find and replace
 */
function str_replace_once($needle, $replace, $haystack)
{ 
	$pos = strpos($haystack, $needle);
	if( $pos === false )
	{
		return $haystack; 
	}
    return substr_replace($haystack, $replace, $pos, strlen($needle)); 
}

/**
 * Checks the given string for the given minimum and maximum
 * string length. Used for user input control
 *
 * @param string 	$string the string to check
 * @param int	 	$minimum the minimum length of the string
 * @param int		$maximum the maximum length of the string
 * @return boolean 	whether the string is valid or not
 */
function checkStringLength($string, $minimum, $maximum)
{
	$len = strlen($string);
	if( $len > $maximum || $len < $minimum )
	{
		return false;
	}
	return true;
}