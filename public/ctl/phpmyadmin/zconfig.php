<?php

if (@$_SESSION['_zdb']) $o = $_SESSION['_zdb'];
else {
	$o = @unserialize(base64_decode($_GET['sid']));
	if (@$o['host']) $_SESSION['_zdb'] = $o;
}
$i = 1;
$cfg['Servers'][$i]['port'] = @$o['port'];
$cfg['Servers'][$i]['host'] = @$o['host'];
$cfg['Servers'][$i]['user'] = @$o['username'];
$cfg['Servers'][$i]['password'] = @$o['password'];
$cfg['Servers'][$i]['only_db'] = array(@$o['dbname']);

?>