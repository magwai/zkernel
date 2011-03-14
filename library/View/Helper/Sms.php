<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Sms extends Zkernel_View_Helper_Override  {
	function sms($message, $phones) {
		$ok = false;

		$client = new Zend_Http_Client('https://corpsms.ru/api/http/sendsms');
		$client->setAuth('TopFlo', 'fruit');
		$client->setParameterPost(array(
			'msg' => $message
		));
		$client->setFileUpload('', 'file', implode("\n", $phones));
		$response = $client->request('POST');
		print_r($response);exit();


		return $ok;
	}
}