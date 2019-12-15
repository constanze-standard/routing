<?php

/**
 * Copyright 2019 Constanze Standard <omytty.alex@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ConstanzeStandard\Routing;

use ConstanzeStandard\Routing\Interfaces\RouteCollectionInterface;
use RuntimeException;

/**
 * The collection of route parameters.
 */
class RouteCollection implements RouteCollectionInterface
{
    /**
     * Static map
     *
     * @var array
     */
    protected $statics = [];

    /**
     * Variable map
     *
     * @var array
     */
    public $variables = [];

    /**
     * Route unserializable data.
     * 
     * @var array
     */
    private $unserializables = [];

    /**
     * Cache file name.
     * 
     * @var string|null
     */
    private $cacheFile = null;

    /**
     * The unserializables index counter.
     * 
     * @var int
     */
    private $countId = 0;

    /**
     * Get contents from cache file.
     * 
     * @param string $source
     * 
     * @return array
     * @throws \RuntimeException
     */
    public static function getContentsFromCache(string $source)
    {
        if (! (is_file($source) && is_readable($source))) {
            throw new \RuntimeException('Cache file does not exist or not readable.');
        }

        return unserialize(require($source));
    }

    /**
     * Put contents to cache file.
     * 
     * @param string $target Cache file name.
     * @param array $contents Collection contents.
     * 
     * @return bool
     * @throws \RuntimeException
     */
    public static function cacheContents(string $target, array $contents)
    {
        if (! is_writable(dirname($target))) {
            throw new \RuntimeException('Cache directory does not writable.');
        }

        $contents = serialize($contents);
        $bytes = file_put_contents($target, "<?php return '$contents';");
        return (bool)$bytes;
    }

    /**
     * Parse the url and get arguments.
     * 
     * @param string $url
     * 
     * @return array
     */
    private static function parseUrlArguments(string $url)
    {
        preg_match_all('/{([^\/^:]+)(:.*)?}/', $url, $matched);
        return $matched[1];
    }

    /**
     * @param string|null $cacheFile
     */
    public function __construct(string $cacheFile = null)
    {
        $this->cacheFile = $cacheFile;
    }

    /**
     * Add a url pattern to collection.
     * 
     * @param array|string $method
     * @param string $url
     * @param mixed $serializable The serializable data.
     * @param array $unserializable  The unserializable data won't be cached.
     */
    public function add($method, string $url, $serializable, $unserializable)
    {
        $unserializableId = $this->registerUnserializableData($unserializable);

        if (! $this->cacheFile || ! file_exists($this->cacheFile)) {
            $arguments = static::parseUrlArguments($url);
            $methods = array_map('strtoupper', (array) $method);
            $parameter = [$url, $unserializableId, $serializable, $arguments];
            foreach ($methods as $method) {
                $this->{$arguments ? 'variables' : 'statics'}[$method][] = $parameter;
            }
        }
    }

    /**
     * Get unserializable data by id.
     * 
     * @param int $id
     * 
     * @throws RuntimeException
     * 
     * @return \Closure|array|string
     */
    public function getUnserializableById(int $id)
    {
        if (array_key_exists($id, $this->unserializables)) {
            return $this->unserializables[$id];
        }
        throw new RuntimeException('The handler id '. $id .' mismatch.');
    }

    /**
     * Get the collection contents.
     * 
     * @return array
     */
    public function getContents(): array
    {
        return [$this->statics, $this->variables];
    }

    /**
     * Cache collection contents to target if file exists.
     * 
     * @return bool
     */
    public function cache(): bool
    {
        if ($this->cacheFile) {
            return static::cacheContents($this->cacheFile, $this->getContents());;
        }
        return false;
    }

    /**
     * Load cache from cache file if seted.
     * 
     * @return bool
     */
    public function loadCache()
    {
        if ($this->cacheFile && file_exists($this->cacheFile)) {
            [$this->statics, $this->variables] = static::getContentsFromCache($this->cacheFile);
            return true;
        }
        return false;
    }

    /**
     * Push a handler to array.
     * 
     * @param \Closure|array|string $handler
     * 
     * @return int the current id
     */
    private function registerUnserializableData($unserializable): int
    {
        $this->unserializables[$this->countId] = $unserializable;
        return $this->countId++;
    }
}
