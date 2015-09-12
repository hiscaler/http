# yii2-http
Http For Yii2(Base cURL)

# 安装
使用 composer，在命令行下使用以下命令：

```php
composer require "yadjet/yii2-http:dev-master" 
```

#使用
```php
$http = new Http();
$http->get('http://httpbin.org/get');
```