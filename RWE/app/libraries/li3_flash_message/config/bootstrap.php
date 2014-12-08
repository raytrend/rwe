<?php

use lithium\storage\Session;

if (!Session::config()) {
	Session::config(array(
		'default' => array('adapter' => 'Php')
	));
}

?>