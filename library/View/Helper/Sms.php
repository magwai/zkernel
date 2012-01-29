<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Sms extends Zkernel_View_Helper_Override  {
	function sms($operator, $message, $phones, $config = array()) {
		if ($phones) {
			foreach ($phones as $k => $v) {
				$v = preg_replace(array(
					'/[^\d]/i'
				), array(
					''
				), $v);
				if (substr($v, 0, 1) == '8') $v = '7'.substr($v, 1);
				if (strlen($v) != 11) unset($phones[$k]);
				else $phones[$k] = $v;
			}
		}
		return $phones ? $this->$operator($message, $phones, $config) : false;
	}

	function mts($message, $phones, $config) {
		$ok = false;
		$client = new Zend_Http_Client('https://corpsms.ru/api/http/sendsms');
		$y = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('sms');
		$y = $y['mts'];
		$client->setAuth($y['login'], $y['password']);
		$client->setParameterPost(array(
			'msg' => $message
		));
		$client->setFileUpload('', 'phones', implode("\n", $phones), 'text/plain');
		$response = $client->request('POST');
		return $response->getStatus() == 200 && stripos($response->getBody(), 'error') == false;
	}
	
	function iqsms($message, $phones, $config) {
		$ok = 0;
		foreach ($phones as $phone) {
			$client = new Zend_Http_Client('http://gate.iqsms.ru/send/?phone='.rawurlencode($phone).'&text='.rawurlencode($message).(@$config['sender'] ? '&sender='.rawurlencode($config['sender']) : ''));
			$y = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('sms');
			$y = $y['iqsms'];
			$client->setAuth($y['login'], $y['password']);
			$response = $client->request('GET');
			if ($response->getStatus() == 200 && stripos($response->getBody(), 'accepted') !== false) $ok++;
		}
		return $ok;
	}
}