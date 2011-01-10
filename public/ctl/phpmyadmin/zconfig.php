<?php

if (@$_SESSION['_zdb']) $o = $_SESSION['_zdb'];
else {
	$o = @unserialize(base64_decode($_GET['sid']));
	if (@$o['host']) $_SESSION['_zdb'] = $o;
}
$i = 1;
$cfg['ShowServerInfo'] = 0;
$cfg['ShowPhpInfo'] = 0;
$cfg['ThemeManager'] = 0;
$cfg['ShowCreateDb'] = 0;
$cfg['DefaultTabServer'] = 'server_databases.php';
$cfg['PmaAbsoluteUri'] = 'http://'.$_SERVER['HTTP_HOST'].'/zkernel/ctl/phpmyadmin/';
$cfg['blowfish_secret'] = 'zs';
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['port'] = @$o['port'];
$cfg['Servers'][$i]['host'] = @$o['host'];
$cfg['Servers'][$i]['user'] = @$o['username'];
$cfg['Servers'][$i]['password'] = @$o['password'];
$cfg['Servers'][$i]['only_db'] = array(@$o['dbname']);

?>