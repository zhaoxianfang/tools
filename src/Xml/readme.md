# xml工具

> 来源：https://github.com/digitickets/lalit
> 时间：2024-05-30

## ArrayToXml

```
$xml = Array2XML::createXML($array,?'root_node_name', ?$attr, ?$docType);
```

```
1、普通数组转XML => createXML 方法

$array = [
    'Good_guy' => [
        'name' => 'Luke Skywalker',
        'weapon' => 'Lightsaber'
    ],
    'Bad_guy' => [
        'name' => 'Sauron',
        'weapon' => 'Evil Eye'
    ]
];

$xml = Array2XML::createXML($array,'root',[
    'version' => '1.0',
    'encoding' => 'UTF-8',
    'standalone' => true,
    'formatOutput' => true
]);
echo $xml->saveXML();


2、转微信文档中示例的 XML 数据 => createWechatXML 方法
// 输入数组数据
$data = [
    'ToUserName' => 'toUser',
    'FromUserName' => 'fromUser',
    'CreateTime' => 12345678, // int 类型的数据 转换后不会携带CDATA
    'MsgType' => 'text',
    'Content' => 'Hello World'
];
$xml = Array2XML::createWechatXML($data);

echo $xml; // <?xml version="1.0" encoding="UTF-8"?><xml><ToUserName><![CDATA[toUser]]></ToUserName><FromUserName><![CDATA[fromUser]]></FromUserName><CreateTime>12345678</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[Hello World]]></Content></xml>

```

## XmlToArray

```
/**
 * @param string $xml  XML字符串 或 DOMDocument对象 或 XML文件路径
 * @return array
 */
$array = XML2Array::parse($xml);
```
