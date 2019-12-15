<?php

use ConstanzeStandard\Routing\RouteCollection;

require_once __DIR__ . '/AbstractTest.php';

class RouteCollectionTest extends AbstractTest
{
    public function testAddWithoutCache()
    {
        $collection = new RouteCollection();
        $collection->add('get', '/foo', 'serializable', 'unserializable');
        $collection->add('get', '/foo/{id:\d+}', 'serializable', 'unserializable');
        $contents = $collection->getContents();

        $this->assertEquals($contents, [
            [
                'GET' => [
                    ['/foo', 0, 'serializable', []]
                ]
            ],
            [
                'GET' => [
                    ['/foo/{id:\d+}', 1, 'serializable', ['id']]
                ]
            ]
        ]);
    }

    public function testGetUnserializableById()
    {
        $collection = new RouteCollection();
        $this->callMethod($collection, 'registerUnserializableData', ['unserializable0']);
        $this->callMethod($collection, 'registerUnserializableData', ['unserializable1']);
        $result = $collection->getUnserializableById(0);
        $this->assertEquals($result, 'unserializable0');
        $result = $collection->getUnserializableById(1);
        $this->assertEquals($result, 'unserializable1');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetUnserializableByIdWithError()
    {
        $collection = new RouteCollection();
        $result = $collection->getUnserializableById(2);
    }

    public function testCacheWithFalse()
    {
        $collection = new RouteCollection();
        $result = $collection->cache();
        $this->assertFalse($result);
    }

    public function testCacheWithTrue()
    {
        $file = __DIR__ . '/cache_file';
        $collection = new RouteCollection();
        $collection->loadCache($file);
        $result = $collection->cache();
        $this->assertTrue($result);
        $this->assertFileExists($file);
        unlink($file);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCacheWithNotWriteable()
    {
        mkdir(__DIR__ . '/tmp', '0000');
        $file = __DIR__ . '/tmp/cache_file';
        $collection = new RouteCollection();
        try {
            $collection->loadCache($file);
            $collection->cache();
        } catch (\Throwable $e) {
            chmod(__DIR__ . '/tmp', '0755');
            rmdir(__DIR__ . '/tmp');
            throw $e;
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetContentsFromCacheWithException()
    {
        RouteCollection::getContentsFromCache(__DIR__ . '/nothing');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetContentsFromCacheWithNotReadable()
    {
        touch(__DIR__ . '/nothing');
        chmod(__DIR__ . '/nothing', '0000');
        try {
            RouteCollection::getContentsFromCache(__DIR__ . '/nothing');
        } catch (\Throwable $e) {
            chmod(__DIR__ . '/nothing', '0755');
            unlink(__DIR__ . '/nothing');
            throw $e;
        }
    }
}
