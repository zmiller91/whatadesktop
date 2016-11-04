Submodules:

    * https://github.com/zmiller91/php-common
    * https://github.com/zmiller91/js-common
    * https://github.com/requirejs/requirejs


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
