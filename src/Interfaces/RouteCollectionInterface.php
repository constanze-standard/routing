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

namespace ConstanzeStandard\Routing\Interfaces;

/**
 * The collection of route parameters.
 */
interface RouteCollectionInterface
{
    /**
     * Add a url pattern to collection.
     * 
     * @param array|string $method
     * @param string $url
     * @param mixed $handler The `handler` won't be cached.
     * @param array $options Other custom route options.
     */
    public function add($method, string $url, $serializable, $unserializable);

    /**
     * Get the collection contents.
     * 
     * @return array
     */
    public function getContents(): array;

    /**
     * Get unserializable data by id.
     * 
     * @param int $id
     * 
     * @throws RuntimeException
     * 
     * @return \Closure|array|string
     */
    public function getUnserializableById(int $id);

    /**
     * Cache collection contents to target if file exists.
     * 
     * @return bool
     */
    public function cache(): bool;
}
