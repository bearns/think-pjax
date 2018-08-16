# ThinkPHP 5.0  Pjax 行为扩展

A pjax behavior for ThinkPHP5

## 需求

此扩展依赖 ThinkPHP 5

## 安装

使用 Composer 安装:

``` bash
$ composer require bearns/think-pjax -vvv
```

## 使用

将 `\Bearns\Pjax\FilterIfPjax` 添加到行为标签 `app_end` 中去。

```php
// application/tags.php

...
    'app_end' => [
        '\\Bearns\\Pjax\\FilterIfPjax',
    ],
```
