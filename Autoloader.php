<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'C:\Program Files (x86)\Apache Software Foundation\Apache2.2\htdocs\libsteam');

spl_autoload_register(function($class) {
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
