<?php

/**
 * Description of IndexDisplay
 *
 * @author Louis
 */

require_once "keepassphp/display.php";

class IndexDisplay extends Display
{
	public $isError;
	public $addSuccess;

	private $isJavascript;
	private $pane;
	private $inputs;
	private $errorMsg;

	const SEE = "see";
	const ABOUT = "about";
	const ADD = "add";
	const OPEN = "open";

	const PLACEHOLDER_PWD = 'placeholder="Mot de passe"';
	const PLACEHOLDER_ID = 'placeholder="Entrez votre ID"';

	const START_HI_NOHIDE = '<span class="help-inline" ident="';
	const START_HI_HIDE = '<span class="help-inline hide" ident="';
	const HI_EMPTY = 'empty"><span class="label label-important">C\'est vide !</span>';
	const HI_ERROR = 'error"><span class="label label-important">Erreur</span>';
	const HI_NOOTHERKEY = 'nootherkey"><span class="label label-important">Erreur</span> Si le mot de passe n\'est pas utilisé comme clé, il en faut au moins une autre.';
	const HI_NOSUCHID = 'nosuchid"><span class="label label-important">Erreur</span> L\'ID utilisé n\'existe pas.';
	const HI_BADPWD = 'badpwd"><span class="label label-important">Erreur</span> Le mot de passe utilisé ne semble pas bon.';
	const HI_FILETOOBIG = 'filetoobig"><span class="label label-important">Erreur</span>Le fichier est trop gros.';
	const HI_FILEERROR = 'fileerror"><span class="label label-important">Erreur</span> Une erreur est survenue lors du téléchargement du fichier.';
	const HI_IDEXISTS = 'idexists"><span class="label label-important">Erreur</span> Cet ID existe déjà, et le mot de passe utilisé ne correspond pas.';

	public function __construct($isJavascript = true)
	{
		$this->isError = false;
		$this->isJavascript = $isJavascript;
		$this->addSuccess = false;

		$pane = isset($_GET['p']) ? $_GET['p'] : '';
		$this->pane = ($pane != self::SEE && $pane != self::ABOUT
			&& $pane != self::ADD) ? self::OPEN : $pane;
		$this->inputs = array(
			'dbid' => array('text', self::PLACEHOLDER_ID, null, null),
			'mainPwd' => array('password', self::PLACEHOLDER_PWD, null, null),
			'usePwdForCK' => array('checkbox', 'value="1"', null, null),
			'openPwd1' => array('password', self::PLACEHOLDER_PWD, null, null),
			'addDbid' => array('text', self::PLACEHOLDER_ID, null, null),
			'addMainPwd' => array('password', self::PLACEHOLDER_PWD, null, null),
			'addKdbxFile' => array('file', null, null, null),
			'addUsePwdForCK' => array('checkbox', 'value="1"', null, null),
			'addPwd1' => array('password', self::PLACEHOLDER_PWD, null, null),
			'addFile1' => array('file', null, null, null));
	}

	public function setIfEmpty($key, $ident, $style)
	{
		if($this->inputs[$key][2] == null && $this->inputs[$key][3] == null)
		{
			$this->isError = true;
			$this->inputs[$key][2] = $ident;
			$this->inputs[$key][3] = $style;
		}
	}

	private function displayControlGroup($k, $label)
	{
		$i = $this->inputs[$k];
		$d = ($i[0] == 'checkbox') ?
			((isset($_POST['submitted']) && !isset($_POST[$k])) ? null : 'checked="checked"') :
			(($i[0] == 'text' && isset($_POST[$k])) ? 'value="'.parent::makePrintable($_POST[$k]).'"' : null);
		return '<div class="control-group'.($i[3] != null ? ' '.$i[3] : '').'">'
			. '<label class="control-label" for="'.$k.'">'.$label.'</label>'
			. '<div class="controls"><input type="'.$i[0].'" id="'.$k.'" name="'
			. $k.'"'.($d != null ? ' '.$d : '').($i[1] != null ? ' '.$i[1] : '')
			.' />'.($i[2] == null ? '' : self::START_HI_NOHIDE.$i[2].'</span>')
			.'</div></div>';
	}

	public function raiseError($error)
	{
		$this->errorMsg = "<strong>Une erreur est survenue :</strong> " . $error;
		$this->display();
	}

	public function display()
	{
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
	<title>KeepassPHP</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">    
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href='http://fonts.googleapis.com/css?family=Philosopher' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="css/bootstrap.min.css" />
	<link rel="stylesheet" href="css/main.css" />

	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	</head>
	<body>
		<div class="container">
		 <div class="row">
			<div class="span10 offset1">
<?php
if($this->errorMsg != null)
	echo '<div class="alert alert-block alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $this->errorMsg . '</div>';
?>
				<ul class="nav nav-tabs" id="mainTab">
					<li<?php echo $this->pane == self::OPEN? ' class="active"' : ''; ?>><a href="?p=open#open" data-toggle="tab">Ouvrir</a></li>
					<li<?php echo $this->pane == self::ADD ? ' class="active"' : ''; ?>><a href="?p=add#add" data-toggle="tab">Ajouter</a></li>
					<li class="<?php echo $this->pane == self::SEE ? "active" : "disabled"; ?>"><a href="?p=see#see" data-toggle="tab">Parcourir</a></li>
					<li<?php echo $this->pane == self::ABOUT ? ' class="active"' : ''; ?>><a href="?p=about#about" data-toggle="tab">À propos</a></li>
					<li><a href="#" onclick="return cleanAll();" class="close" title="Reset and clean all tabs">&times;</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane <?php echo $this->pane == self::OPEN ? "active fade in" : "fade"; ?>" id="open">
						<div class="row">
							<div class="span8 offset1">
								<form class="form-horizontal" method="post" action="./?p=open" id="formOpen">
									<fieldset>
										<legend>Ouvrir une base de données</legend>
<?php
echo $this->displayControlGroup('dbid', 'Votre ID');
echo $this->displayControlGroup('mainPwd', 'Mot de passe');
?>
										<p><a data-toggle="collapse" href="#openmore" class="withToggleableChevron">Plus d'options <i class="icon-chevron-down"></i></a></p>
										<div id="openmore" class="collapse">
<?php
echo $this->displayControlGroup('usePwdForCK', 'Utiliser aussi ce mot de passe comme clé');
echo $this->displayControlGroup('openPwd1', 'Autre clé');
?>
										</div>
										<div class="control-group">
											<div class="controls">
												<input type="hidden" name="submitted" value="open" />
												<button type="submit" class="btn" data-loading-text="Envoi..." autocomplete="off">Envoyer</button>
											</div>
										</div>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
					<div class="tab-pane <?php echo $this->pane == self::ADD ? "active fade in" : "fade"; ?>" id="add">
						<div class="row">
							<div class="span8 offset1">
								<form class="form-horizontal" method="post" action="./?p=add" enctype="multipart/form-data" id="formAdd">
									<input type="hidden" name="MAX_FILE_SIZE" value="100000" /> 
									<fieldset>
										<legend>Ajouter une base de données</legend>
<?php
echo $this->displayControlGroup('addDbid', 'Votre ID');
echo $this->displayControlGroup('addKdbxFile', 'Fichier de base de données');
echo $this->displayControlGroup('addMainPwd', 'Mot de passe');
?>
										<p><a data-toggle="collapse" href="#addmore" class="withToggleableChevron">Plus d'options <i class="icon-chevron-down"></i></a></p>
										<div id="addmore" class="collapse">
<?php
echo $this->displayControlGroup('addUsePwdForCK', 'Utiliser aussi le mot de passe comme clé');
echo $this->displayControlGroup('addPwd1', 'Autre clé');
echo $this->displayControlGroup('addFile1', 'Autre clé');
?>
										</div>
										<div class="control-group">
											<div class="controls">
												<input type="hidden" name="submitted" value="add" />
												<button type="submit" class="btn">Envoyer</button>
											</div>
										</div>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
					<div class="tab-pane <?php echo $this->pane == self::SEE ? "active fade in" : "fade"; ?>" id="see">
						<div class="alert alert-block" id="seeAlert">
							<p class="lead">Aucune base de donnée n'a été chargée !</p>
							<p>Vous devez ouvrir une base de données avant de pouvoir en parcourir le contenu. <a href="#open" data-toggle="tab">Faites-le donc !</a></p>
						</div>
						<div id="seeResult" class="hide"></div>
					</div>
					<div class="tab-pane <?php echo $this->pane == self::ABOUT ? "active fade in" : "fade"; ?>" id="about">
						<div class="row">
							<div class="span8 offset1">
								<p class="lead">Bienvenue sur KeePassPHP !</p>
								<p>KeePassPHP est un portage de <a href="http://keepass.info">KeePass</a> en PHP, encore en développement.
									L'idée est de pouvoir accéder à sa base de données KeePass sur une autre machine sur la sienne (en
									essayant de ne pas trop brîser la chaîne de sécurité, mais il est toujours dur d'éviter les keyloggers
									dès qu'on est plus sur sa propre machine). L'interface web est réalisée avec
									<a href="http://twitter.github.com/bootstrap/index.html">Bootstrap</a> et <a href="http://jquery.com/">jQuery</a>.</p>
							</div>
						</div>
					</div>
				</div>
<?php
$debug = $this->dumpDebug();
echo '<pre id="debugtrace" class="well', ((strlen($debug) > 0) ?
	('">Debug trace:' . "\n " . $debug) : ' hide">'), "</pre>";
echo '</div></div></div>';

if($this->isJavascript)
{
	echo '<div class="hide superhide" id="errorMessages">',
		self::START_HI_HIDE . self::HI_EMPTY . '</span>',
		self::START_HI_HIDE . self::HI_ERROR . '</span>',
		self::START_HI_HIDE . self::HI_NOOTHERKEY . '</span>',
		self::START_HI_HIDE . self::HI_NOSUCHID . '</span>',
		self::START_HI_HIDE . self::HI_BADPWD . '</span></div>';
}
?>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/main.js"></script>
	<div class="modal hide fade" id="modalError">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Hm, c'est embarassant...</h3>
		</div>
		<div class="modal-body">
			<p class="alert alert-block alert-error">Une erreur inattendue s'est produite. KeePassPHP a généré les informations suivantes :</p>
			<pre class="well"></pre>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
		</div>
	</div>
 <?php
if($this->addSuccess && $this->isJavascript)
{
?>
	<div class="modal hide fade" id="modalSuccess">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Base de donnée ajoutée !</h3>
		</div>
		<div class="modal-body">
			<p class="alert alert-block alert-success">La base de donnée proposée a été ajoutée avec succès ; vous pouvez maintenant y accéder via l'ID et le(s) mot(s) de passe que vous avez renseignés.</p>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
		</div>
	</div>
	<script type="text/javascript">$(function(){ $("#modalSuccess").modal("show"); })</script>
<?php
}	

echo "</body></html>";

	}
}

?>