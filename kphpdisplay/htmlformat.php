<?php

/**
 * Description of AjaxFormatter
 *
 * @author Louis
 */

abstract class HTMLFormat
{
	const ENTRIES_HEAD = '<thead><tr><th> </th><th>Titre</th><th>URL</th><th>Nom d\'utilisateur</th><th>Mot de passe</th></tr></thead>';
	const DEFAULT_LINK_LIMIT = 20;
	const PASSWORD_NOT_FOUND = '<span class="label label-important">Non trouvé !</span>';

	static public function formatPassword($pwd)
	{
		return '<input type="text" class="verysmall selectOnFocus"' .
			' value="' . Display::makePrintable($pwd). '" style="font-size:3px !important;"/>';
	}
	
	static public function formatEntries(Database $db, $llimit = self::DEFAULT_LINK_LIMIT)
	{
		$s = '<table class="table table-hover form-inline">' . self::ENTRIES_HEAD . '<tbody>';
		foreach($db->getEntries() as $uuid => $e)
		{
			$icon = $db->getIconSrc($e[Database::KEY_CUSTOMICON]);
			$s.= '<tr><td>' . ($icon == null ? '' : '<img src="' .
				Display::makePrintable($icon) . '" />') . '</td>';
			$s.= '<td>' . Display::makePrintable($e[Database::KEY_TITLE]) . '</td>';
			$s.= '<td>'. self::formatLink($e[Database::KEY_URL], $llimit) . '</td>';
			$s.= '<td><input type="text" class="span3 selectOnFocus" value="' .
				Display::makePrintable($e[Database::KEY_USERNAME]) . '" /></td>';
			$s.= '<td id="pwd_'.$uuid.'"><button type="button" class="btn btn-primary"'.
				' onclick="loadPassword(\'' . $uuid . '\');" autocomplete="off"'.
				' data-loading-text="...">Load</button></td></tr>';
		}
		return $s . '</tbody></table>';
	}

	static public function formatLink($l, $limit)
	{
		$b = substr($l, 0, 7);
		$p = '<span title="';
		$r = $l;
		$a = '</span>';
		if($b == "http://" || ($b == "https:/" && strlen($l) > 7 && $l[7] == '/'))
		{
			$p = '<a href="';
			$a = '</a>';
			$r = substr($l, $b == "http://" ? 7 : 8);
		}        
		return $p . Display::makePrintable($l) . '">' . Display::makePrintable(
			strlen($r) > $limit ? substr($r, 0, $limit-3)."...":$r) . $a;
	}
}

?>
