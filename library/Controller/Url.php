<?php

/**
 * @zk_title   		Адреса для роутера
 * @zk_routable		0
 */
class Zkernel_Controller_Url extends Zkernel_Controller_Action {
	function ctlinit() {
		$this->_helper->control()->config->set(array(
			'field' => array(
				'url' => array(
					'title' => 'Адрес',
					'required' => true,
					'unique' => true
				),
				'controller' => array(
					'title' => 'Контроллер'
				),
				'action' => array(
					'title' => 'Действие'
				),
				'map' => array(
					'title' => 'Привязка параметров',
					'description' => 'Список переменных. Разделитель - запятая'
				),
				'orderid' => array(
					'active' => false
				)
			)
		));
	}

	function ctlc2uAction() {
		$dir = $this->getFrontController()->getControllerDirectory();
    	$dir = @$dir['default'];
    	$handle = @opendir($dir);@readdir($handle);@readdir($handle);
    	$cnt = 0;
    	$ids = array();
    	while ($path = @readdir($handle)) {
			if (is_file($dir.'/'.$path)) {
				$u = array();
				$um = array();
				$nm = strtolower(str_ireplace('Controller.php', '', $path));
				$c = ucfirst($nm).'Controller';
				if (!class_exists($c)) include $dir.'/'.$path;
				$db = $this->_helper->util()->getDocblock($c);
				if (isset($db['zk_routes'])) $u = array_merge($u, explode(' ', $db['zk_routes']));
				$r = new Zend_Reflection_Class($c);
				$met = $r->getMethods();
				if (@$met) {
		    		foreach ($met as $el) {
		    			$db = $this->_helper->util()->getDocblock($el, 'method');
		    			if (isset($db['zk_routes'])) $um[$el->name] = explode(' ', $db['zk_routes']);
		    		}
		    	}
		    	if ($u) foreach ($u as $n => $el) {
		    		$uu = explode('|', $el);
		    		if ((int)$this->_helper->control()->config->model->fetchCount(array('`url` = ?' => $uu[0]))) unset($u[$n]);
		    	}
		    	if ($um) foreach ($um as $n => $el) {
		    		foreach ($el as $n_1 => $el_1) {
		    			$uu = explode('|', $el_1);
		    			if ((int)$this->_helper->control()->config->model->fetchCount(array('`url` = ?' => $uu[0]))) unset($um[$n][$n_1]);
		    		}
		    		if (!$um[$n]) unset($um[$n]);
		    	}
		    	if ($u) {
		    		foreach ($u as $n => $el) {
		    			$mp = explode('|', $el);
		    			$ids[] = $this->_helper->control()->config->model->insert(array(
		    				'controller' => $nm,
			    			'action' => '',
			    			'url' => @$mp[0],
			    			'map' => @$mp[1],
			    			'orderid' => $cnt + 1
		    			));
		    			$cnt++;
		    		}

		    	}
				if ($um) {
					foreach ($um as $n => $el) {
		    			foreach ($el as $n_1 => $el_1) {
			    			$mp = explode('|', $el_1);
			    			$ids[] = $this->_helper->control()->config->model->insert(array(
			    				'controller' => $nm,
				    			'action' => substr($n, 0, -6),
				    			'url' => @$mp[0],
				    			'map' => @$mp[1],
				    			'orderid' => $cnt + 1
			    			));
			    			$cnt++;
		    			}
		    		}
		    	}
			}
		}
		if ($cnt) {
			$next = $this->_helper->control()->config->model->fetchAll('`id` NOT IN ('.implode(',', $ids).')', 'orderid');
    		if ($next) foreach ($next as $n => $el_1) {
    			$el_1->orderid = $cnt + $n + 1;
    			$el_1->save();
    		}
    		$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
			$js->addEval('c.go("url");');
		}
		$this->_helper->control()->config->set(array(
			'type' => 'none',
			'stop_frame' => 1,
			'info' => array(
				$cnt ? 'Загружено адресов: '.$cnt : 'Не было загружено ни одного адреса'
			)
		));
		$this->_helper->control()->routeDefault();
	}

	function ctlshowAction() {
		$this->_helper->control()->config->set(array(
			'button_top' => array('add', 'edit', 'delete', array(
				'title' => 'Адреса из контроллеров',
				'controller' => 'url',
				'action' => 'ctlc2u'
			)),
			'field' => array(
				'message' => array(
					'active' => false
				),
				'cedit' => array(
					'hidden' => true
				)
			)
		));

		$this->_helper->control()->routeDefault();
	}
}