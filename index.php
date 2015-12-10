<?php

require_once "keepassphpui/main.php";

$hasAddSuccess = false;

// give javascript error messages for the selected language
$javascriptContent = "var errorMessages = {" . 
	"empty: \"" . addslashes(KPHPUI::l(KPHPUI::LANG_FORM_ERROR_EMPTY)) . "\", " .
	"nootherkey: \"" . addslashes(KPHPUI::l(KPHPUI::LANG_FORM_ERROR_NOOTHERKEY)) . "\", " .
	"nosuchid: \"" . addslashes(KPHPUI::l(KPHPUI::LANG_FORM_ERROR_NOSUCHID)) . "\", " .
	"badpwd: \"" . addslashes(KPHPUI::l(KPHPUI::LANG_FORM_ERROR_BADPWD)) . "\", " .
	"filetoobig: \"" . addslashes(KPHPUI::l(KPHPUI::LANG_FORM_ERROR_FILETOOBIG)) . "\", " .
	"fileerror: \"" . addslashes(KPHPUI::l(KPHPUI::LANG_FORM_ERROR_FILEERROR)) . "\", " .
	"idexists: \"" . addslashes(KPHPUI::l(KPHPUI::LANG_FORM_ERROR_IDEXISTS)) . "\"};";

// the page that should be displayed at firt
$p = isset($_GET["p"]) && $_GET["p"] == "add" ? "add" : "open";

$url_nolang_params = isset($_GET["p"]) && $_GET["p"] == $p ? ("&amp;p=" . $p) : "";
$url_lang_param = isset($_GET["l"]) && $_GET["l"] == KPHPUI::$lang ? ("l=" . KPHPUI::$lang . "&amp;") : "";

// always force lang of ajax queries
$javascriptContent .= "\nvar forceLang = \"" . KPHPUI::$lang . "\";";

// add a database if the add form is being submitted
$formErrors = array();
$submitted = KPHPUI::getPost("submitted");
if($submitted == "add")
{
	// check all POST & FILE parameters
	$dbid = KPHPUI::getPost("add_dbid");
	$mainPwd = KPHPUI::getPost("add_main_pwd");
	$otherPwd = KPHPUI::getPost("add_other_pwd");
	$kdbxFile = null;
	$kdbxFileStatus = KPHPUI::getFile("add_kdbx_file", $kdbxFile);
	$keyFile = null;
	$keyFileStatus = KPHPUI::getFile("add_other_keyfile", $keyFile);
	$usePwdInKey = !empty(KPHPUI::getPost("add_use_pwd_in_key"));

	$ok = true;
	if(empty($dbid))
	{
		$ok = false;
		$formErrors["add_dbid"] = "empty";
	}
	if(empty($mainPwd))
	{
		$ok = false;
		$formErrors["add_main_pwd"] = "empty";
	}
	if(!$usePwdInKey && empty($otherPwd) && $keyFileStatus != KPHPUI::GET_FILE_OK)
	{
		$ok = false;
		$formErrors["add_other_pwd"] = "nootherkey";
		$formErrors["add_other_keyfile"] = "nootherkey";
	}
	if($kdbxFileStatus != KPHPUI::GET_FILE_OK)
	{
		$ok = false;
		$formErrors["add_kdbx_file"] = $kdbxFileStatus == KPHPUI::GET_FILE_EMPTY
			? "empty" : $kdbxFileStatus == KPHPUI::GET_FILE_TOO_BIG
			? "filetoobig" : "fileerror";
	}

	if($ok)
	{
		// every parameter seems to be fine:
		// include KeePassPHP and try to add the database 
		require_once KEEPASSPHP_LOCATION;
		KeePassPHP::init(KEEPASSPHP_DEBUG);

		if(!KeePassPHP::exists($dbid) || KeePassPHP::checkPassword($dbid, $mainPwd))
		{
			$keys = array();
			if($usePwdInKey)
				$keys[] = array(KeePassPHP::KEY_PWD, $mainPwd);
			else if(!empty($otherPwd))
				$keys[] = array(KeePassPHP::KEY_PWD, $otherPwd);
			if($keyFileStatus == KPHPUI::GET_FILE_OK)
				$keys[] = array(KeePassPHP::KEY_FILE, $keyFile);

			if(KeePassPHP::checkKeys($kdbxFile, $keys))
			{
				if(KeePassPHP::tryAdd($kdbxFile, $dbid, $mainPwd, $keys))
				{
					$hasAddSuccess = true;
					$javascriptContent .= "\n$(function() { $('#modal_success').modal('show'); });";
				}
				else
					$javascriptContent .= "\n$(function() { raiseError(\"" . str_replace("\n", "\\n", addslashes(KeePassPHP::$errordump)) . "\"); });";
			}
			else
			{
				if(!empty($usePwdInKey))
					$formErrors["add_main_pwd"] = "badpwd";
				if(!empty($otherPwd))
					$formErrors["add_other_pwd"] = "badpwd";
				if($keyFileStatus == KPHPUI::GET_FILE_OK)
					$formErrors["add_other_keyfile"] = "badpwd";
			}
		}
		else
			$formErrors["add_dbid"] = "idexists";

		if(KEEPASSPHP_DEBUG)
			$javascriptContent .= "\nvar debugTrace = \"" . str_replace("\n", "\\n", addslashes(KeePassPHP::$errordump)) . "\";";
	}
}

// errors to be displayed by javascript after the page loading
$javascriptContent .= "\nvar formErrors = {";
$isFirst = true;
foreach($formErrors as $input => &$error)
{
	if($isFirst)
		$isFirst = false;
	else
		$javascriptContent .= ", ";
	$javascriptContent .= $input . ": '" . $error . "'";
}
$javascriptContent .= "};";

// display the HTML
?>

<!DOCTYPE html>
<html lang="<?php echo KPHPUI::$lang; ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo KPHPUI::l(KPHPUI::LANG_PAGE_TITLE); ?></title>
	<link rel="stylesheet" href="css/bootstrap.min.css?3.3.6" />
	<link rel="stylesheet" href="css/main.css?1.0" />
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation"<?php if($p == "open") echo ' class="active"'; ?>><a href="#open" aria-controls="open" role="tab" data-toggle="tab" id="open_tab_a"><?php echo KPHPUI::l(KPHPUI::LANG_TAB_OPEN); ?></a></li>
					<li role="presentation"<?php if($p == "add") echo ' class="active"'; ?>><a href="#add" aria-controls="add" role="tab" data-toggle="tab"><?php echo KPHPUI::l(KPHPUI::LANG_TAB_ADD); ?></a></li>
					<li role="presentation" class="disabled" id="see_tab_li"><a href="#see" aria-controls="see" role="tab" data-toggle="tab"><?php echo KPHPUI::l(KPHPUI::LANG_TAB_SEE); ?></a></li>
					<li role="presentation"><a href="#about" aria-controls="about" role="tab" data-toggle="tab"><?php echo KPHPUI::l(KPHPUI::LANG_TAB_ABOUT); ?></a></li>
					<li role="presentation" class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#lang" role="button"><?php echo KPHPUI::$lang; ?> <span class="caret"></span></a>
						<ul class="dropdown-menu"><?php
$availableLangs = KPHPUI::avilablableLangs();
foreach($availableLangs as &$lang)
	echo '<li><a href="?l=' . $lang . $url_nolang_params . '">' . $lang . '</a></li>';
						?></ul>
					</li>
					<li role="presentation"><a type="button" id="btn_clean_all" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></a></li>
				</ul>
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane fade<?php if($p == "open") echo ' active in'; ?>" id="open">
						<div class="row">
							<div class="col-sm-10 col-sm-offset-1">
								<form class="form-horizontal" method="post" action="./?<?php echo $url_lang_param; ?>p=open" id="form_open">
									<fieldset>
										<legend><?php echo KPHPUI::l(KPHPUI::LANG_OPEN_TITLE); ?></legend>
										<div class="form-group">
											<label class="col-sm-4 control-label" for="dbid"><?php echo KPHPUI::l(KPHPUI::LANG_OPEN_DBID_LABEL); ?></label>
											<div class="col-sm-6">
												<input type="text" class="form-control" id="dbid" name="dbid" placeholder="<?php echo KPHPUI::l(KPHPUI::LANG_OPEN_DBID_PLACEHOLDER); ?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" for="main_pwd"><?php echo KPHPUI::l(KPHPUI::LANG_OPEN_PWD_LABEL); ?></label>
											<div class="col-sm-6">
												<input type="password" class="form-control" id="main_pwd" name="main_pwd" placeholder="<?php echo KPHPUI::l(KPHPUI::LANG_PWD_PLACEHOLDER); ?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-7 control-label" for="use_pwd_in_key"><?php echo KPHPUI::l(KPHPUI::LANG_OPEN_USE_AS_KEY); ?></label>
											<div class="col-sm-1">
												<input type="checkbox" id="use_pwd_in_key" name="use_pwd_in_key" checked="checked" value="1" />
											</div>
										</div>
										<div id="open_more" class="collapse">
											<div class="form-group">
												<label class="col-sm-4 control-label" for="open_other_pwd"><?php echo KPHPUI::l(KPHPUI::LANG_OPEN_OTHER_PWD_LABEL); ?></label>
												<div class="col-sm-6">
													<input type="password" class="form-control" id="open_other_pwd" name="open_other_pwd" placeholder="<?php echo KPHPUI::l(KPHPUI::LANG_PWD_PLACEHOLDER); ?>" />
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-offset-4 col-sm-6">
												<input type="hidden" name="submitted" value="open" />
												<button type="submit" class="btn btn-primary" data-loading-text="<?php echo KPHPUI::l(KPHPUI::LANG_OPEN_SEND_LOADING); ?>" autocomplete="off"><?php echo KPHPUI::l(KPHPUI::LANG_OPEN_SEND); ?></button>
											</div>
										</div>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade<?php if($p == "add") echo ' active in'; ?>" id="add">
						<div class="row">
							<div class="col-sm-10 col-sm-offset-1">
								<form class="form-horizontal" method="post" action="./?<?php echo $url_lang_param; ?>p=add" enctype="multipart/form-data" id="form_add">
									<input type="hidden" name="<?php echo MAX_FILE_SIZE; ?>" value="100000" />
									<fieldset>
										<legend><?php echo KPHPUI::l(KPHPUI::LANG_ADD_TITLE); ?></legend>
										<div class="form-group">
											<label class="col-sm-4 control-label" for="add_dbid"><?php echo KPHPUI::l(KPHPUI::LANG_ADD_DBID_LABEL); ?></label>
											<div class="col-sm-6">
												<input type="text" class="form-control" id="add_dbid" name="add_dbid" placeholder="<?php echo KPHPUI::l(KPHPUI::LANG_ADD_DBID_PLACEHOLDER); ?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" for="add_kdbx_file"><?php echo KPHPUI::l(KPHPUI::LANG_ADD_FILE_LABEL); ?></label>
											<div class="col-sm-6">
												<input type="file" id="add_kdbx_file" name="add_kdbx_file" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" for="add_main_pwd"><?php echo KPHPUI::l(KPHPUI::LANG_ADD_PWD_LABEL); ?></label>
											<div class="col-sm-6">
												<input type="password" class="form-control" id="add_main_pwd" name="add_main_pwd" placeholder="<?php echo KPHPUI::l(KPHPUI::LANG_PWD_PLACEHOLDER); ?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-7 control-label" for="add_use_pwd_in_key"><?php echo KPHPUI::l(KPHPUI::LANG_ADD_USE_AS_KEY); ?></label>
											<div class="col-sm-1">
												<input type="checkbox" id="add_use_pwd_in_key" name="add_use_pwd_in_key" checked="checked" value="1" />
											</div>
										</div>
										<div id="add_more" class="collapse">
											<div class="form-group">
												<label class="col-sm-4 control-label" for="add_other_pwd"><?php echo KPHPUI::l(KPHPUI::LANG_ADD_OTHER_PWD_LABEL); ?></label>
												<div class="col-sm-6">
													<input type="password" class="form-control" id="add_other_pwd" name="add_other_pwd" placeholder="<?php echo KPHPUI::l(KPHPUI::LANG_PWD_PLACEHOLDER); ?>" />
												</div>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-4 control-label" for="add_other_keyfile"><?php echo KPHPUI::l(KPHPUI::LANG_ADD_OTHER_KEYFILE_LABEL); ?></label>
											<div class="col-sm-6">
												<input type="file" id="add_other_keyfile" name="add_other_keyfile" />
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-offset-4 col-sm-6">
												<input type="hidden" name="submitted" value="add" />
												<button type="submit" class="btn btn-primary"><?php echo KPHPUI::l(KPHPUI::LANG_ADD_SEND); ?></button>
											</div>
										</div>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade" id="see">
						<div class="alert alert-warning" id="see_alert">
							<p class="lead"><?php echo KPHPUI::l(KPHPUI::LANG_SEE_NO_DB_TITLE); ?></p>
							<p><?php echo KPHPUI::l(KPHPUI::LANG_SEE_NO_DB_TEXT); ?> <a href="#open" aria-controls="add" data-toggle="tab"><?php echo KPHPUI::l(KPHPUI::LANG_SEE_NO_DB_LINK); ?></a></p>
						</div>
						<div id="see_results" class="hide"></div>
					</div>
					<div role="tabpanel" class="tab-pane fade" id="about">
						<div class="row">
							<div class="col-sm-10 col-sm-offset-1">
								<p class="lead"><?php echo KPHPUI::l(KPHPUI::LANG_ABOUT_TITLE); ?></p>
								<p><?php echo KPHPUI::l(KPHPUI::LANG_ABOUT_TEXT); ?></p>
							</div>
						</div>
					</div>
				</div>
				<pre id="debugtrace" class="well hide"></pre>
			</div>
		</div>
	</div>
	<div class="modal fade" id="modal_error" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php echo KPHPUI::l(KPHPUI::LANG_MODAL_ERROR_TITLE); ?></h4>
				</div>
				<div class="modal-body">
					<p class="alert alert-danger"><?php echo KPHPUI::l(KPHPUI::LANG_MODAL_ERROR_TEXT); ?></p>
					<pre class="well"></pre>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo KPHPUI::l(KPHPUI::LANG_MODAL_CLOSE); ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
if($hasAddSuccess) {
?>
	<div class="modal fade" id="modal_success" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h3><?php echo KPHPUI::l(KPHPUI::LANG_MODAL_SUCCESS_TITLE); ?></h3>
				</div>
				<div class="modal-body">
					<p class="alert alert-success"><?php echo KPHPUI::l(KPHPUI::LANG_MODAL_SUCCESS_TEXT); ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo KPHPUI::l(KPHPUI::LANG_MODAL_CLOSE); ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<script src="js/bootstrap.min.js?3.3.6"></script>
	<script type="text/javascript"><?php echo $javascriptContent; ?></script>
	<script src="js/main.js?1.1"></script>
</body>

</html>