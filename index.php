<?php
@set_time_limit(0);
error_reporting(E_ALL &~ E_WARNING);

require_once __DIR__.'/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

$loader->registerNamespaces(array(
    'Finder' => __DIR__,
    'Common' => __DIR__
));

$loader->register();

use Finder\Parser;

try {
	$parser = new Parser;
	$parser->handle();
} catch( \Exception $e) {
	echo $e->getMessage();
}