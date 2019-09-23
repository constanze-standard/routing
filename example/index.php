<?php

use ConstanzeStandard\Routing\Cache;
use ConstanzeStandard\Routing\Matcher;
use ConstanzeStandard\Routing\RouteCollection;

require __DIR__ . '/../vendor/autoload.php';

$collection = new RouteCollection();

$collection->add('GET', '/user/{name}', 'handler', ['name' => 'user.id']);
$collection->add('GET', '/users/{name}', 'handler', ['name' => 'user.id']);
$collection->add('GET', '/userss/{name}', 'handler', ['name' => 'user.id']);

// $cache = new Cache($collection, __DIR__ . '/cacheFile.php');
// $collection->cache();

$matcher = new Matcher($collection);

$result = $matcher->match('GET', '/userss/12');

print_r($result);
