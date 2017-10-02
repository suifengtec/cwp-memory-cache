# WordPress 内存缓存插件

## 简介
需启用 WP Rocket 或 WP Super Cache 之类的插件,该插件才有效,因为 WordPress 的 `transient` 默认时存储在 WordPress 数据库的,只有启用了支持静态存储的插件, WordPress 才会把一些缓存存储在内存中。

本插件支持 PHP 的 Memcached 和 PHP 的Memcache 扩展, 支持 阿里云OCS, 以不同前缀的方式 支持多个独立站点使用。

## 调试
使用 WordPress 插件 Debug Bar 和自制的脚本进行调试。

## PHP Memcache 扩展

x86_vc14_ts (3.0.9-dev)支持
```

Compiler   MSVC14 (Visual C++ 2015)
Architecture   x86
Thread Safety  enabled

```

对 PHP 7 和 PHP 7.1,请使用不同目录中的 `dll` 文件.

## Memcached
Windows 平台的 exe 文件见 `memcached` 目录。
也可以使用 Couchbase 替代 memcached。

## Couchbase

[下载Couchbase](https://www.couchbase.com/downloads)
安装,比如安装在 `W:\Program Files\Couchbase\Server`,安装之后,Couchbase 会使用默认浏览器打开 `http://localhost:8091/ui/`,让你进行必要的设置:
![]()
![]()
![]()
![]()

