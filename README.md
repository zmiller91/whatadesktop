Two Submodules:
    - https://github.com/zmiller91/js-common
    - https://github.com/requirejs/requirejs

Additions to `httpd.conf`

```
    DocumentRoot "/whatadesktop/public"
    <Directory "/whatadesktop/public">
        RewriteEngine on

        # Don't rewrite files or directories
        RewriteCond %{REQUEST_FILENAME} -f [OR]
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^ - [L]

        # Rewrite everything else to index.html to allow html5 state links
        RewriteRule ^ index.php [L]
    </Directory>
```