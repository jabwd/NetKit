<?php
//----------------------------------------------------------//
// Autoloading												//

// Potential problem: What happens if the amount of classes
// stored in $GLOBALS['classes'] becomes really big?
// Potential fix: use the caching more directly ( probably slower )
// and therefore do not get PHP to copy a huge array into its runtime
spl_autoload_register(function($className) {
	if( $GLOBALS['classes'][$className] ) {
		require_once $GLOBALS['classes'][$className];
		return;
	} else {
		// just in case
		if( $className == 'PageNotFoundException' ) {
			throw new Exception('Page not found', 404);
		}

		// default behavior
		if( Config::debugMode ) {
			throw new Exception("Cannot find class ".$className, 500);
		}
		throw new PageNotFoundException();
	}
});


//----------------------------------------------------------//
// Error handling											//

function handleException($exception)
{
	$_SESSION['exception'] = serialize($exception);
	if( Config::debugMode ) {
		print_r($exception);
		exit;
	}
	if( headers_sent() )
	{
		print_r($exception);
		exit;
	}
	else
	{
		redirect('/error/index/'.$exception->getCode());
	}
	
	// TODO: Why is libnetkit handling this ?
	print_r($exception->getMessage());
	print_r($exception->getTrace());
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



//----------------------------------------------------------//
// Standard functions										//

/*
 * Description: this function creates a cache file that allows the NKWebsite class to load all the
 *				dependencies in one go. This means that the system only needs to load 1 single file
 *				which if it really has to go QUICK can be loaded into RAM memory to handle requests
 *				more quickly.
 */
function createWebsiteCache()
{
	// try to use MemCached
	$memcache = new Memcache;
	$memcache->addserver("localhost", 11211)or die("Cannot connect memcache");
	if( $memcache )
	{
		$cache = cacheForDirectory(".");
		$memcache->set("websiteClasses",$cache);
		return;
	}
	$cache = cacheForDirectory(".");
	$data = json_encode($cache);
	file_put_contents("Cache/classes.json", $data);
}

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
		if( $pathParts['extension'] != 'php' && !is_dir($filePath) )
		{
			continue;
		}
			
		if( is_dir($filePath) ) {
			// scan next directory
			if( $filePath === "./.git" ) {
				continue;
			}
			$output = array_merge($output, cacheForDirectory($filePath));
		}
		else if( file_exists($filePath) ) {
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
 * getMicroTime() returns a timestamp in miliseconds
 *
 * @return long timestamp in miliseconds since the unix epoch
 */
function getMicroTime()
{
	$tstart = explode(" ",microtime());
	return ($tstart[1] + $tstart[0]);
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