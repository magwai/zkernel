<?php

class Zkernel_View_Helper_Pay extends Zend_View_Helper_Abstract  {
	protected $_config = null;

	function init() {
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		$this->_config = @$config['pay'] ? $config['pay'] : array();
		$mt = new Default_Model_Txt;
		$config_db = $mt->fetchPairs('key', 'value', 'SUBSTRING(`key`, 1, 4) = "pay_"');
		if ($config_db) {
			foreach ($config_db as $k => $v) {
				$p = explode('_', $k);
				array_shift($p);
				$p0 = array_shift($p);
				if ($p0 && $p) {
					$p = implode('_', $p);
					$this->_config[$p0] = isset($this->_config[$p0]) ? $this->_config[$p0] : array();
					$this->_config[$p0][$p] = $v;
				}
			}
		}
	}
	
	function genForm($url, $data) {
		$res = '';
		if ($data) {
			foreach ($data as $k => $v) $res .= '<input type="hidden" name="'.$k.'" value="'.$this->view->escape($v).'">';
		}
		return $res ? '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body onload="document.frm.submit();return false;">
		<form name="frm" action="'.$url.'" method="post" name="form" >'.$res.'</form>
	</body>
</html>' : '';
	}

	function pay($type = null, $action = 'form', $param = array()) {
		$this->init();
		if ($type === null) return $this;
		else {
			$f = 'pay'.ucfirst($type).ucfirst($action);
			return method_exists($this, $f) ? $this->$f($param) : false;
		}
	}

	function payWebmoneyForm($order, $param = array()) {
		$config = @$this->_config['webmoney'] ? $this->_config['webmoney'] : array();
		if ($param) $config = array_merge($config, $param);

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		$config['LMI_PAYMENT_NO'] = $order;

		if (@$config['merchantid']) $config['LMI_MERCHANT_ID'] = $config['merchantid'];
		if (@$config['description']) $config['LMI_PAYMENT_DESC'] = $config['description'];

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['LMI_PAYMENT_AMOUNT'] = $config['price'];

		if (@!$config['LMI_CURRENCY']) $config['LMI_CURRENCY'] = 'RUR';
		if (@!$config['url']) $config['url'] = 'https://paymaster.ru/Payment/Init';
		if (@!$config['LMI_PAYMENT_DESC']) $config['LMI_PAYMENT_DESC'] = 'Заказ №'.$order;

		if (@!$config['LMI_MERCHANT_ID'] || !@$config['LMI_PAYMENT_AMOUNT']) return false;

		$res = $this->genForm($config['url'], array(
			'LMI_MERCHANT_ID' => $config['LMI_MERCHANT_ID'],
			'LMI_CURRENCY' => $config['LMI_CURRENCY'],
			'LMI_PAYMENT_AMOUNT' => $config['LMI_PAYMENT_AMOUNT'],
			'LMI_PAYMENT_DESC' => $config['LMI_PAYMENT_DESC'],
			'LMI_PAYMENT_NO' => $config['LMI_PAYMENT_NO']
		));
		if ($res) echo $res;
		else return false;

		exit();
	}

	function payWebmoneyResult($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['webmoney'] ? $this->_config['webmoney'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['LMI_PAYMENT_NO'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) {
			echo 'ERROR: ORDER NOT FOUND';
			exit();
		}

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];

		if (number_format(@(float)$config['LMI_PAYMENT_AMOUNT'], 2, '.', '') == number_format(@(float)$config['price'], 2, '.', '')) {
			if ((@(int)$config['LMI_PREREQUEST'] == 1) || (@(int)$config['LMI_PREREQUEST'] == 2)) {
				if (@(int)$card['payed']) {
					echo 'ERROR: ORDER PREVIOUSLY PAID';
				}
				else {
					echo 'YES';
				}
			}
			else {
				$md5 = base64_encode(md5(
					@$config['LMI_MERCHANT_ID'].';'.
					@$config['LMI_PAYMENT_NO'].';'.
					@$config['LMI_SYS_PAYMENT_ID'].';'.
					@urldecode(@$config['LMI_SYS_PAYMENT_DATE']).';'.
					@$config['LMI_PAYMENT_AMOUNT'].';'.
					@$config['LMI_CURRENCY'].';'.
					@$config['LMI_PAID_AMOUNT'].';'.
					@$config['LMI_PAID_CURRENCY'].';'.
					@$config['LMI_PAYMENT_SYSTEM'].';'.
					@$config['LMI_SIM_MODE'].';'.
					@$config['key']
				, true));
				if (@urldecode(@$config['LMI_HASH']) == $md5) {
					echo 'YES';
					if ($callback_success !== null) $callback_success($card, $config);
				}
				else echo 'ERROR: KEY NOT MATCH';
			}
		}
		else {
			echo 'ERROR: INVALID PRICE';
		}
		exit();
	}

	function payWebmoneySuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['webmoney'] ? $this->_config['webmoney'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['LMI_PAYMENT_NO'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;
		
		if ($card && $callback_success !== null) $callback_success($card, $config);
		return false;
	}

	function payWebmoneyFail($order, $param = array(), $callback_success = null) {
		return $this->payWebmoneySuccess($order, $param, $callback_success);
	}

	function payYandexForm($order, $param = array()) {
		$config = @$this->_config['yandex'] ? $this->_config['yandex'] : array();
		if ($param) $config = array_merge($config, $param);

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['Sum'] = $config['price'];

		if (@$config['shopid']) $config['ShopId'] = $config['shopid'];

		if (@!$config['url']) $config['url'] = 'https://money.yandex.ru/eshop.xml';

		if (@!$config['Sum'] || @!$config['scid'] || @!$config['shopid']) return false;

		$res = $this->genForm($config['url'], array(
			'scid' => $config['scid'],
			'ShopId' => $config['ShopId'],
			'Sum' => $config['Sum'],
			'CustomerNumber' => $order
		));
		if ($res) echo $res;
		else return false;

		exit();
	}

	function payYandexCheck($order, $param = array()) {
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

		$config = @$this->_config['yandex'] ? $this->_config['yandex'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['orderId'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$order.'" shopId="'.$config['shopid'].'" message="Заказ не найден" />';
			exit();
		}

		if (@$config['shopid']) $config['ShopId'] = $config['shopid'];

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['orderSumAmount'] = $config['price'];

		if (@!$config['orderSumAmount'] || @!$config['shopid']) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" message="Ошибка" />';
		}
		else if (number_format(@(float)$config['price'], 2, '.', '') != number_format(@(float)$config['orderSumAmount'], 2, '.', '')) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" message="Неверная сумма заказа" />';
		}
		else if (@(int)$card['payed']) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" message="Заказ был оплачен ранее" />';
		}
		else echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="0" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" />';

		exit();
	}

	function payYandexResult($order, $param = array(), $callback_success = null) {
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

		$config = @$this->_config['yandex'] ? $this->_config['yandex'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['orderId'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$order.'" shopId="'.$config['shopid'].'" message="Заказ не найден" />';
			exit();
		}

		if (@$config['shopid']) $config['ShopId'] = $config['shopid'];

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['orderSumAmount'] = $config['price'];

		if (@!$config['orderSumAmount'] || @!$config['shopid']) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" message="Ошибка" />';
		}
		else if (number_format(@(float)$config['price'], 2, '.', '') != number_format(@(float)$config['orderSumAmount'], 2, '.', '')) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" message="Неверная сумма заказа" />';
		}
		else if (@(int)$card['payed']) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" message="Заказ был оплачен ранее" />';
		}
		else {
			$md5 = strtoupper(md5(
				@$config['action'].';'.
				@$config['orderSumAmount'].';'.
				@$config['orderSumCurrencyPaycash'].';'.
				@$config['orderSumBankPaycash'].';'.
				@$config['shopId'].';'.
				@$config['invoiceId'].';'.
				@$config['customerNumber'].';'.
				@$config('key')
			));
			if ($md5 == @$config['md5']) {
				echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="0" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" />';
				if ($callback_success !== null) $callback_success($card, $config);
			}
			else {
				echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="1" invoiceId="'.$order.'" shopId="'.$config['ShopId'].'" />';
			}
		}
		exit();
	}

	function payYandexSuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['yandex'] ? $this->_config['yandex'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['orderId'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		if ($card && $callback_success !== null) $callback_success($card, $config);
		return false;
	}

	function payYandexFail($order, $param = array(), $callback_success = null) {
		return $this->payYandexSuccess($order, $param, $callback_success);
	}

	function payIntellectForm($order, $param = array()) {
		$config = @$this->_config['intellect'] ? $this->_config['intellect'] : array();
		if ($param) $config = array_merge($config, $param);

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['recipientAmount'] = $config['price'];

		if (@$config['mail']) $config['user_mail'] = $config['mail'];
		if (@$config['id']) $config['eshopId'] = $config['id'];
		if (@$config['description']) $config['serviceName'] = $config['description'];

		if (@!$config['recipientCurrency']) $config['recipientCurrency'] = 'RUR';
		if (@!$config['serviceName']) $config['serviceName'] = 'Заказ №'.$order;

		if (@!$config['user_email']) $config['user_email'] = @$card['mail'];
		if (@!$config['url']) $config['url'] = 'https://merchant.intellectmoney.ru/ru/';
		if (@!$config['url_success']) $config['successUrl'] = 'http://'.$_SERVER['HTTP_HOST'].'/pay/irok?order='.$order;
		if (@!$config['url_fail']) $config['failUrl'] = 'http://'.$_SERVER['HTTP_HOST'].'/pay/irfail?order='.$order;

		if (@!$config['recipientAmount'] || @!$config['eshopId']) return false;

		$res = $this->genForm($config['url'], array(
			'eshopId' => $config['eshopId'],
			'successUrl' => $config['successUrl'],
			'failUrl' => $config['failUrl'],
			'recipientCurrency' => $config['recipientCurrency'],
			'recipientAmount' => $config['recipientAmount'],
			'orderId' => $order,
			'serviceName' => $config['serviceName'],
			'user_email' => @$config['user_email']
		));
		if ($res) echo $res;
		else return false;

		exit();
	}

	function payIntellectResult($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['yandex'] ? $this->_config['yandex'] : array();
		if ($param) $config = array_merge($config, $param);
		
		if ($order === null) $order = @(int)$config['orderId'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) {
			echo 'ERROR';
			exit();
		}

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['recipientAmount'] = $config['price'];

		if (number_format(@(float)$config['recipientAmount'], 2, '.', '') == number_format(@(float)$config['price'], 2, '.', '')) {
			if (@$config['paymentStatus'] == 5) {
				$md5 = md5(
					@$config['eshopId'].'::'.
					@$config['orderId'].'::'.
					@$config['serviceName'].'::'.
					@$config['eshopAccount'].'::'.
					@$config['recipientAmount'].'::'.
					@$config['recipientCurrency'].'::'.
					@$config['paymentStatus'].'::'.
					@$config['userName'].'::'.
					@$config['userEmail'].'::'.
					@$config['paymentData'].'::'.
					@$config['key']
				);
				if (@strtoupper(@$config['hash']) == @strtoupper($md5)) {
					echo 'OK';
					if ($callback_success !== null) $callback_success($card, $config);
				}
			}
		}
		echo 'ERROR';
		exit();
	}

	function payIntellectSuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['intellect'] ? $this->_config['intellect'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['orderId'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		if ($card && $callback_success !== null) $callback_success($card, $config);
		return false;
	}

	function payIntellectFail($order, $param = array(), $callback_success = null) {
		return $this->payIntellectSuccess($order, $param, $callback_success);
	}

	function payRbkForm($order, $param = array()) {
		$config = @$this->_config['rbk'] ? $this->_config['rbk'] : array();
		if ($param) $config = array_merge($config, $param);

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['recipientAmount'] = $config['price'];

		if (@$config['id']) $config['eshopId'] = $config['id'];
		if (@$config['description']) $config['serviceName'] = $config['description'];

		if (@!$config['recipientCurrency']) $config['recipientCurrency'] = 'RUR';
		if (@!$config['serviceName']) $config['serviceName'] = 'Заказ №'.$order;

		if (@!$config['url']) $config['url'] = 'https://rbkmoney.ru/acceptpurchase.aspx';

		if (@!$config['recipientAmount'] || @!$config['eshopId']) return false;

		$res = $this->genForm($config['url'], array(
			'eshopId' => $config['eshopId'],
			'recipientCurrency' => $config['recipientCurrency'],
			'recipientAmount' => $config['recipientAmount'],
			'orderId' => $order,
			'serviceName' => $config['serviceName']
		));
		if ($res) echo $res;
		else return false;

		exit();
	}

	function payRbkResult($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['rbk'] ? $this->_config['rbk'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['orderId'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) {
			echo 'ERROR';
			exit();
		}

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['recipientAmount'] = $config['price'];

		if (number_format(@(float)$config['recipientAmount'], 2, '.', '') == number_format(@(float)$config['price'], 2, '.', '')) {
			if (@$config['paymentStatus'] == 5) {
				$md5 = md5(
					@$config['eshopId'].'::'.
					@$config['orderId'].'::'.
					@$config['serviceName'].'::'.
					@$config['eshopAccount'].'::'.
					@$config['recipientAmount'].'::'.
					@$config['recipientCurrency'].'::'.
					@$config['paymentStatus'].'::'.
					@$config['userName'].'::'.
					@$config['userEmail'].'::'.
					@$config['paymentData'].'::'.
					@$config['key']
				);
				if (@strtoupper(@$config['hash']) == @strtoupper($md5)) {
					echo 'OK';
					if ($callback_success !== null) $callback_success($card, $config);
				}
			}
		}
		echo 'ERROR';
		exit();
	}

	function payRbkSuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['rbk'] ? $this->_config['rbk'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['orderId'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		if ($card && $callback_success !== null) $callback_success($card, $config);
		return false;
	}

	function payRbkFail($order, $param = array(), $callback_success = null) {
		return $this->payRbkSuccess($order, $param, $callback_success);
	}
}
