<?php

use ConstanzeStandard\Routing\Matcher;
use ConstanzeStandard\Routing\RouteCollection;

require_once __DIR__ . '/AbstractTest.php';

class MatcherTest extends AbstractTest
{
    public function testMatchWithStatics()
    {
        $collection = new RouteCollection();
        $collection->add('get', '/foo', 'serializable', 'unserializable');

        $matcher = new Matcher($collection);
        $result = $matcher->match('GET', '/foo');
        $this->assertEquals($result, [Matcher::STATUS_OK, 'serializable', 'unserializable', []]);
    }

    public function testMatchWithVariables()
    {
        $collection = new RouteCollection();
        $collection->add('get', '/foo/{id:\d+}', 'serializable', 'unserializable');

        $matcher = new Matcher($collection);
        $result = $matcher->match('GET', '/foo/10');
        $this->assertEquals($result, [Matcher::STATUS_OK, 'serializable', 'unserializable', ['id' => 10]]);
    }

    public function testMatchWithStaticsErrorMethodNotAllowed()
    {
        $collection = new RouteCollection();
        $collection->add(['put', 'DELETE'], '/foo', 'serializable', 'unserializable');

        $matcher = new Matcher($collection);
        $result = $matcher->match('GET', '/foo');
        $this->assertEquals($result, [Matcher::STATUS_ERROR, Matcher::ERROR_METHOD_NOT_ALLOWED, ['PUT', 'DELETE']]);
    }

    public function testMatchWithVariablesErrorMethodNotAllowed()
    {
        $collection = new RouteCollection();
        $collection->add(['put', 'DELETE'], '/foo/{id:\d+}', 'serializable', 'unserializable');

        $matcher = new Matcher($collection);
        $result = $matcher->match('GET', '/foo/10');
        $this->assertEquals($result, [Matcher::STATUS_ERROR, Matcher::ERROR_METHOD_NOT_ALLOWED, ['PUT', 'DELETE']]);
    }

    public function testMatchWithNotFound()
    {
        $collection = new RouteCollection();

        $matcher = new Matcher($collection);
        $result = $matcher->match('GET', '/');
        $this->assertEquals($result, [Matcher::STATUS_ERROR, Matcher::ERROR_NOT_FOUND]);
    }
}
