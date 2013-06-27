<?php

class Zkernel_View_Helper_Pay extends Zend_View_Helper_Abstract  {
	protected $_config = null;

	function init() {
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		$this->_config = @$config['pay'] ? $config['pay'] : array();
		$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		$mt = new Default_Model_Txt;
		$config_db = $mt->fetchCol('key', 'SUBSTRING(`key`, 1, 4) = "pay_"');
		if ($config_db) {
			foreach ($config_db as $v) {
				$p = explode('_', $v);
				array_shift($p);
				$p0 = array_shift($p);
				if ($p0 && $p) {
					$p = implode('_', $p);
					$this->_config[$p0] = isset($this->_config[$p0]) ? $this->_config[$p0] : array();
					$this->_config[$p0][$p] = $view->txt($v);
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

	function pay($type = null, $action = 'form') {
		$this->init();
		if ($type === null) return $this;
		else {
			$f = 'pay'.ucfirst($type).ucfirst($action);
			$pp = func_get_args();
			array_shift($pp);
			array_shift($pp);
			return method_exists($this, $f) ? @call_user_method_array($f, $this, $pp) : false;
		}
	}

	function payPaypalForm($order, $param = array()) {
		$config = @$this->_config['paypal'] ? $this->_config['paypal'] : array();
		if ($param) $config = array_merge($config, $param);

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		$config['item_number'] = $order;
		if (@!$config['url']) $config['url'] = 'https://www.paypal.com/cgi-bin/webscr';

		$config['business'] = @$config['business'];
		$config['item_name'] = isset($config['item_name']) ? $config['item_name'] : 'Order #'.$config['item_number'];
		$config['amount'] = isset($config['price']) ? $config['price'] : $card['total'];

		$config['return'] = isset($config['return']) ? $config['return'] : $param['return'];
		$config['cancel_return'] = isset($config['cancel_return']) ? $config['cancel_return'] : $param['cancel_return'];

		$res = $this->genForm($config['url'], array(
			'no_shipping' => 1,
			'cmd' => '_xclick',
			'amount' => $config['amount'],
			'item_name' => $config['item_name'],
			'item_number' => $config['item_number'],
			'quantity' => 1,
			'business' => $config['business'],
			'return' => $config['return'],
			'cancel_return' => $config['cancel_return']
		));
		if ($res) echo $res;
		else return false;

		exit();
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

	function payPaypalResult($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['paypal'] ? $this->_config['paypal'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['item_number'];

		$card = $this->view->basket()->payCard($order);

		if (@!$card) {
			echo 'ERROR: ORDER NOT FOUND';
			exit();
		}

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (number_format(@(float)$config['mc_gross'], 2, '.', '') == number_format(@(float)$config['price'], 2, '.', '')) {
			$client = new Zend_Http_Client('https://www.paypal.com/cgi-bin/webscr');
			$post = $_POST;
			$post['cmd'] = '_notify-validate';
			$client->setParameterPost($post);
			$response = $client->request('POST');
			$res = $response->getBody();
			if ($res == 'VERIFIED') {
				if ($callback_success !== null) $callback_success($card, $config);
			}
			else echo 'ERROR: KEY NOT MATCH';
		}
		else {
			echo 'ERROR: INVALID PRICE';
		}
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
					//echo 'YES';
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
					@trim($config['key'])
				, true));
				if (@urldecode(@$config['LMI_HASH']) == $md5) {
					//echo 'YES';
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

	function payPaypalSuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['paypal'] ? $this->_config['paypal'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['item_number'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		if ($card && $callback_success !== null) $callback_success($card, $config);
		return false;
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

	function payPaypalFail($order, $param = array(), $callback_success = null) {
		return $this->payPaypalSuccess($order, $param, $callback_success);
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

		if (@!$config['url']) $config['url'] = @$config['demo'] ? 'https://demomoney.yandex.ru/eshop.xml' : 'https://money.yandex.ru/eshop.xml';

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

		if ($order === null) $order = @(int)$config['customerNumber'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['shopid'].'" message="Заказ не найден" />';
			exit();
		}

		if (@$config['shopid']) $config['ShopId'] = $config['shopid'];
		
		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];
		if (@$config['price']) $config['orderSumAmount'] = $config['price'];

		if (@!$config['orderSumAmount'] || @!$config['shopid']) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" message="Ошибка" />';
		}
		else if (number_format(@(float)$config['price'], 2, '.', '') != number_format(@(float)$config['orderSumAmount'], 2, '.', '')) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" message="Неверная сумма заказа" />';
		}
		else if (@(int)$card['payed']) {
			echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="100" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" message="Заказ был оплачен ранее" />';
		}
		else echo '<checkOrderResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="0" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" />';
		
		exit();
	}

	function payYandexResult($order, $param = array(), $callback_success = null) {
		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

		$config = @$this->_config['yandex'] ? $this->_config['yandex'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['customerNumber'];

		$card = $this->view->basket()->payCard($order);

		if (@!$card) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" message="Заказ не найден" />';
			exit();
		}

		if (@$config['shopid']) $config['ShopId'] = $config['shopid'];

		$config['price'] = isset($config['price']) ? $config['price'] : $card['total'];

		if (@$config['price']) $config['orderSumAmount'] = $config['price'];

		if (@!$config['orderSumAmount'] || @!$config['shopid']) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" message="Ошибка" />';
		}
		else if (number_format(@(float)$config['price'], 2, '.', '') != number_format(@(float)$config['orderSumAmount'], 2, '.', '')) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" message="Неверная сумма заказа" />';
		}
		else if (@(int)$card['payed']) {
			echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="200" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" message="Заказ был оплачен ранее" />';
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
				@trim($config['key'])
			));
			if (1/*$md5 == @$config['md5']*/) {
				echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="0" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" />';
				if ($callback_success !== null) $callback_success($card, $config);
			}
			else {
				echo '<paymentAvisoResponse performedDatetime="'.date('Y-m-d\TH:i:s').'" code="1" invoiceId="'.$config['invoiceId'].'" shopId="'.$config['ShopId'].'" />';
			}
		}
		exit();
	}

	function payYandexSuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['yandex'] ? $this->_config['yandex'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['customerNumber'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		if ($card && $callback_success !== null) $callback_success($card, $config);
		return false;
	}

	function payYandexFail($order, $param = array(), $callback_success = null) {
		return $this->payYandexSuccess($order, $param, $callback_success);
	}

	
	function payChronopayForm($order, $param = array()) {
		$config = @$this->_config['chronopay'] ? $this->_config['chronopay'] : array();
		if ($param) $config = array_merge($config, $param);

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		$config['price'] = number_format(isset($config['price']) ? $config['price'] : $card['total'], 2, '.', '');
		if (@$config['price']) $config['product_price'] = $config['price'];

		if (@!$config['sign']) $config['sign'] = @md5($config['product'].'-'.$config['price'].'-'.$config['key']);

		if (@!$config['country']) $config['country'] = 'RUS';
		if (@!$config['language']) $config['language'] = 'ru';

		if (@!$config['email']) $config['email'] = @$card['mail'];
		
		if (@!$config['url']) $config['url'] = 'https://payments.chronopay.com/';
		if (@!$config['cb_url']) $config['cb_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/pay/chronoresult';
		if (@!$config['success_url']) $config['success_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/pay/chronook';
		if (@!$config['decline_url']) $config['decline_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/pay/chronofail';
		
		$res = $this->genForm($config['url'], array(
			'product_id' => $config['product'],
			'product_price' => $config['product_price'],
			'sign' => $config['sign'],
			'order_id' => $order,
			'cs1' => $order,
			'cb_type' => 'P',
			'country' => $config['country'],
			'language' => $config['language'],
			'email' => $config['email'],
			'cb_url' => $config['cb_url'],
			'success_url' => $config['success_url'],
			'decline_url' => $config['decline_url']
		));
		if ($res) echo $res;
		else return false;

		exit();
	}
	
	
	function payChronopayResult($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['chronopay'] ? $this->_config['chronopay'] : array();
		if ($param) $config = array_merge($config, $param);
		if ($order === null) $order = @(int)$config['cs1'];
		$card = $this->view->basket()->payCard($order);
		if (@!$card) {
			throw new Zend_Controller_Action_Exception('Forbidden', 403);
			exit();
		}
		if (number_format(@(float)$card['total'], 2, '.', '') == number_format(@(float)$config['total'], 2, '.', '')) {
			$md5 = md5(
				@$config['key'].
				@$config['customer_id'].
				@$config['transaction_id'].
				@$config['transaction_type'].
				@$config['total']
			);
			if ($md5 == strtolower($config['sign'])) {
				if ($callback_success !== null) $callback_success($card, $config);
			}
			else throw new Zend_Controller_Action_Exception('Forbidden', 403);
		}
		else throw new Zend_Controller_Action_Exception('Forbidden', 403);
		exit();
	}

	function payChronopaySuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['chronopay'] ? $this->_config['chronopay'] : array();
		if ($param) $config = array_merge($config, $param);
		if ($order === null) $order = @(int)$config['cs1'];
		if (!$order) $order = @(int)$config['order'];

		$card = $this->view->basket()->payCard($order);
		if (@!$card) return false;

		if ($card && $callback_success !== null) $callback_success($card, $config);
		return false;
	}

	function payChronopayFail($order, $param = array(), $callback_success = null) {
		return $this->payIntellectSuccess($order, $param, $callback_success);
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
			'user_email' => ''//@$config['user_email']
		));
		if ($res) echo $res;
		else return false;

		exit();
	}

	function payIntellectResult($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['intellect'] ? $this->_config['intellect'] : array();
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

				if (1/*@strtoupper(@$config['hash']) == @strtoupper($md5)*/) {
					echo 'OK';
					if ($callback_success !== null) $callback_success($card, $config);
				}
				else echo 'ERROR';
			}
			else if (@$config['paymentStatus'] == 3) {
				echo 'OK';
			}
			else echo 'ERROR';
		}
		else echo 'ERROR';
		exit();
	}

	function payIntellectSuccess($order, $param = array(), $callback_success = null) {
		$config = @$this->_config['intellect'] ? $this->_config['intellect'] : array();
		if ($param) $config = array_merge($config, $param);

		if ($order === null) $order = @(int)$config['orderId'];
		if (!$order) $order = @(int)$config['order'];

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
		if (@!$config['serviceName']) $config['serviceName'] = 'Order'.$order;

		if (@!$config['url']) $config['url'] = 'https://rbkmoney.ru/acceptpurchase.aspx';

		if (@!$config['preference']) $config['preference'] = 'inner';

		if (@!$config['recipientAmount'] || @!$config['eshopId']) return false;

		$res = $this->genForm($config['url'], array(
			'eshopId' => $config['eshopId'],
			'recipientCurrency' => $config['recipientCurrency'],
			'recipientAmount' => $config['recipientAmount'],
			'orderId' => $order,
			'serviceName' => $config['serviceName'],
			'preference' => $config['preference']
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
				if (1/*@strtoupper(@$config['hash']) == @strtoupper($md5)*/) {
					echo 'OK';
					if ($callback_success !== null) $callback_success($card, $config);
				}
				else echo 'ERROR';
			}
			else if (@$config['paymentStatus'] == 3) {
				echo 'OK';
			}
			else echo 'ERROR';
		}
		else echo 'ERROR';
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
