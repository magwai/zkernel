<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Twitter {
    public $_name = 'default';
    public $_service;
    public $_twitter;

    function __construct($options = array()) {
    	$this->_twitter = Zend_Registry::get('Zkernel_Twitter');
    	if (isset($this->_twitter[$this->_name]['service'])) $this->_service = $this->_twitter[$this->_name]['service'];
		else {
			$user = isset($options['login']) ? $options['login'] : $this->_twitter['login'];
    		$pass = isset($options['password']) ? $options['password'] : $this->_twitter['password'];
			$this->_service = new Zend_Service_Twitter(array(
				$user,
				$pass
			));
			$this->_twitter[$this->_name]['service'] = $this->_service;
			Zend_Registry::set('Zkernel_Twitter', $this->_twitter);
		}
	}

	function _fetchStatuses() {
		$res = array();
		if (isset($this->_twitter[$this->_name]['status_list'])) $statuses = $this->_twitter[$this->_name]['status_list'];
		else {
			$statuses = $this->_service->status->userTimeline();
			$this->_twitter[$this->_name]['status_list'] = $statuses;
			Zend_Registry::set('Zkernel_Twitter', $this->_twitter);
		}
		if ($statuses && count($statuses) > 0) {
			foreach ($statuses as $el) {
				$d = $this->_parseStatus($el);
				$res[] = $d;
				$this->_twitter[$this->_name]['status_card'][$d->id] = $d;
			}
		}
		return $res ? new Zkernel_View_Data($res) : array();
	}

	function _parseStatus($el) {
		return array(
			'id' => (string)$el->id,
			'date' => date('Y-m-d H:i:s', strtotime((string)$el->created_at)),
			'message' => (string)$el->text
		);
	}

	function _update($msg) {
		$this->_service->status->update($msg);
	}
}
