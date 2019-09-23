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

use ConstanzeStandard\Routing\Interfaces\MatcherInterface;
use ConstanzeStandard\Routing\Interfaces\RouteCollectionInterface;

class Matcher implements MatcherInterface
{
    const CHUNK_SIZE = 10;

    const STATUS_OK = 'status_ok';
    const STATUS_ERROR = 'status_error';

    const ERROR_METHOD_NOT_ALLOWED = 'error_method_not_allowed';
    const ERROR_NOT_FOUND = 'error_not_found';

    /**
     * The route collection.
     * 
     * @var RouteCollectionInterface
     */
    private $routeCollection;

    /**
     * @param RouteCollectionInterface $routeCollection
     */
    public function __construct(RouteCollectionInterface $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    /**
     * Match the HTTP method and url pathinfo.
     * 
     * @param string $method
     * @param string $pathinfo
     * 
     * @return array
     */
    public function match(string $method, string $pathinfo): array
    {
        $pathinfo = \urldecode($pathinfo);
        $allowedMethods = [];
        [$statics, $variables] = $this->routeCollection->getContents();

        foreach ($statics as $allowedMethod => $_statics) {
            foreach ($_statics as [$url, $unserializableId, $serializable, $arguments]) {
                if ($pathinfo === $url) {
                    if (strcasecmp($method, $allowedMethod) === 0) {
                        return [
                            static::STATUS_OK,
                            $serializable,
                            $this->routeCollection->getUnserializableById($unserializableId),
                            $arguments
                        ];
                    }
                    $allowedMethods[] = $allowedMethod;
                }
            }
        }

        foreach ($variables as $allowedMethod => $_variables) {
            $chunkedVariables = \array_chunk($_variables, self::CHUNK_SIZE);
            foreach ($chunkedVariables as $_chunkedVariables) {
                [$regex, $map] = $this->getRegexAndMap($_chunkedVariables);
                if (preg_match($regex, $pathinfo, $matches)) {
                    if (strcasecmp($method, $allowedMethod) === 0) {
                        [, $unserializableId, $serializable, $arguments] = $map[count($matches)];
                        return [
                            static::STATUS_OK,
                            $serializable,
                            $this->routeCollection->getUnserializableById($unserializableId),
                            array_combine($arguments, array_slice($matches, 1, count($arguments)))
                        ];
                    }
                    $allowedMethods[] = $allowedMethod;
                }
            }
        }

        if (!empty($allowedMethods)) {
            return [static::STATUS_ERROR, static::ERROR_METHOD_NOT_ALLOWED, $allowedMethods];
        }

        return [static::STATUS_ERROR, static::ERROR_NOT_FOUND];
    }

    /**
     * Merge all variable data patterns in one pattern.
     * This part has been referred to nikic/fast-route
     *
     * @return string
     */
    private function getRegexAndMap($variables)
    {
        $patterns = [];
        $map = [];
        $numGroups = 0;

        foreach ($variables as $variable) {
            [$url, , , $arguments] = $variable;
            $numArguments = count($arguments);
            $numGroups = max($numGroups, $numArguments);
            $patterns[] = $url . str_repeat('()', $numGroups - $numArguments);
            $map[$numGroups + 1] = $variable;
            ++$numGroups;
        }

        $pattern = implode('|', $patterns);
        $regex = preg_replace('/{[^\/^:]+:(?=)([^\/]+)}/', '(${1})', $pattern);
        $regex = preg_replace('/{[^\/]+}/', '([^/]+)', '~^(?|'.$regex.')$~');
        return [$regex, $map];
    }
}
