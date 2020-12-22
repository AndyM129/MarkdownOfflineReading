# markdown_offline_reading.php

## Introduction

`markdown_offline_reading.php` 是一个Markdown 离线阅读工具，

可以单个或批量的，将Markdown文档中的网络图片下载至本地，并在原文中加载、显示，方便我们离线阅读

## Features

1. 单个Markdown文档中的网络图片下载 及离线阅读
2. 指定文件夹，将其内部所有Markdown文档离线阅读
3. 支持源文件备份，如 `file.md` -> `file.ori.md`



## Installation

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/AndyM129/MarkdownOfflineReading/master/install.sh)"
```



## Example

```
php markdown_offline_reading.php [markdown_file]
```

### Example 1：将指定Markdown文件离线

```
php markdown_offline_reading.php ./Examples/example.md
```

![](https://raw.githubusercontent.com/AndyM129/ImageHosting/master/images/20201221233847.png)

![](https://raw.githubusercontent.com/AndyM129/ImageHosting/master/images/20201221234145.png)


### Example 2：将指定目录下的Markdown文件全部离线

```
php markdown_offline_reading.php ./Examples
```

![](https://raw.githubusercontent.com/AndyM129/ImageHosting/master/images/20201221234346.png)

## History

* 1.0.0
	* 完成主体功能的开发
	* 添加 `Examples.zip` 文件，解压后，可配合示例进行功能演示

## Author

AndyMeng, andy_m129@163.com

If you have any question with using it, you can email to me. 

## Collaboration

Feel free to collaborate with ideas, issues and/or pull requests.

## License

AMKCategories is available under the MIT license. See the LICENSE file for more info.


