# yii2-http
Simple Http(Base cURL)

# 安装
使用 composer，在命令行下使用以下命令：

```php
composer require "yadjet/http:dev-master" 
```

#使用
```php
$http = new Http();
$http->get('http://httpbin.org/get');
```