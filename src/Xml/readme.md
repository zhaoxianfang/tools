# XML 解析和转换

## ArrayToXml (数组转XML)

### 1、基础用法

```php
// 简单数组转换
$data = [
    'user' => [
        'id' => 1001,
        'name' => '张三',
        'email' => 'zhangsan@example.com',
        'is_active' => true,
        'roles' => ['admin', 'editor']
    ]
];

$xml = Array2XML::toXML($data);
echo $xml;
```

输出结果：

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <user>
        <id>1001</id>
        <name>张三</name>
        <email>zhangsan@example.com</email>
        <is_active>true</is_active>
        <roles>admin</roles>
        <roles>editor</roles>
    </user>
</root>
```

### 2、启用autoCData功能/微信文档中示例的 XML 数据

```php
$config = [
    'autoCData' => true, // 启用CDATA功能
    'rootName' => 'xml' // 根节点名称
];
$data = [
   'ToUserName' => 'toUser',
   'FromUserName' => 'fromUser',
   'CreateTime' => 12345678, // int 类型的数据 转换后不会携带CDATA
   'MsgType' => 'text',
   'Content' => 'Hello World',
   'description' => '包含<特殊>字符的内容'
];
```

输出结果：

```xml
<?xml version="1.0" encoding="UTF-8"?>
<xml>
    <ToUserName><![CDATA[toUser]]></ToUserName>
    <FromUserName><![CDATA[fromUser]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[Hello World]]></Content>
    <description><![CDATA[包含<特殊>字符的内容]]></description>
</xml>
```

### 3、高级功能演示

```php
// 高级配置示例
$config = [
    'version' => '1.1', // 版本号
    'encoding' => 'UTF-8', // 文档编码
    'rootName' => 'WeChat:Message', // 根节点名称
    'namespaces' => [ // 命名空间
        'WeChat' => 'http://wechat.example.com/ns',
        'xs' => 'http://www.w3.org/2001/XMLSchema'
    ],
    'defaultNamespace' => 'http://wechat.example.com/dfns', // 默认命名空间
    'rootAttributes' => [ // 根元素属性
        'created' => date('Y-m-d H:i:s')
    ],
    'autoCData' => true // 自动添加CDATA
];

$data = [
    '@from' => 'user123', // 根元素属性
    '@to' => 'server001',
    'MsgType' => 'event',
    'Event' => 'subscribe',
    'CreateTime' => time(),
    'Content' => '欢迎订阅!',
    'Extra' => [
        '#comment' => '附加信息',
        'Location' => [
            'Latitude' => 39.9042,
            'Longitude' => 116.4074
        ],
        '#pi' => [
            'target' => 'php',
            'data' => 'echo "Hello World";'
        ]
    ]
];

$xml = Array2XML::toXML($data, $config);
echo $xml;
```

输出结果：

```xml
<?xml version="1.1" encoding="UTF-8"?>
<WeChat:Message created="2025-05-08 01:52:21" xmlns:WeChat="http://wechat.example.com/ns"
                xmlns:xs="http://www.w3.org/2001/XMLSchema" from="user123" to="server001">
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[subscribe]]></Event>
    <CreateTime>1746669141</CreateTime>
    <Content><![CDATA[欢迎订阅!]]></Content>
    <Extra>
        <!--附加信息-->
        <Location>
            <Latitude>39.9042</Latitude>
            <Longitude>116.4074</Longitude>
        </Location>
        <?php echo "Hello World";?>
    </Extra>
</WeChat:Message>
```

### 4、动态操作XML文档

```php
// 初始化转换器
$converter = new Array2XML([
    'rootName' => 'Products',
    'autoCData' => true
]);

// 添加基础信息
$converter->convert([
    'info' => [
        'title' => '产品目录',
        'version' => '1.0'
    ]
]);

// 获取根元素
$root = $converter->getDocument()->documentElement;

// 动态添加产品 - 修正后的代码
$products = [
    [
        'name' => '智能手机',
        'price' => 2999,
        'description' => '高端旗舰<手机>'
    ],
    [
        'name' => '笔记本电脑',
        'price' => 5999,
        'description' => '高性能<笔记本>'
    ]
];

foreach ($products as $product) {
    // 创建产品元素（带命名空间示例）
    $productElement = $converter->createElement(
        'Product', 
        null, 
        ['id' => 'p' . uniqid()],  // 属性
        'https://example.com/ns'    // 命名空间
    );
    
    // 添加产品详情
    foreach ($product as $key => $value) {
        $productElement->appendChild(
            $converter->createElement($key, $value)
        );
    }
    
    $root->appendChild($productElement);
}

// 添加注释
$root->appendChild(
    $converter->getDocument()->createComment('生成于 ' . date('Y-m-d'))
);

// 输出最终XML
echo $converter->getDocument()->saveXML();
```

输出结果：

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Products>
    <info>
        <title><![CDATA[产品目录]]></title>
        <version>1.0</version>
    </info>
    <Product xmlns="https://example.com/ns" id="p681c0e55c1ae6">
        <name><![CDATA[智能手机]]></name>
        <price>2999</price>
        <description><![CDATA[高端旗舰<手机>]]></description>
    </Product>
    <Product xmlns="https://example.com/ns" id="p681c0e55c1aec">
        <name><![CDATA[笔记本电脑]]></name>
        <price>5999</price>
        <description><![CDATA[高性能<笔记本>]]></description>
    </Product>
    <!--生成于 2025-05-08-->
</Products>
```

### 配置参数说明:

| 参数                 | 	类型    | 默认值     | 说明          |
|--------------------|--------|---------|-------------|
| version            | string | '1.0'   | XML版本号      |
| encoding           | string | 'UTF-8' | 文档编码        |
| standalone         | bool   | false   | 是否独立文档      |
| formatOutput       | bool   | true    | 是否格式化输出     |
| preserveWhiteSpace | bool   | false   | 是否保留空白      |
| rootName           | string | 'root'  | 根元素名称       |
| rootAttributes     | array  | []      | 根元素属性       |
| defaultNamespace   | string | null    | 默认命名空间URI   |
| autoCData          | bool   | false   | 是否自动处理CDATA |
| booleanToString    | bool   | true    | 布尔值转字符串     |
| namespaces         | array  | []      | 命名空间配置      |

### 特殊键说明:

| 参数(键名)   | 	用途       | 示例                                                          | 生成内容                                  |
|----------|-----------|-------------------------------------------------------------|---------------------------------------|
| @key     | 属性        | ['@attribute' => 'value']                                   | `<element attribute="value">`         |
| #comment | 注释        | ['#comment' => '注释内容']                                      | `<!-- 注释内容 -->`                       |
| #pi      | 处理指令/PI指令 | ['#pi' => ['target'=>'php', 'data'=>'echo "Hello World";']] | `<?php echo "Hello World";?>`         |
| #cdata   | CDATA     | ['MsgType'=>[ '#cdata' => 'text' ]]                         | `<MsgType><![CDATA[text]]></MsgType>` |

## XmlToArray (XML 转数组)

### 1、解析微信官方文档中示例的 XML 数据

```xml

<xml>
    <ToUserName><![CDATA[toUser]]></ToUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[Hello World]]></Content>
</xml>
```

解析：

```php
// 静态调用
XML2Array::toArray($xml);
```

输出结果：

```php
[
    'ToUserName' => 'toUser',
    'CreateTime' => 12345678,
    'MsgType' => 'text',
    'Content' => 'Hello World'
]
```

### 2、示例化后调用(默认不保留根节点)

```php
$parser = new XML2Array();
// 或者定义配置
// $parser = new XML2Array(['preserveRootNode' => false]);

// 示例：内存受限环境
// $parser = new EnterpriseXMLParser(['memoryLimit' => 1024 * 1024 * 100]); // 100MB限制
$result = $parser->parse('<root><item>value</item></root>'); // 解析XML字符串为数组
// 获取统计信息
$stats = $parser->getStats();
```

输出结果：
```php
// 去除根节点得到的数组
[
    'item' => 'value'
]
// 统计信息
[
    'nodes' => 2
    'attributes' => 0
    'depth' => 1
    'memory' => 84432
    'parseTime' => 1.9073486328125E-5
]
```

### 3、流式处理并忽略根节点
```php
foreach ($parser->parseStream('/your/path/data.xml') as $key => $value) {
    // 直接获取子节点，不包含根节点
}
```

### 4、包含注释解析
```php
$parser = new XML2Array(['parseComments' => true]);
$result = $parser->parse('<!-- comment --><root/>');
```

### 5、内存受限环境
```php
$parser = new XML2Array(['memoryLimit' => 1024 * 1024 * 100]); // 100MB限制
try {
    $result = $parser->parse($largeXml);
} catch (\RuntimeException $e) {
    // 处理内存不足情况
}
```


### 配置参数说明：

| 参数                   | 	类型    | 默认值                                                                                   | 说明                                            |
|----------------------|--------|---------------------------------------------------------------------------------------|-----------------------------------------------|
| attributePrefix      | string | '@'                                                                                   | 属性前缀                                          |
| textNodeName         | string | '#text'                                                                               | 文本节点名称                                        |
| commentNodeName      | string | '#comment'                                                                            | 注释节点名称                                        |
| autoArray            | bool   | false                                                                                 | 是否自动创建数组                                      |
| namespaceHandling    | string | 'remove'                                                                              | 三种命名空间处理模式：none（忽略）、remove（移除前缀）、preserve（保留） |
| parseAttributes      | bool   | true                                                                                  | 是否解析属性                                        |
| parseComments        | bool   | false                                                                                 | 是否解析注释                                        |
| trimValues           | bool   | true                                                                                  | 是否修剪值                                         |
| typeDetection        | bool   | true                                                                                  | 是否启用类型检测                                      |
| encoding             | string | 'UTF-8'                                                                               | 默认编码                                          |
| preserveRootNode     | bool   | false                                                                                 | 是否保留根节点                                       |
| removeXmlDeclaration | bool   | true                                                                                  | 是否移除XML声明                                     |
| libxmlOptions        | int    | `LIBXML_NOCDATA & LIBXML_NOBLANKS & LIBXML_NONET & LIBXML_COMPACT & LIBXML_PARSEHUGE` | libxml解析选项                                    |
| maxDepth             | int    | 50                                                                                    | 最大递归深度                                        |
| validateOnParse      | bool   | false                                                                                 | 是否启用XML验证                                     |
| schemaValidation     | bool   | false                                                                                 | 是否使用XSD验证                                     |
| schemaPath           | string | null                                                                                  | XSD文件路径                                       |
| memoryLimit          | int    | null                                                                                  | 内存限制(字节)                                      |
