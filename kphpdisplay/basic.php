<?php

function getString($name, $array, $default = "")
{
	return isset($array[$name]) ? $array[$name] : $default;
}

?>
