Briquettes(微信公众号运营工具)
=======================

![](https://img.shields.io/badge/framework-Zend2-yellowgreen)

![](https://img.shields.io/badge/version-v0.0.2-brightgreen)

![](https://img.shields.io/badge/PHP-7.2-lightgrey)

Introduction
------------
`Briquettes`：煤球，即"媒"球的谐音，旨在打造一个微信公众号运营生态。感谢 [微信官方文档](https://developers.weixin.qq.com/doc/offiaccount/Getting_Started/Overview.html ) 提供的详细接口说明**(鹅厂NB!文档写的太棒了)**。本工具为**微信公众号运营者**提供服务，包括**数据统计**，**绩效管理**，**FTP空间管理**等常用功能，目前系统是通过`Web page`进行访问，后续更新到使用`微信公众号`进行管理。

该系统还在完善ing，欢迎各位提交Issues,pr。



## Feature

* **绩效管理**：对运营者工作进行统计
* **FTP空间管理**：针对新媒体工作特性，解决跨版本合作问题。（针对校园网用户，网速较差环境极佳）ps.还在完善开发



Installation
------------

### Using Composer (must be installed)

```shell
cd my/project/dir
git clone https://github.com/Gao-pw/wxWork.git
cd wxWork
php composer.phar self-update
php composer.phar install
```



### DB config

根目录 --config -- autoload -- global.php

```
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=xmtoa;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        'user'=>'你的数据库名',
        'password'=>"你的数据库密码",
    ),
```





## Question

### Apache Setup

伪静态配置

    RewriteEngine On
    # The following rule tells Apache that if the requested filename
    # exists, simply serve it.
    RewriteCond %{REQUEST_FILENAME} -s [OR]
    RewriteCond %{REQUEST_FILENAME} -l [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^.*$ - [L]
    # The following rewrites all other queries to index.php. The 
    # condition ensures that if you are using Apache aliases to do
    # mass virtual hosting or installed the project in a subdirectory,
    # the base path will be prepended to allow proper resolution of
    # the index.php file; it will work in non-aliased environments
    # as well, providing a safe, one-size fits all solution.
    RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
    RewriteRule ^(.*) - [E=BASE:%1]
    RewriteRule ^(.*)$ %{ENV:BASE}/index.php? [L]



## LICENSE

[MIT](LICENSE) © Gao-pw



## 彩蛋

### 开发历程

* 2020.11.01 ：因学校新媒体部要统计运营者工作信息，手动收集信息重复工作太多，考虑用机器代替人工。
* 2020.11.02：依据微信开发文档，完成处理Access_token值。
* 2020.11.03 - 2020.11.18 ：忙于开题。
* 2020.11.20：和远在上海的小胖子联系说他们公司也需要一套运营工具，遂合作。
* 2020.11.21-22：完成第一版构建，并开源。
