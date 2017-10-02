# WordPress 内存缓存插件

需启用 WP Rocket 或 WP Super Cache 之类的插件,该插件才有效,因为 WordPress 的 `transient` 默认时存储在 WordPress 数据库的,只有启用了支持静态存储的插件, WordPress 才会把一些缓存存储在内存中。

本插件支持 PHP 的 Memcached 和 PHP 的Memcache 扩展。

