<?php

// Location of KeePassPHP main file, relative to index.php and ajaxopen.php (!)
define('KEEPASSPHP_LOCATION', 'keepassphp/keepassphp.php');

// Debug mode for KeePassPHP
define('KEEPASSPHP_DEBUG', false);

// The maximum accepted size for uploaded files, in bytes.
// Try to guess it from the configuration directives upload_max_filesize and
// post_max_size, but make it at least >= 1M in case our method fails.
// That's already a quite big size for a password database.
$post_max_size = parse_ini_size(ini_get('post_max_size'), 8388608);
$upload_max_filesize = parse_ini_size(ini_get('upload_max_filesize'), 2097152);
// If $post_max_size is 0, it should actually be ignored.
define("MAX_FILE_SIZE", max(1048576, $post_max_size === 0
	? $upload_max_filesize : min($post_max_size, $upload_max_filesize)));

/**
 * Parses the value of a configuration directive containing a size.
 * Returns the result as an integer, defaulting to $default if the
 * argument $ini_size is empty.
 */
function parse_ini_size($ini_size, $default)
{
	if(empty($ini_size) || $ini_size === false)
		return $default;
	switch(substr($ini_size, -1))
	{
		case 'M': case 'm': return (int)$ini_size * 1048576;
		case 'K': case 'k': return (int)$ini_size * 1024;
		case 'G': case 'g': return (int)$ini_size * 1073741824;
		case 'B': case 'b': return parse_ini_size(substr($ini_size, 0, -1),
			$default);
		default: return intval($ini_size);
	}
}


// We'll need this
require_once "kphpui.php";


/*************************
 * Languages declaration *
 *************************/

// register german
require_once "lang/de.php";
KPHPUI::registerLang("de", $lang_de);

// register english
require_once "lang/en.php";
KPHPUI::registerLang("en", $lang_en);

// register french
require_once "lang/fr.php";
KPHPUI::registerLang("fr", $lang_fr);


/**********************
 * language selection *
 **********************/

// select the language, depending on the query string or HTTP header
if(!isset($_GET["l"]) || !KPHPUI::setLang($_GET["l"]))
	KPHPUI::setLang(KPHPUI::getPreferredLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]));

?>
