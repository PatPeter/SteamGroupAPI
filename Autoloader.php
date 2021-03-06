<?php
require('Configuration.php');
set_include_path(get_include_path() . '.');

spl_autoload_register(function($class) {
	if (strstr($class, 'Crypt') !== false || strstr($class, 'File') !== false ||
		strstr($class, 'Math') !== false || strstr($class, 'Net') !== false) {
		return;
	}
	
	$class = ltrim($class, '\'');
	$filename  = '';
	$namespace = '';
	if (($lastNamespacePosition = strripos($class, '\''))) {
		$namespace = substr($class, 0, $lastNamespacePosition);
		$class = substr($class, $lastNamespacePosition + 1);
		$filename  = str_replace('\'', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	//$filename .= str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
	$filename .= $class . '.php';
 
	require $filename;
});
