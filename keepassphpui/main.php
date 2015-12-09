<?php

// Location of KeePassPHP main file, relative to index.php and ajaxopen.php (!)
define('KEEPASSPHP_LOCATION', 'keepassphp/keepassphp.php');

// Debug mode for KeePassPHP
define('KEEPASSPHP_DEBUG', false);

// We'll need this
require_once "kphpui.php";


/*************************
 * Languages declaration *
 *************************/

// register french
require_once "lang/fr.php";
KPHPUI::registerLang("fr", $lang_fr);

// register english
require_once "lang/en.php";
KPHPUI::registerLang("en", $lang_en);


/**********************
 * language selection *
 **********************/

// selects the language, depending on the query string or HTTP header
if(!isset($_GET["l"]) || !KPHPUI::setLang($_GET["l"]))
	KPHPUI::setLang(KPHPUI::getPreferredLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]));

?>