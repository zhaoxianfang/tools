# 迁移笔记

1、替换命名空间

```
把
Nwidart\Modules
全部替换为
zxf\Laravel\Modules
```

2、在 `ConsoleServiceProvider` 里面添加 `customAddCommands`方法

3、FileRepository.php

```
设置 getScanPaths() 方法直接 return [];
改造 scan() 方法
```

4、 使用改造的 `LaravelModulesServiceProvider`文件代替以下文件

```
LaravelModulesServiceProvider
ModulesServiceProvider
```

5、删除`Modules`下的几个文件

```
LumenModulesServiceProvider.php
ModulesServiceProvider.php
LaravelModulesServiceProvider.php
```

6、改造`helpers.php`里面的几个方法
```
module_path()
```

