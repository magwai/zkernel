<?php

$clickheatConf = array (
  'logPath' => $_SERVER['DOCUMENT_ROOT'].'/../data/clickheat/logs/',
  'cachePath' => $_SERVER['DOCUMENT_ROOT'].'/../data/clickheat/cache/',
  'referers' => false,
  'groups' => false,
  'filesize' => 0,
  'adminLogin' => '',
  'adminPass' => '',
  'viewerLogin' => '',
  'viewerPass' => '',
  'memory' => 256,
  'step' => 5,
  'dot' => 19,
  'flush' => 40,
  'start' => 'm',
  'palette' => false,
  'heatmap' => true,
  'hideIframes' => true,
  'hideFlashes' => true,
  'yesterday' => false,
  'alpha' => 80,
  'version' => '1.9-revD',
);

@mkdir($clickheatConf['logPath'], 0777, true);
@chmod($clickheatConf['logPath'], 0777);
@mkdir($clickheatConf['cachePath'], 0777, true);
@chmod($clickheatConf['cachePath'], 0777);

$_COOKIE['language'] = 'ru';
$_COOKIE['clickheat'] = $clickheatConf['viewerLogin'].'||'.$clickheatConf['viewerPass'];
