<?php

// Location of KeePassPHP main file, relative to index.php and ajaxopen.php (!)
define('KEEPASSPHP_LOCATION', 'keepassphp/keepassphp.php');

// Debug mode for KeePassPHP
define('KEEPASSPHP_DEBUG', false);

// The maximum accepted size for uploaded files, in bytes. This is roughly 1 Mb
// and should be okay for regular password databases. You may want to change
// it if you expect specifically heavy databases.
define("MAX_FILE_SIZE", 1048576);

// We'll need this
require_once "kphpui.php";


/*************************
 * Languages declaration *
 *************************/

// register french
require_once "lang/de.php";
KPHPUI::registerLang("de", $lang_fr);

// register french
require_once "lang/fr.php";
KPHPUI::registerLang("fr", $lang_fr);

// register english
require_once "lang/en.php";
KPHPUI::registerLang("en", $lang_en);


/**********************
 * language selection *
 **********************/

// select the language, depending on the query string or HTTP header
if(!isset($_GET["l"]) || !KPHPUI::setLang($_GET["l"]))
	KPHPUI::setLang(KPHPUI::getPreferredLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]));

?>
