<?php

class Zkernel_Controller_Plugin_Feedback extends Zend_Controller_Plugin_Abstract {
	const DEFAULT_REGISTRY_KEY = 'Zkernel_Feedback';
	const DEFAULT_MAIL = 'feedback@magwai.ru';

	public function __construct($options = array()) {
		$mail = isset($options['mail']) ? $options['mail'] : self::DEFAULT_MAIL;
		$key = isset($options['registry']) ? $options['registry'] : self::DEFAULT_REGISTRY_KEY;
		Zend_Registry::set($key, array(
			'mail' => $mail
		));
    }

	public function dispatchLoopShutdown(Zend_Controller_Request_Abstract $request) {
		$response = $this->getResponse();
		$response->setBody(preg_replace('/(<head.*>)/i', '$1' . '<link href="/zkernel/img/feedback/main.css" media="screen" rel="stylesheet" type="text/css" />', $response->getBody()));
		$response->setBody(str_ireplace('</body>', '<div id="Zkernel_Feedback"><div title="Написать сообщение разработчику" id="Zkernel_Feedback_Btn">!</div></div><script src="/zkernel/js/zfeedback.js" type="text/javascript"></script></body>', $response->getBody()));
	}
}