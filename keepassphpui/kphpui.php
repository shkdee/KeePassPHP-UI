<?php

/**
 * Static class containing language managing methods and some helping
 * methods for the UI.
 */
abstract class KPHPUI
{
	const LANG_PAGE_TITLE              = 0;
	const LANG_TAB_OPEN                = 1;
	const LANG_TAB_ADD                 = 2;
	const LANG_TAB_SEE                 = 3;
	const LANG_TAB_ABOUT               = 4;
	const LANG_OPEN_TITLE              = 5;
	const LANG_OPEN_DBID_LABEL         = 6;
	const LANG_OPEN_DBID_PLACEHOLDER   = 7;
	const LANG_OPEN_PWD_LABEL          = 8;
	const LANG_PWD_PLACEHOLDER         = 9;
	const LANG_OPEN_USE_AS_KEY         = 10;
	const LANG_OPEN_OTHER_PWD_LABEL    = 11;
	const LANG_OPEN_SEND               = 12;
	const LANG_OPEN_SEND_LOADING       = 13;
	const LANG_ADD_TITLE               = 14;
	const LANG_ADD_DBID_LABEL          = 15;
	const LANG_ADD_DBID_PLACEHOLDER    = 16;
	const LANG_ADD_FILE_LABEL          = 17;
	const LANG_ADD_PWD_LABEL           = 18;
	const LANG_ADD_USE_AS_KEY          = 19;
	const LANG_ADD_OTHER_PWD_LABEL     = 20;
	const LANG_ADD_OTHER_KEYFILE_LABEL = 21;
	const LANG_ADD_SEND                = 22;
	const LANG_SEE_NO_DB_TITLE         = 23;
	const LANG_SEE_NO_DB_TEXT          = 24;
	const LANG_SEE_NO_DB_LINK          = 25;
	const LANG_ABOUT_TITLE             = 26;
	const LANG_ABOUT_TEXT              = 27;
	const LANG_MODAL_ERROR_TITLE       = 28;
	const LANG_MODAL_ERROR_TEXT        = 29;
	const LANG_MODAL_CLOSE             = 30;
	const LANG_MODAL_SUCCESS_TITLE     = 31;
	const LANG_MODAL_SUCCESS_TEXT      = 32;
	const LANG_FORM_ERROR_EMPTY        = 33;
	const LANG_FORM_ERROR_NOOTHERKEY   = 34;
	const LANG_FORM_ERROR_NOSUCHID     = 35;
	const LANG_FORM_ERROR_BADPWD       = 36;
	const LANG_FORM_ERROR_FILETOOBIG   = 37;
	const LANG_FORM_ERROR_FILEERROR    = 38;
	const LANG_FORM_ERROR_IDEXISTS     = 39;
	const LANG_SEE_PWD_DOES_NOT_EXIST  = 40;
	const LANG_SEE_ENTRY_TITLE         = 41;
	const LANG_SEE_ENTRY_URL           = 42;
	const LANG_SEE_ENTRY_USERNAME      = 43;
	const LANG_SEE_ENTRY_PASSWORD      = 44;
	const LANG_SEE_ENTRY_LOAD          = 45;


	public static $availableLangData = array();
	private static $langData = array();

	/**
	 * The selected language code.
	 */
	public static $lang = null;

	/**
	 * Gets a language string of the selected language, corresponding to the
	 * given language key.
	 */
	public static function l($idx) {
		return self::$langData[$idx];
	}

	/**
	 * Selects the language to use in the user interface, if it has been
	 * registered before.
	 */
	public static function setLang($lang) {
		if(!array_key_exists($lang, self::$availableLangData))
			return false;
		self::$lang = $lang;
		self::$langData = self::$availableLangData[self::$lang];
		return true;
	}

	/**
	 * Registers a language with its data.
	 */
	public static function registerLang($lang, $data) {
		if(array_key_exists($lang, self::$availableLangData))
			return false;
		self::$availableLangData[$lang] = $data;
		return true;
	}

	/**
	 * Returns the list of all available language codes (i.e that have been
	 * registered).
	 */
	public static function avilablableLangs() {
		return array_keys(self::$availableLangData);
	}

	/**
	 * Extracts the prefered language from the ACCEPT-LANGUAGE HTTP header.
	 */
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

	/** The file has been correctly uploaded. */
	const GET_FILE_OK = 1;
	/** The form input is empty. */
	const GET_FILE_EMPTY = 2;
	/** The file is too big to be uploaded. */
	const GET_FILE_TOO_BIG = 3;
	/** An error occured when uploading the file. */
	const GET_FILE_ERROR = 4;

	/**
	 * Checks whether the given file has been correctly uploaded, and gets
	 * its temporary filename.
	 */
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

	/**
	 * Returns the POST var of the given name, or an empty string if it does
	 * not exist.
	 */
	public static function getPost($name)
	{
		return isset($_POST[$name]) ? $_POST[$name] : "";
	}

	/**
	 * Returns the string in a html-printable format.
	 */
	public static function htmlify($s)
	{
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
	}
}

?>