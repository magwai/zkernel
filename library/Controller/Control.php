<?php

/**
 * @zk_title   		Панель управления
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Control extends Zkernel_Controller_Action {

	function init() {
		$this->_helper->user()
			->setModels(array(
				'role' => new Default_Model_Crole(),
				'role_refer' => new Default_Model_Crolerefer(),
				'resource' => new Default_Model_Cresource(),
				'rule' => new Default_Model_Crule(),
				'rule_role' => new Default_Model_Crulerole(),
				'rule_resource' => new Default_Model_Cruleresource(),
				'user' => new Default_Model_Cuser()
			))
			->initAuth()
			->initAcl()
			->loginAuto();
	}

	public function indexAction()
    {
    	$this->view->doctype('HTML5');
        $this->_helper->layout->disableLayout();
        $this->_helper->control->configFromDb();
    }

	public function langAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$data = array(
			'no_connect' => 'Сервер не отвечает',
			'no_template' => 'Ошибка загрузки шаблона',
			'no_config' => 'Ошибка загрузки конфигурации',
			'no_lang' => 'Ошибка загрузки языка',
			'try_again' => 'Повторить попытку?',
			'no_menu' => 'Ошибка загрузки меню',
			'txt_alert_title' => 'Информация',
			'no_login' => 'Ошибка получения сведений об авторизации',
			'no_logout' => 'Ошибка выхода',
			'no_loggin' => 'Ошибка входа',
			'no_login_err' => 'Логин / пароль неверны',
			'no_sel' => 'Элемент не выбран',
			'no_response' => 'Ошибка загрузки модуля',
			'no_filter' => 'Укажите запрос',
    		'file_error' => 'Ошибка загрузки файла'
		);
		if ($data) {
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><d></d>');
			foreach ($data as $k => $v) $xml->addChild($k, $v);
			$res = $xml->asXML();
		}
	    $this->getResponse()	->setHeader('Content-Type', 'text/xml; charset=utf-8')
    							->setBody($res);
    }

	public function configAction()
    {
    	$s = new Zend_Session_Namespace();
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$data = array(
			'title' => 'Панель управления',
			'def_controller' => 'cindex',
			'def_action' => '',
			'def_param' => '',
			'controller' => $s->control['last']['controller'],
			'action' => @$s->control['last']['action'],
			'param' => htmlspecialchars($this->_helper->util()->urlAssemble('', '', @$s->control['last']['param']))
		);

		if ($data) {
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><d></d>');
			foreach ($data as $k => $v) $xml->addChild($k, $v);
			$res = $xml->asXML();
		}
	    $this->getResponse()	->setHeader('Content-Type', 'text/xml; charset=utf-8')
    							->setBody($res);
    }

	public function authAction()
    {
    	$request = $this->getRequest();
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$res = '';
		$login = 'none';
		if ($request->has('login') && $request->has('password')) {
			if ($request->getPost('login') == '' && $request->getPost('password') == '') $this->_helper->user()->logout();
			else {
				$ok = $this->_helper->user()->login($request->getPost('login'), $request->getPost('password'), true);
				if ($ok) $login = $this->_helper->user()->login;
			}
		}
		else if ($this->_helper->user()->data) $login = $this->_helper->user()->login;
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><d></d>');
		$xml->addChild('l', $login);
		$res = $xml->asXML();
	    $this->getResponse()	->setHeader('Content-Type', 'text/xml; charset=utf-8')
    							->setBody($res);
    }

	public function menuAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$res = '';
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><d></d>');
		$this->menuInner($xml);
		$res = $xml->asXML();
	    $this->getResponse()	->setHeader('Content-Type', 'text/xml; charset=utf-8')
    							->setBody($res);
    }

    public function routerAction()
    {
    	$s = new Zend_Session_Namespace();
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$s = new Zend_Session_Namespace();
		$request = $this->getRequest();
		$request->setParamSources(array('_GET'));

		$controller = $request->getPost('_controller') ? $request->getPost('_controller') : 'cindex';
		$action = $request->getPost('_action') ? $request->getPost('_action') : 'ctlshow';
		$param = @$s->control['history'][$controller] ? $s->control['history'][$controller] : array();

		$mm = new Default_Model_Cmenu();
		$menu = $mm->fetchRow(array('`controller` = ?' => (string)$controller));
		$error = '';
		if ($controller == 'cindex' || $menu) {
			if ($controller == 'cindex' || $this->_helper->user()->acl->isAllowed($this->_helper->user()->role, $menu->resource)) {
		    	if ($request->getPost()) {
			    	foreach ($request->getPost() as $k => $v) {
						if ($k != '_controller' && $k != '_action' && substr($k, 0, 1) == '_') {
							$k = substr($k, 1);
							$s->control['history'][$controller][$k] = $param[$k] = $v;
						}
					}
		    	}
		    	$this->_helper->jquery()->evalScript('c.controller = "'.$controller.'";c.action = "'.$action.'";c.param = "'.$this->_helper->util->urlAssemble('', '', $param).'";');
		    	$this->_forward(
		    		$action,
		    		$controller,
		    		'default',
		    		$param
		    	);
			}
			else $error = 'У вас нет доступа в этот раздел';
		}
		else $error = 'Контроллер не найден';

		$_SESSION['isLoggedIn'] = $this->_helper->user()->acl->isAllowed(
			$this->_helper->user()->role,
			$this->_helper->util()->getById(array(
				'model' => new Default_Model_Cresource(),
				'field' => 'id',
				'key' => 'key',
				'id' => 'file'
			))
		);

		if ($error) {
			$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
			$this->_helper->control()->config->response_type = 'jqphp';
			$this->_helper->control()->config->stop_frame = true;
			$this->_helper->control()->config->info = array($error);
			$js->addEval('c.load_menu();c.load_auth();c.go(c.cfg["def_controller"], c.cfg["def_action"], c.cfg["def_param"]);');
			$this->_helper->control()->viewControl();
		}
    }

	function menuInner($xml, $parentid = 0) {
		$res = new Default_Model_Cresource();
		$db = new Default_Model_Cmenu();
		$row = $db->fetchRow(array('id = ?' => $parentid));

		$result = $db->fetchAll(array('parentid = ?' => $parentid, '`show_it` = 1'), 'orderid');
		if ($result) {
			foreach ($result as $num => $el) {
				if (!@$el['title']) continue;
				if (!$el->resource || $this->_helper->user()->acl->isAllowed($this->_helper->user()->role, $el->resource)) {
					$e = $xml->addChild('e');
					$e->addAttribute('t', $el['title']);
					if (@$el['controller']) $e->addAttribute('c', $el['controller']);
					if (@$el['action']) $e->addAttribute('a', $el['action']);
					if (@$el['param']) $e->addAttribute('p', $el['param']);
					$this->menuInner($e, $el['id']);
				}
			}
		}
	}
}

