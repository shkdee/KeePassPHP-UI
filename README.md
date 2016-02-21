KeePassPHP-UI
=============

A nice user interface for [KeePassPHP](//github.com/shkdee/KeePassPHP), built with jQuery (2.x) and Bootstrap (3.3.6), available in several languages (French and English so far). KeePassPHP can store and read KeePass password databases, so that you can access your passwords from any device, simply through a web browser. It never stores your text password - in agreement with the KeePass philosophy - so your password database is always kept encrypted, and only you can access it.

See [KeePassPHP](//github.com/shkdee/KeePassPHP) project for more information.


How to use it?
-------------------

The `keepassphp` directory from the [KeePassPHP](//github.com/shkdee/KeePassPHP) project must be added next to the others. If you want to put it somewhere else, just change the value of `KEEPASSPHP_LOCATION` in `keepassphpui/main.php`. And voilà!

You will also find some more configuration options in `keepassphpui/main.php` that you can change if you have specific needs:
* `KEEPASSPHP_DEBUG`: whether you want to activate KeePassPHP debug mode. It just temporarily logs execution data, useful when developing.
* `MAX_FILE_SIZE`: the maximum size for kdbx files uploaded to KeePassPHP. The default value is roughly 1 Mb; you may want to change it if you expect heavy databases.


Web server configuration
-------------------

The directories `icons`, `css` and `js` contain only web resources, so you may want to configure your web server to cache those files and serve them statically. Beside these directories, the web server should only be able to serve `index.php` and `ajaxopen.php`; you can deny the access to other files through the web server.


Translation
-------------------

KeePassPHP-UI can be easily translated: create a new file in `keepassphpui/lang/` containing an array of translated strings (see `keepassphpui/lang/fr.php` and `keepassphpui/lang/en.php` for, respectively, French and English versions). Then, include this file from `keepassphpui/main.php` and add a line to have the UI register the language.
