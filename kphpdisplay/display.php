<?php

abstract class Display
{
	private $debugDump;

	protected function __construct()
	{
		$this->debugDump = "";
	}

	/**
	 * Should print all the required information to end abruptly the page,
	 * and possibly the given string $error which should contain a description
	 * of the error.
	 */
	abstract public function raiseError($error);

	/**
	 * Adds the given string to the debug data.
	 * @param string $debug
	 */
	public function addDebug($debug)
	{
		$this->debugDump .= $debug;
	}

	/**
	 * Gets the currently saved debug data.
	 * @return string
	 */
	public function dumpDebug()
	{
		return $this->debugDump;
	}

	/**
	 * Returns the string in a html-printable format : encoded
	 * in UTF8, and with some special chars rightly encoded. Every piece of
	 * data printed in a web page and coming from KeePassPHP (either a password,
	 * an username, or a debug stuff, *anything*) should be 'protected' by this
	 * method.
	 * @param string $s
	 * @return string
	 */
	public static function makePrintable($s)
	{
		return htmlspecialchars(utf8_encode($s), ENT_QUOTES, 'UTF-8');
	}
}

?>
