<?php

require_once "keepassphpui/main.php";

// alias KeePassPHP
use \KeePassPHP\KeePassPHP as KeePassPHP;

/**
 * A class that manages the answer that will be sent as a stringified json
 * object. This object has three properties: 'status', containing an integer
 * describing the result; 'result', containing a string which is the result;
 * and 'debug' if there is debug information to send.
 */
class AjaxAnswer
{
	private $status;
	private $result;
	private $debug;

	/** Status: unexpected fail. */
	const FAIL = 0;
	/** Status: operation succeeded (either database opening or password extracting). */
	const SUCCESS = 1;
	/** Status: wrong password for the database. */
	const BAD_PASSWORD = 2;
	/** Status: the given database ID does not exist. */
	const NO_SUCH_ID = 3;
	/** Status: a POST parameter is missing or empty. */
	const SOMETHING_EMPTY = 4;
	/** Status: the requested password does not exist in the database (this is weird). */
	const PASSWORD_NOT_FOUND = 5;

	public function __construct()
	{
		$this->status = self::FAIL;
		$this->result = null;
		$this->debug = null;
	}

	/**
	 * Sets the 'status' and 'result' fields of the answer.
	 */
	public function set($status, $result = "")
	{
		$this->status = $status;
		$this->result = $result;
	}

	/**
	 * Sets the 'debug' field of the answer.
	 */
	public function setDebug($debug)
	{
		$this->debug = $debug;
	}

	/**
	 * Sends the answer. You should not output something anymore after
	 * calling this method.
	 */
	public function send()
	{
		header('Content-Type: application/json; charset=utf-8');
		$out = array("status" => $this->status, "result" => $this->result);
		if(!empty($this->debug))
			$out["debug"] = $this->debug;
		echo json_encode($out);
	}
}

function visitDatabase(\KeePassPHP\Database $db)
{
	$s = '<table class="table table-hover form-inline"><thead><tr><th> </th><th>'
		. KPHPUI::l(KPHPUI::LANG_SEE_ENTRY_TITLE) . '</th><th>'
		. KPHPUI::l(KPHPUI::LANG_SEE_ENTRY_URL) . '</th><th>'
		. KPHPUI::l(KPHPUI::LANG_SEE_ENTRY_USERNAME) . '</th><th>'
		. KPHPUI::l(KPHPUI::LANG_SEE_ENTRY_PASSWORD) . '</th></tr></thead><tbody>';

	$groups = $db->getGroups();
	if($groups != null)
	{
		foreach($groups as &$g)
			$s .= visitGroup($db, $g);
	}

	return $s . '</tbody></table>';
}

function visitGroup(\KeePassPHP\Database $db, \KeePassPHP\Group $group)
{
	$s = "";
	if($group->groups != null)
	{
		foreach($group->groups as &$g)
			$s .= visitGroup($db, $g);
	}
	if($group->entries != null)
	{
		foreach($group->entries as &$e)
			$s .= visitEntry($db, $e);
	}
	return $s;
}

function visitEntry(\KeePassPHP\Database $db, \KeePassPHP\Entry $entry)
{
	$icon = null;
	if(!empty($entry->customIcon))
		$icon = $db->getCustomIcon($entry->customIcon);
	if(empty($icon) && !empty($entry->icon))
		$icon = KPHPUI::iconPath($entry->icon);

	$uuid = bin2hex(base64_decode($entry->uuid));

	$url = $entry->url;
	$protoSep = strpos($url, "://");
	$proto = $protoSep === false ? null : substr($url, 0, $protoSep);
	$isHttp = $proto == "http" || $proto == "https";
	$displayed = $isHttp ? substr($url, $protoSep + 3) : $url;

	return '<tr><td>' . ($icon == null ? '' : '<img src="' . KPHPUI::htmlify($icon) . '" />')
		. '</td><td>' . KPHPUI::htmlify($entry->title) . '</td>'
		. '<td>' . ($isHttp ? '<a href="' : '<span title="') . KPHPUI::htmlify($url) . '">'
		. KPHPUI::htmlify(strlen($displayed) > 20 ? substr($displayed, 0, 17) . '...' : $displayed)
		. ($isHttp ? '</a>' : '</span>') . '</td>'
		. '<td><input type="text" class="col-sm-3 form-control selectOnFocus" value="' . KPHPUI::htmlify($entry->username) . '" /></td>'
		. '<td id="pwd_' . $uuid . '"><button type="button" class="btn btn-primary passwordLoader" data-uuid="'
		. $uuid . '" autocomplete="off" data-loading-text="...">' . KPHPUI::l(KPHPUI::LANG_SEE_ENTRY_LOAD) . '</button></td></tr>';
}

$answer = new AjaxAnswer();

$dbid = KPHPUI::getPost("dbid");
$mainPwd = KPHPUI::getPost("main_pwd");
$usePwdInKey = KPHPUI::getPost("use_pwd_in_key") == "true";
$otherPwd = KPHPUI::getPost("open_other_pwd");

if(empty($dbid))
	$answer->set(AjaxAnswer::SOMETHING_EMPTY, "dbid");
elseif(empty($mainPwd))
	$answer->set(AjaxAnswer::SOMETHING_EMPTY, "main_pwd");
elseif(!$usePwdInKey && empty($otherPwd))
	$answer->set(AjaxAnswer::SOMETHING_EMPTY, "open_other_pwd");
else
{
	require_once KEEPASSPHP_LOCATION;
	KeePassPHP::init(null, KEEPASSPHP_DEBUG);

	if(KeePassPHP::existsKphpDB($dbid))
	{
		$uuid = KPHPUI::getPost("uuid");
		$getPasswords = !empty($uuid);
		$db = KeePassPHP::getDatabase($dbid,
			$usePwdInKey ? KeePassPHP::extractHalfPassword($mainPwd) : $mainPwd,
			$usePwdInKey ? $mainPwd : $otherPwd,
			$getPasswords);
		if($db != null)
		{
			if($getPasswords)
			{
				$pwd = $db->getPassword(base64_encode(hex2bin($uuid)));
				if($pwd != null)
					$answer->set(AjaxAnswer::SUCCESS, '<input type="text" class="verysmall selectOnFocus form-control" value="' . KPHPUI::htmlify($pwd) . '" style="font-size:3px !important;"/>');
				else
					$answer->set(AjaxAnswer::PASSWORD_NOT_FOUND, '<span class="label label-danger">' . KPHPUI::l(KPHPUI::LANG_SEE_PWD_DOES_NOT_EXIST) . '</span>');
			}
			else
				$answer->set(AjaxAnswer::SUCCESS, visitDatabase($db));
		}
		else
			$answer->set(AjaxAnswer::BAD_PASSWORD);
	}
	else
		$answer->set(AjaxAnswer::NO_SUCH_ID);

	if(KeePassPHP::$debug && !empty(KeePassPHP::$debugData))
		$answer->setDebug(KeePassPHP::$debugData);
}

$answer->send();

?>
