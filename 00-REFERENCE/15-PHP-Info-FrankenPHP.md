# PHP Info Summary – Local FrankenPHP

**PHP Version**  
8.4.18

**Build Date**  
Feb 20 2026 01:15:53

**Server API**  
PHP CLI embedded in FrankenPHP

**Loaded php.ini**  
/Users/jonathanbodnar/Documents/Repos/ark-manager/php.ini  
(Additional .ini files: none scanned)

**Thread Safety**  
Enabled (ZTS build)

**Key General Settings**

- **error_reporting**: E_ALL (30719)
- **display_errors**: STDOUT (On for CLI)
- **display_startup_errors**: On
- **log_errors**: On
- **error_log**: log/php.log
- **date.timezone**: America/New_York (Atlanta-friendly)
- **memory_limit**: 256M
- **max_execution_time**: 0 (unlimited in CLI)
- **post_max_size** / **upload_max_filesize**: 100M
- **expose_php**: Off (good for security)

**SQLite Info**

- **sqlite3** extension: Enabled
- **SQLite Library**: 3.45.2
- **pdo_sqlite**: Enabled (same library version 3.45.2)

**Other Important Extensions Status**  
Enabled (most are statically compiled in FrankenPHP):

| Extension             | Status   | Notes / Version                           |
| --------------------- | -------- | ----------------------------------------- |
| amqp                  | Enabled  | v2.2.0, librabbitmq 0.15.0                |
| apcu                  | Enabled  | v5.1.28 (but APCu debugging disabled)     |
| ast                   | Enabled  | v1.1.3                                    |
| bcmath                | Enabled  | -                                         |
| brotli                | Enabled  | v0.18.3, lib 1.2.0                        |
| bz2                   | Enabled  | lib 1.0.8                                 |
| calendar              | Enabled  | -                                         |
| ctype                 | Enabled  | -                                         |
| curl                  | Enabled  | libcurl 8.18.0, OpenSSL 3.6.1, HTTP2/3    |
| date                  | Enabled  | ICU 77.1, TZData 2025a                    |
| dba                   | Enabled  | Handlers: cdb, inifile, flatfile          |
| dom / xml / libxml    | Enabled  | libxml2 2.15.1                            |
| exif                  | Enabled  | -                                         |
| fileinfo              | Enabled  | libmagic 545                              |
| filter                | Enabled  | -                                         |
| ftp                   | Enabled  | FTPS supported                            |
| gd                    | Enabled  | Bundled 2.1.0, FreeType 2.14.1, AVIF/WebP |
| gettext               | Enabled  | -                                         |
| gmp                   | Enabled  | 6.3.0                                     |
| iconv                 | Enabled  | libiconv 1.18                             |
| igbinary              | Enabled  | 3.2.17RC1                                 |
| imagick               | Enabled  | 3.8.1, ImageMagick 7.1.2-13               |
| intl                  | Enabled  | ICU 77.1                                  |
| json                  | Enabled  | -                                         |
| ldap                  | Enabled  | OpenLDAP 2.6.12                           |
| lz4                   | Enabled  | 0.6.0, lib 1.10.0                         |
| mbstring              | Enabled  | libmbfl 1.3.2, regex oniguruma 6.9.10     |
| memcache              | Enabled  | 8.2                                       |
| memcached             | Enabled  | 3.4.0, libmemcached-awesome 1.1.4         |
| mysqli / mysqlnd      | Enabled  | mysqlnd 8.4.18                            |
| openssl               | Enabled  | OpenSSL 3.6.1                             |
| parallel              | Enabled  | 1.2.11                                    |
| pcntl                 | Enabled  | -                                         |
| pdo                   | Enabled  | Drivers: mysql, pgsql, sqlite, sqlsrv     |
| pdo_mysql             | Enabled  | -                                         |
| pdo_pgsql             | Enabled  | libpq 18.2                                |
| pdo_sqlite            | Enabled  | SQLite 3.45.2                             |
| pdo_sqlsrv            | Enabled  | 5.13.0-beta1                              |
| pgsql                 | Enabled  | libpq 18.2                                |
| phar                  | Enabled  | -                                         |
| posix                 | Enabled  | -                                         |
| protobuf              | Enabled  | 5.34.0RC2                                 |
| random                | Built-in | -                                         |
| readline              | Enabled  | EditLine wrapper                          |
| redis                 | Enabled  | 6.3.0, serializers: php/json/igbinary     |
| session               | Enabled  | Handlers: files, memcached, redis, etc.   |
| shmop                 | Enabled  | -                                         |
| simplexml             | Enabled  | -                                         |
| soap                  | Enabled  | -                                         |
| sockets               | Enabled  | -                                         |
| sodium                | Enabled  | libsodium 1.0.20                          |
| sqlsrv                | Enabled  | 5.13.0-beta1                              |
| ssh2                  | Enabled  | 1.4.1, libssh2 1.11.1                     |
| tidy                  | Enabled  | libTidy 5.8.0                             |
| tokenizer             | Enabled  | -                                         |
| xlswriter             | Enabled  | 1.5.8, libxlsxwriter 1.1.3                |
| xmlreader / xmlwriter | Enabled  | -                                         |
| xsl                   | Enabled  | libxslt 1.1.45                            |
| xz                    | Enabled  | 1.2.0, liblzma 5.8.2                      |
| yaml                  | Enabled  | 2.3.0, LibYAML 0.2.5                      |
| zip                   | Enabled  | 1.22.7, libzip 1.11.4                     |
| zlib                  | Enabled  | 1.3.2                                     |
| zstd                  | Enabled  | 0.15.2                                    |

**OPcache**

- Enabled in php.ini but **disabled for CLI** (common in FrankenPHP embeds).
- Startup failed message: "Opcode Caching is disabled for CLI"

**Environment & Server Variables** (top ones)

- **HOME**: /Users/jonathanbodnar
- **PATH**: (includes Homebrew, nvm node 23.6.1, etc.)
- **TERM**: xterm-256color
- **SHELL**: /bin/zsh
- **PWD**: /Users/jonathanbodnar/Documents/Repos/ark-manager
- Running from Zed editor (TERM_PROGRAM=zed)
