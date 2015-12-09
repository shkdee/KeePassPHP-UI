<?php

define('KEEPASSPHP_LOCATION', 'keepassphp/keepassphp.php');
define('KEEPASSPHP_DEBUG', false);

abstract class KPHPUI
{
	const LANG_TITLE                   = 0;
	const LANG_TAB_OPEN                = 1;
	const LANG_TAB_ADD                 = 2;
	const LANG_TAB_SEE                 = 3;
	const LANG_TAB_ABOUT               = 4;
	const LANG_OPEN_TITLE              = 5;
	const LANG_OPEN_DBID_LABEL         = 6;
	const LANG_OPEN_DBID_PLACEHOLDER   = 7;
	const LANG_OPEN_PWD_LABEL          = 8;
	const LANG_PWD_PLACEHOLDER         = 9;
	const LANG_OPEN_MORE               = 10;
	const LANG_OPEN_USE_AS_KEY         = 11;
	const LANG_OPEN_OTHER_PWD_LABEL    = 12;
	const LANG_OPEN_SEND               = 13;
	const LANG_OPEN_SEND_LOADING       = 14;
	const LANG_ADD_TITLE               = 15;
	const LANG_ADD_DBID_LABEL          = 16;
	const LANG_ADD_DBID_PLACEHOLDER    = 17;
	const LANG_ADD_FILE_LABEL          = 18;
	const LANG_ADD_PWD_LABEL           = 19;
	const LANG_ADD_MORE                = 20;
	const LANG_ADD_USE_AS_KEY          = 21;
	const LANG_ADD_OTHER_PWD_LABEL     = 22;
	const LANG_ADD_OTHER_KEYFILE_LABEL = 23;
	const LANG_ADD_SEND                = 24;
	const LANG_SEE_NO_DB_TITLE         = 25;
	const LANG_SEE_NO_DB_TEXT          = 26;
	const LANG_SEE_NO_DB_LINK          = 27;
	const LANG_ABOUT_TITLE             = 28;
	const LANG_ABOUT_TEXT              = 29;
	const LANG_MODAL_ERROR_TITLE       = 30;
	const LANG_MODAL_ERROR_TEXT        = 31;
	const LANG_MODAL_CLOSE             = 32;
	const LANG_MODAL_SUCCESS_TITLE     = 33;
	const LANG_MODAL_SUCCESS_TEXT      = 34;
	const LANG_FORM_ERROR_EMPTY        = 35;
	const LANG_FORM_ERROR_NOOTHERKEY   = 36;
	const LANG_FORM_ERROR_NOSUCHID     = 37;
	const LANG_FORM_ERROR_BADPWD       = 38;
	const LANG_FORM_ERROR_FILETOOBIG   = 39;
	const LANG_FORM_ERROR_FILEERROR    = 40;
	const LANG_FORM_ERROR_IDEXISTS     = 41;
	const LANG_SEE_PWD_DOES_NOT_EXIST  = 42;
	const LANG_SEE_ENTRY_TITLE         = 43;
	const LANG_SEE_ENTRY_URL           = 44;
	const LANG_SEE_ENTRY_USERNAME      = 45;
	const LANG_SEE_ENTRY_PASSWORD      = 46;
	const LANG_SEE_ENTRY_LOAD          = 47;


	public static $availableLangData = array();
	private static $langData = array();
	public static $lang = null;

	public static function l($idx) {
		return self::$langData[$idx];
	}

	public static function setLang($lang) {
		if(!array_key_exists($lang, self::$availableLangData))
			return false;
		self::$lang = $lang;
		self::$langData = self::$availableLangData[self::$lang];
		return true;
	}

	public static function registerLang($lang, $data) {
		if(array_key_exists($lang, self::$availableLangData))
			return false;
		self::$availableLangData[$lang] = $data;
		return true;
	}

	public static function getPreferredLanguage($http_accept_language) {
		$maxq = 0;
		$lang = null;
		preg_match_all('/([\w-]+)(?:\s*;\s*q=([\d.]+))?/', strtolower($http_accept_language), $matches, PREG_SET_ORDER);
		foreach($matches as &$match)
		{
			$q = isset($match[2]) ? floatval($match[2]) : 1.0;
			if($q <= $maxq)
				continue;
			if(array_key_exists($match[1], self::$availableLangData)) {
				$lang = $match[1];
				$maxq = $q;
				continue;
			}
			$p = strpos($match[1], "-");
			if($p !== false)
			{
				$m = substr($match[1], 0, $p);
				if(array_key_exists($m, self::$availableLangData))
				{
					$lang = $m;
					$maxq = $q;
				}
			}
		}
		return $lang;
	}

	const GET_FILE_OK = 1;
	const GET_FILE_EMPTY = 2;
	const GET_FILE_TOO_BIG = 3;
	const GET_FILE_ERROR = 4;

	public static function getFile($k, &$result)
	{
		$result = null;
		if(!isset($_FILES[$k]))
			return self::GET_FILE_EMPTY;
		$f = $_FILES[$k];
		if(empty($f['name']) || $f['error'] == UPLOAD_ERR_NO_FILE || empty($f['tmp_name']))
			return self::GET_FILE_EMPTY;
		if($f['error'] == UPLOAD_ERR_INI_SIZE || $f['error'] == UPLOAD_ERR_FORM_SIZE
				|| $f['size'] > MAX_FILE_SIZE)
			return self::GET_FILE_TOO_BIG;
		if($f['error'] != UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name']))
			return self::GET_FILE_ERROR;
		$result = $f['tmp_name'];
		return self::GET_FILE_OK;
	}

	public static function getPost($name)
	{
		return isset($_POST[$name]) ? $_POST[$name] : "";
	}

	/**
	 * Returns the string in a html-printable format : encoded
	 * in UTF8, and with some special chars rightly encoded.
	 * @param string $s
	 * @return string
	 */
	public static function makePrintable($s)
	{
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
	}
}

require_once "lang/fr.php";
KPHPUI::registerLang("fr", $lang_fr);

//require_once "lang/en.php";
//KPHPUI::registerLang("en", $lang_en);

if(!isset($_GET["l"]) || !KPHPUI::setLang($_GET["l"]))
	KPHPUI::setLang(KPHPUI::getPreferredLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]));

?>