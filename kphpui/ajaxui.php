<?php

/**
 * Description of AjaxDisplay
 *
 * @author Louis
 */

require_once "kphpui.php";

class AjaxUI extends KphpUI
{
	private $result;
	private $html;   

	const SUCCESS = "1";
	const FAIL = "0";
	const BAD_PASSWORD = "2";
	const NO_SUCH_ID = "3";
	const SOMETHING_EMPTY = "4";
	const PASSWORD_NOT_FOUND = "5";

	public function __construct($defaultResult = self::FAIL)
	{
		parent::__construct();
		$this->result = $defaultResult;
		$this->html = "";
	}

	public function setResult($r)
	{
		$this->result = $r;
	}

	public function setHTML($h)
	{
		$this->html = $h;
	}

	public function addHTML($h)
	{
		$this->html .= $h;
	}

	public function display()
	{
		if($this->isError)
			$this->result = self::FAIL;
		echo json_encode(array($this->result,
			$this->result == self::FAIL ? $this->debug : $this->html,
			$this->result != self::SUCCESS ? $this->debug : ""));
	}
}

?>
