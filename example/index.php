<?php

use ConstanzeStandard\Routing\Cache;
use ConstanzeStandard\Routing\Matcher;
use ConstanzeStandard\Routing\RouteCollection;

require __DIR__ . '/../vendor/autoload.php';

$collection = new RouteCollection(__DIR__ . '/cacheFile.php');

$collection->add('GET', '/user/{id:\d+}', 'handler', ['name' => 'user.id']);

// $cache = new Cache($collection, __DIR__ . '/cacheFile.php');
$collection->cache();

$matcher = new Matcher($collection);

$result = $matcher->match('get', '/user/12');

print_r($result);
