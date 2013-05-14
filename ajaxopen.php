<?php

require "kphpdisplay/basic.php";
require_once "kphpdisplay/ajaxdisplay.php";

$isPwd = getString("r", $_GET) == "p" &&
	($uuid = getString("uuid", $_POST)) != null;

$dbid = getString("dbid", $_POST);
$pwd = getString("mainPwd", $_POST);
$usePwdForCK = getString("usePwdForCK", $_POST, "0") != "0";
$otherPwd = getString("openPwd1", $_POST);

$display = new AjaxDisplay(AjaxDisplay::FAIL);

if(strlen($dbid) == 0)
{
	$display->setResult(AjaxDisplay::SOMETHING_EMPTY);
	$display->setHTML("dbid");
}
elseif(strlen($pwd) == 0)
{
	$display->setResult(AjaxDisplay::SOMETHING_EMPTY);
	$display->setHTML("mainPwd");
}
elseif(!$usePwdForCK && strlen($otherPwd) == 0)
{
	$display->setResult(AjaxDisplay::SOMETHING_EMPTY);
	$display->setHTML("openPwd1");
}
else
{
	require_once "keepassphp/keepassphp.php";
	KeePassPHP::init(true);
	if(KeePassPHP::exists($dbid))
	{
		$db = KeePassPHP::get($dbid, $pwd, $usePwdForCK ? $pwd : $otherPwd);
		if($db != null && $db->tryLoad())
		{
			require_once "kphpdisplay/htmlformat.php";
			if($isPwd)
			{
				$pwd = $db->getPassword($uuid);
				if($pwd != null)
				{
					$display->setResult(AjaxDisplay::SUCCESS);
					$display->addHTML(HTMLFormat::formatPassword($pwd));
				}
				else
				{
					$display->setResult(AjaxDisplay::PASSWORD_NOT_FOUND);
					$display->addHTML(HTMLFormat::PASSWORD_NOT_FOUND);
				}
			}
			else
			{
				$display->setResult(AjaxDisplay::SUCCESS);
				$display->addHTML(HTMLFormat::formatEntries($db));
			}
		}
		else
			$display->setResult(AjaxDisplay::BAD_PASSWORD);
	}
	else
		$display->setResult(AjaxDisplay::NO_SUCH_ID);
	$display->addDebug(KeePassPHP::$errordump);
	if(KeePassPHP::$isError)
		$display->raiseError(KeePassPHP::$errordump);
}

$display->display();

?>