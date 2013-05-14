<?php

require_once "kphpui/conf.php";
require_once "kphpui/ajaxui.php";

$isPwd = KphpUI::getString("r", $_GET) == "p" &&
	($uuid = KphpUI::getString("uuid", $_POST)) != null;

$dbid = KphpUI::getString("dbid", $_POST);
$pwd = KphpUI::getString("mainPwd", $_POST);
$usePwdForCK = KphpUI::getString("usePwdForCK", $_POST, "0") != "0";
$otherPwd = KphpUI::getString("openPwd1", $_POST);

$ui = new AjaxUI(AjaxUI::FAIL);

if(strlen($dbid) == 0)
{
	$ui->setResult(AjaxUI::SOMETHING_EMPTY);
	$ui->setHTML("dbid");
}
elseif(strlen($pwd) == 0)
{
	$ui->setResult(AjaxUI::SOMETHING_EMPTY);
	$ui->setHTML("mainPwd");
}
elseif(!$usePwdForCK && strlen($otherPwd) == 0)
{
	$ui->setResult(AjaxUI::SOMETHING_EMPTY);
	$ui->setHTML("openPwd1");
}
else
{
	require_once KEEPASSPHP_LOCATION;
	KeePassPHP::init(KEEPASSPHP_DEBUG);
	if(KeePassPHP::exists($dbid))
	{
		$db = KeePassPHP::get($dbid, $pwd, $usePwdForCK ? $pwd : $otherPwd);
		if($db != null && $db->tryLoad())
		{
			require_once "kphpui/htmlformat.php";
			if($isPwd)
			{
				$pwd = $db->getPassword($uuid);
				if($pwd != null)
				{
					$ui->setResult(AjaxUI::SUCCESS);
					$ui->addHTML(HTMLFormat::formatPassword($pwd));
				}
				else
				{
					$ui->setResult(AjaxUI::PASSWORD_NOT_FOUND);
					$ui->addHTML(HTMLFormat::PASSWORD_NOT_FOUND);
				}
			}
			else
			{
				$ui->setResult(AjaxUI::SUCCESS);
				$ui->addHTML(HTMLFormat::formatEntries($db));
			}
		}
		else
			$ui->setResult(AjaxUI::BAD_PASSWORD);
	}
	else
		$ui->setResult(AjaxUI::NO_SUCH_ID);
	$ui->addDebug(KeePassPHP::$errordump, KeePassPHP::$isError);
}

$ui->display();

?>