<?php

$zend_config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();

return array(
	'resource' => array(
		'database' => array(
			'database' => $zend_config['resources']['db']['params']['dbname'],
			'user' => $zend_config['resources']['db']['params']['username'],
			'password' => $zend_config['resources']['db']['params']['password']
		)
	),
	'user' => array(
		'salt' => '23hertem4280g3'
	),
	'js' => $zend_config['js'],
	'css' => $zend_config['css']
);