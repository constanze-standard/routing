# Constanze Standard Routing

[![GitHub license](https://img.shields.io/badge/license-Apache%202-red)](https://github.com/constanze-standard/routing/blob/master/LICENSE)
[![Coverage 100%](https://img.shields.io/azure-devops/coverage/swellaby/opensource/25.svg)](https://github.com/constanze-standard/routing)

> 路由组件将 HTTP 请求映射到一组预存变量中。主要用于为 Web 应用程序构建路由系统。

## 安装
```sh
composer require constanze-standard/routing
```

## 开始使用

routing 有两个概念：
1. `RouteCollection` 路由表数据的集合，负责收集 URL 匹配的信息。
2. `Matcher` 负责将请求数据和路由表进行匹配，并返回匹配结果。

### 路由集合 (RouteCollection)
使用 `RouteCollection` 添加一条路由数据：
```php
use ConstanzeStandard\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->add('GET', '/foo/{bar:\d+}', 'serializable data', 'unserializable data');
```
上例演示了使用 `ConstanzeStandard\Routing\RouteCollection` 的 `add` 方法添加一条路由数据。add 方法的参数：
- `$method` 可以接受的 HTTP Method, 可以用字符串或数组表示一个或多个 method.
- `$url` 请求的 URL, 可以用花括号标记一个 url 参数，并用 `:` 符号分隔定义参数的正则表达式规则。
- `$serializable` 表示可以被序列化的数据。
- `$unserializable` 表示不可序列化的参数，在我们对 `RouteCollection` 使用缓存时，将不会缓存这个参数传入的数据，而是生成一个对应 `id`, 进行映射。这解决了某些类型的数据（如 \Closure）无法序列化的问题。

### 匹配器 (Matcher)
使用 `Matcher` 匹配请求数据：
```php
use ConstanzeStandard\Routing\Matcher;
...

$matcher = new Matcher($collection);
$result = $matcher->match('GET', '/foo/12');
```
`Matcher` 接受一个 `RouteCollection` 作为初始化参数，然后可以调用 `match` 方法进行请求匹配。`match` 方法的返回值受匹配结果影响，分为三种情况：
1. 匹配成功的情况。将返回包含 4 个元素的数组：`array(Matcher::STATUS_OK, $serializable, $unserializable, $arguments)`, 第一个元素固定为 `Matcher::STATUS_OK` 的值，表示匹配成功；第二个元素为`可序列化数据`；第三个元素为`不可序列化数据`；第四个元素为 URL 参数的 mapping（如：array('id' => 10)）.
2. URL 匹配成功，但 HTTP Method 匹配失败的情况。将返回包含 3 个元素的数组：`array(Matcher::STATUS_ERROR, Matcher::ERROR_METHOD_NOT_ALLOWED, $allowedMethods)`, 第一个元素固定为 `Matcher::STATUS_ERROR` 的值，表示匹配出错；第二个元素为错误类型 `Matcher::ERROR_METHOD_NOT_ALLOWED`，表示不支持的 HTTP Method；第三个元素是一个数组，包含了这个 URL 所支持的 HTTP Method（如 array('GET', 'POST')）.
3. 匹配失败的情况。将返回包含 2 个元素的数组：`array(Matcher::STATUS_ERROR, Matcher::ERROR_NOT_FOUND)`, 第一个元素固定为 `Matcher::STATUS_ERROR` 的值，表示匹配出错；第二个元素为错误类型 `Matcher::ERROR_NOT_FOUND` 表示找不到匹配项。

### 路由缓存
当路由数据变得庞大时，一次请求将会面临频繁的读写操作，这时可以使用路由缓存来加速 `RouteCollection` 的构建。

`RouteCollection` 唯一的初始化参数 `$cacheFile` 默认值是 `null`, 表示不使用缓存，如果希望开启缓存功能，则需要通过这个参数定义缓存文件的路径：
```php
use ConstanzeStandard\Routing\RouteCollection;

$file = __DIR__ . '/cacheFile.php';
$collection = new RouteCollection($file);

$collection->add('GET', '/foo/{bar:\d+}', 'serializable data', 'unserializable data');
...

if (file_exists($file)) {
    $collection->loadCache();
} else {
    $collection->cache();
}
```
在你第一次开启缓存时，首先要确保缓存文件是不存在的。当缓存文件不存在时，`RouteCollection::cache` 会根据收集到的路由数据创建缓存文件，如果缓存文件存在，`RouteCollection` 会直接从缓存文件一次性读取数据，并忽略 `cache` 方法。

> 你的程序必须对缓存文件所在的位置有写入权限。
