<?php

/**
 * Abstract class for an user interface.
 */
abstract class KphpUI
{
	protected $debug;
	protected $isError;

	protected function __construct()
	{
		$this->debug = "";
		$this->isError = false;
	}

	public function addDebug($debug, $isError)
	{
		$this->debug .= $debug;
		$this->isError = $this->isError || $isError;
	}

	abstract public function display();

	/**
	 * Returns the string in a html-printable format : encoded
	 * in UTF8, and with some special chars rightly encoded.
	 * @param string $s
	 * @return string
	 */
	public static function makePrintable($s)
	{
		return htmlspecialchars(utf8_encode($s), ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Returns the value associated with the key $name in the array $array if
	 * the key exists exists, and $default otherwise.
	 * @param mixed $name
	 * @param array $array
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getString($name, $array, $default = "")
	{
		return isset($array[$name]) ? $array[$name] : $default;
	}

	

}

?>
