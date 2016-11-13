www.whatadesktop.com

After install:

1. Create a `log` directory at the top of the working tree
2. Allow apache to write to the `cache` directory

Submodules:

* https://github.com/zmiller91/php-common
* https://github.com/zmiller91/js-common
* https://github.com/requirejs/requirejs
* https://github.com/daneden/animate.css
* https://github.com/BlackrockDigital/startbootstrap-creative

Additions to `httpd.conf`

```
    DEFINE RootDir C:/xampp/htdocs/
    DEFINE PublicDir whatadesktop/public
    SetEnv ROOT_DIR ${RootDir}
    SetEnv PUBLIC_DIR ${PublicDir}

    DocumentRoot ${RootDir}${PublicDir}
    <Directory ${RootDir}${PublicDir}>
        RewriteEngine on

        # Don't rewrite files or directories
        RewriteCond %{REQUEST_FILENAME} -f [OR]
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^ - [L]

        # Rewrite everything else to index.html to allow html5 state links
        RewriteRule ^ index.php [L]
    </Directory>
```
