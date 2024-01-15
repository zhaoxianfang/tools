# xml工具

> 来源：https://github.com/digitickets/lalit
> 时间：2024-01-15

## ArrayToXml

```
$xml = Array2XML::createXML($array,?'root_node_name', ?$attr, ?$docType);
```

```
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
echo $xml;
```

## XmlToArray

```
/**
 * @param string $xml  XML字符串 或 DOMDocument对象 或 XML文件路径
 * @return array
 */
$array = XML2Array::createArray($xml);
```
