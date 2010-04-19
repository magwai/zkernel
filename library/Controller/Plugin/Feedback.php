<?php

class Zkernel_Controller_Plugin_Feedback extends Zend_Controller_Plugin_Abstract {
	public function dispatchLoopShutdown(Zend_Controller_Request_Abstract $request) {
		$html = '<div id="Zkernel_Feedback">Написать</div>';

		$response = $this->getResponse();
		$response->setBody(preg_replace('/(<head.*>)/i', '$1' . '<link href="/zkernel/img/feedback/main.css" media="screen" rel="stylesheet" type="text/css" />', $response->getBody()));
		$response->setBody(str_ireplace('</body>', '<div id="Zkernel_Feedback">'.$html.'</div></body>', $response->getBody()));
	}
}