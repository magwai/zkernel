<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @zk_title   		Ошибка
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Error extends Zkernel_Controller_Action {
	public function errorAction() {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
		$this->view->errors = 'development' == APPLICATION_ENV ? '
<div style="border:1px solid #888888;width:100%;overflow:auto;background:#f1f1f1;font-size:12px;line-height:14px;"><div style="padding:20px;">
	<p><h3>Message:</h3><pre>'.$errors->exception->getMessage().'</pre></p>
	<p><h3>Stack:</h3><pre>'.$errors->exception->getTraceAsString().'</pre></p>
	<p><h3>Request:</h3><pre>'.var_export($errors->request->getParams(), true).'</pre></p>
</div></div>' : '';

		$this->getResponse()->setHeader('zk_error',
			$errors->exception->getMessage()
		);

    }
}

