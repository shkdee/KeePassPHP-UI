<?php

require_once "kphpui/conf.php";
require_once "kphpui/mainui.php";

define("MAX_FILE_SIZE", 100000);

function getFile($k)
{
		if(!isset($_FILES[$k]))
				return null;
		$f = $_FILES[$k];
		if($f['name'] == '' || $f['error'] == UPLOAD_ERR_NO_FILE || $f['tmp_name'] == '')
				return null;
		return $f;
}

function checkFile($k, $f, $display)
{
	if($f == null)
	{
		$display->setIfEmpty($k, MainUI::HI_EMPTY, 'warning');
		return null;
	}
	if($f['error'] == UPLOAD_ERR_INI_SIZE || $f['error'] == UPLOAD_ERR_FORM_SIZE
		|| $f['size'] > MAX_FILE_SIZE)
	{
		$display->setIfEmpty($k, MainUI::HI_FILETOOBIG, 'error');
		return null;
	}
	if($f['error'] != UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name']))
	{
		$display->setIfEmpty($k, MainUI::HI_FILEERROR, 'error');
		return null;
	}
	return $f['tmp_name'];
}

$ui = new MainUI();

$submitted = KphpUI::getString("submitted", $_POST);
if($submitted == "add")
{
	if(($dbid = KphpUI::getString("addDbid", $_POST)) == "")
		$ui->setIfEmpty("addDbid", MainUI::HI_EMPTY, "warning");
	if(($mainPwd = KphpUI::getString("addMainPwd", $_POST)) == "")
		$ui->setIfEmpty("addMainPwd", MainUI::HI_EMPTY, "warning");
	$kdbxFile = checkFile("addKdbxFile", getFile("addKdbxFile"), $ui);
	$pwd1 = KphpUI::getString("addPwd1", $_POST);
	$keyfile = getFile("addFile1");
	if(!($usePwdForCK = (KphpUI::getString("addUsePwdForCK", $_POST, "") != "")) &&
		$pwd1 == "" && $keyfile == null)
	{
		$ui->setIfEmpty("addUsePwdForCK", null, "error");
		$ui->setIfEmpty("addPwd1", MainUI::HI_NOOTHERKEY, "error");
		$ui->setIfEmpty("addFile1", MainUI::HI_ERROR, "error");
	}
	if(!$ui->isSomethingEmpty)
	{
		require_once KEEPASSPHP_LOCATION;
		KeePassPHP::init(KEEPASSPHP_DEBUG);
		if(!KeePassPHP::exists($dbid) || KeePassPHP::checkPassword($dbid, $mainPwd))
		{
			$keys = $usePwdForCK ? array(array(KeePassPHP::KEY_PWD, $mainPwd)) : array();
			if($pwd1 != '')
				$keys[] = array(KeePassPHP::KEY_PWD, $pwd1);
			if($keyfile != null)
				if(($keyfile = checkFile("addFile1", $keyfile, $ui)) != null)
					$keys[] = array(KeePassPHP::KEY_FILE, $keyfile);
			if(KeePassPHP::checkKeys($kdbxFile, $keys))
			{
				if(KeePassPHP::tryAdd($kdbxFile, $dbid, $mainPwd, $keys))
					$ui->addSuccess = true;
			}
			else
			{
				if($usePwdForCK)
					$ui->setIfEmpty("addMainPwd", MainUI::HI_BADPWD, "error");
				if($pwd1 != "")
					$ui->setIfEmpty("addPwd1", MainUI::HI_BADPWD, "error");
				if($keyfile != null)
					$ui->setIfEmpty ("addFile1", MainUI::HI_BADPWD, "error");
			}
		}
		else
		{
			$ui->setIfEmpty("addDbid", MainUI::HI_IDEXISTS, "error");
			$ui->setIfEmpty("addMainPwd", null, "error");
		}
		$ui->addDebug(KeepassPHP::$errordump, KeePassPHP::$isError);
	}
}

$ui->display();

?>