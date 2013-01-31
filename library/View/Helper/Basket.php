<?php

class Zkernel_View_Helper_Basket extends Zend_View_Helper_Abstract  {
	protected $_uid = null;
	protected $_model_item = null;
	protected $_model_order = null;
	protected $_model_order_item = null;
	protected $_model_discount = null;
	protected $_model_delivery = null;
	protected $_model_deliveryregion = null;
	protected $_model_cash = null;
	protected $_model_pay = null;
	protected $_field_order_item_id = 'itemid';

	function __construct() {
		if ($this->_model_item === null) $this->_model_item = new Default_Model_Catalogitem;
		if ($this->_model_order === null) $this->_model_order = new Default_Model_Order;
		if ($this->_model_order_item === null) $this->_model_order_item = new Default_Model_Orderitem;
		if ($this->_model_discount === null && class_exists('Default_Model_Discount')) $this->_model_discount = new Default_Model_Discount;
		if ($this->_model_delivery === null && class_exists('Default_Model_Delivery')) $this->_model_delivery = new Default_Model_Delivery;
		if ($this->_model_deliveryregion === null && class_exists('Default_Model_Deliveryregion')) $this->_model_deliveryregion = new Default_Model_Deliveryregion;
		if ($this->_model_cash === null && class_exists('Default_Model_Cash')) $this->_model_cash = new Default_Model_Cash;
		if ($this->_model_pay === null && class_exists('Default_Model_Pay')) $this->_model_pay = new Default_Model_Pay;
	}

	function setUid($uid) {
		$this->_uid = $uid;
		return $this;
	}

	function basket() {
		return $this;
	}

	function basket2Sid($uid) {
		if (!$uid) return false;
		if (!Zend_Session::isStarted()) Zend_Session::start();
		$sid = Zend_Session::getId();
		return $this->_model_order->update(array('author' => $sid), array(
			'`author` = ?' => $uid,
			'`finished` = 0',
			'`active` = 1'
		));
	}

	function basketId($create = false) {
		if ($this->_uid === null) {
			$uid = $this->view->user('id');
			if (!Zend_Session::isStarted()) Zend_Session::start();
			$sid = Zend_Session::getId();
			if ($uid) {
				$id = (int)$this->_model_order->fetchOne('id', array(
					'`author` = ?' => $sid,
					'`finished` = 0',
					'`active` = 1'
				), 'date desc');
				if ($id) {
					$this->_model_order->update(array('author' => $uid), array('`id` = ?' => $id));
					$this->_model_order->update(array('active' => 0), array(
						'`author` = ?' => $uid,
						'`finished` = 0',
						'`active` = 1',
						'`id` != ?' => $id
					));
				}
			}
			else $uid = $sid;
		}
		else $uid = $this->_uid;
		$id = (int)$this->_model_order->fetchOne('id', array(
			'`author` = ?' => $uid,
			'`finished` = 0',
			'`active` = 1'
		), 'date desc');
		if (!$id && $create) {
			$d = array(
				'author' => $uid
			);
			if (method_exists($this, 'basketDefault')) {
				$dd = $this->basketDefault();
				if ($dd) $d = array_merge($d, $dd);
			}
			$id = $this->_model_order->insert($d);
			if ($id) {
				if (method_exists($this, 'onCreate')) {
					$this->onCreate($id);
				}
			}
		}
		return $id;
	}

	function basketQuant($id = null) {
		$oid = $this->basketId();
		$s = $this->_model_order->getAdapter()->select()
			->from(array('i' => $this->_model_order->info('name')), '')
			->joinLeft(array('m' => $this->_model_order_item->info('name')), 'i.id = m.parentid', array(
				'quant' => 'SUM(m.quant)'
			))
			->where('i.id = ?', $oid)
			->group('i.id');
		if ($id != null) $s->where('m.'.$this->_field_order_item_id.' = ?', $id);
		$ret = (int)$this->_model_order->getAdapter()->fetchOne($s, 'SUM(`quant`)');
		return $ret;
	}

	function basketPriceClean($id = null) {
		$oid = $this->basketId();
		$s = $this->_model_order->getAdapter()->select()
			->from(array('i' => $this->_model_order->info('name')), '')
			->joinLeft(array('m' => $this->_model_order_item->info('name')), 'i.id = m.parentid', array(
				'price' => 'SUM(m.price * m.quant)'
			))
			->where('i.id = ?', $oid)
			->group('i.id');
		if ($id != null) $s->where('m.'.$this->_field_order_item_id.' = ?', $id);
		$ret = (float)$this->_model_order->getAdapter()->fetchOne($s, 'SUM(`price`)');
		return $ret;
	}

	function basketPrice($id = null) {
		$oid = $this->basketId();
		$ret_original = $this->basketPriceClean($id);
		$ret = $ret_original;
		if (!$id) {
			$ret += $this->fetchDelivery($oid, $ret_original);
			$ret += $this->fetchDeliveryregion($oid, $ret_original);
			$ret += $this->fetchPay($oid, $ret_original);
			$ret -= $this->fetchCash($oid);
		}
		return $ret;
	}

	function getPercent($v, $total = 1) {
		if (strpos($v, '%') !== false) $v = substr($v, 0, -1) * $total / 100;
		return is_numeric($v)
			? (is_float($v) ? (float)$v : (int)$v)
			: (string)$v;
	}

	function fetchCash($oid, $check_amount = true) {
		$ret = 0;
		if ($this->_model_cash && $this->view->user('id')) {
			$card = $this->_model_order->fetchRow(array(
				'`id` = ?' => $oid
			));
			if ($card && $card->cash) {
				if ($check_amount) {
					$cash = $this->_model_cash->fetchAmountForBasket($this->view->user('id'), $card->id);
					if ($cash && $card->cash) {
						$ret = min($card->cash, $cash);
						if ($ret != $card->cash) {
							$this->basketSave(array(
								'cash' => $ret
							));
						}
					}
				}
				else $ret = $card->cash;
			}
		}
		return $ret;
	}

	function fetchDelivery($oid, $price, $original = false, $edit_mode = false) {
		$ret = 0;
		if ($this->_model_delivery) {
			$card = $this->_model_order->fetchRow(array(
				'`id` = ?' => $oid
			));
			if ($card) {
				$card = new Zkernel_View_Data($card);
				if ($card->price_delivery && !$edit_mode) return $card->price_delivery;
				$delivery = $this->_model_delivery->fetchRow(array(
					'`id` = ?' => (int)$card->delivery
				));
				if ($delivery && $delivery->price) {
					if ($original) $ret = $delivery->price;
					else {
						$delivery_price_from = isset($delivery->price_from) ? $this->getPercent($delivery->price_from, $price) : '';
						if (!$delivery_price_from || $delivery_price_from > $price) $ret = $this->getPercent($delivery->price, $price);
						$ret += $this->discountPayDelivery($card->pay, $price, $ret);
					}
				}
			}
		}
		return $ret;
	}

	function fetchDeliveryregion($oid, $price, $original = false, $edit_mode = false) {
		$ret = 0;
		if ($this->_model_deliveryregion) {
			$card = $this->_model_order->fetchRow(array(
				'`id` = ?' => $oid
			));
			if ($card) {
				$card = new Zkernel_View_Data($card);
				if ($card->price_deliveryregion && !$edit_mode) return $card->price_deliveryregion;
				$delivery = $this->_model_deliveryregion->fetchRow(array(
					'`id` = ?' => (int)$card->region
				));
				if ($delivery && $delivery->price) {
					if ($original) $ret = $delivery->price;
					else {
						$delivery_price_from = isset($delivery->price_from) ? $this->getPercent($delivery->price_from, $price) : '';
						if (!$delivery_price_from || $delivery_price_from > $price) $ret = $this->getPercent($delivery->price, $price);
						$ret += $this->discountPayDelivery($card->pay, $price, $ret);
					}
				}
			}
		}
		return $ret;
	}

	function discountPayDelivery($pay_id, $price, $delivery) {
		$ret = 0;
		if ($delivery) {
			if ($this->_model_pay) {
				$pay = $this->_model_pay->fetchRow(array(
					'`id` = ?' => (int)$pay_id
				));
				if ($pay) {
					$pay = new Zkernel_View_Data($pay);
					if ($pay->price_delivery) {
						$ret += $this->getPercent($pay->price_delivery, $delivery);
					}
					if ($pay->price_delivery_item) {
						$ret += $this->getPercent($pay->price_delivery_item, $price);
					}
				}
			}
		}
		return $ret;
	}

	function fetchPay($oid, $price, $original = false, $edit_mode = false) {
		$ret = 0;
		if ($this->_model_pay) {
			$card = $this->_model_order->fetchRow(array(
				'`id` = ?' => $oid
			));
			if ($card) {
				$card = new Zkernel_View_Data($card);
				if ($card->price_pay && !$edit_mode) return $card->price_pay;
				$pay = $this->_model_pay->fetchRow(array(
					'`id` = ?' => (int)$card->pay
				));
				if ($pay) {
					$pay = new Zkernel_View_Data($pay);
					if ($pay->price) {
						if ($original) $ret = $pay->price;
						else {
							$pay_price_from = isset($pay->price_from) ? $this->getPercent($pay->price_from, $price) : 0;
							if (!$pay_price_from || $pay_price_from > $price) $ret = $this->getPercent($pay->price, $price);
						}
					}
				}
			}
		}
		return $ret;
	}

	function basketAdd($id, $quant = 1, $ext = array()) {
		$oid = $this->basketId(true);
		$item = $this->_model_item->fetchBasketCard($id);
		$ex = $this->_model_order_item->fetchRow(array(
			'`parentid` = ?' => $oid,
			'`'.$this->_field_order_item_id.'` = ?' => $id
		));
		if ($ex) {
			$d = array_merge(array(
				'quant' => $ex->quant + $quant
			), $ext);
			$ok = $this->_model_order_item->update($d, array(
				'`id` = ?' => $ex->id
			));
		}
		else {
			$d = array(
				'parentid' => $oid,
				'quant' => $quant,
				$this->_field_order_item_id => $id,
				'price' => $item->price,
				'orderid' => (int)$this->_model_order_item->fetchMax('orderid') + 1
			);
			if (method_exists($this, 'basketItemDefault')) {
				$dd = $this->basketItemDefault();
				if ($dd) $d = array_merge($d, $dd);
			}
			$d = array_merge($d, $ext);
			$ok = $this->_model_order_item->insert($d);
		}
		return $ok;
	}

	function basketSet($id, $quant, $ext = array()) {
		$oid = $this->basketId(true);
		$item = $this->_model_item->fetchBasketCard($id);
		if (!$item || !$item->price || !$quant) return false;
		$ex = $this->_model_order_item->fetchRow(array(
			'`parentid` = ?' => $oid,
			'`'.$this->_field_order_item_id.'` = ?' => $id
		));
		if ($ex) {
			$d = array_merge(array(
				'quant' => $quant
			), $ext);
			$ok = $this->_model_order_item->update($d, array(
				'`id` = ?' => $ex->id
			));
		}
		else {
			$d = array(
				'parentid' => $oid,
				'quant' => $quant,
				$this->_field_order_item_id => $id,
				'price' => $item->price,
				'orderid' => (int)$this->_model_order_item->fetchMax('orderid') + 1
			);
			if (method_exists($this, 'basketItemDefault')) {
				$dd = $this->basketItemDefault();
				if ($dd) $d = array_merge($d, $dd);
			}
			$d = array_merge($d, $ext);
			$ok = $this->_model_order_item->insert($d);
		}
		return $ok;
	}

	function basketRemove($id, $quant = 1) {
		$oid = $this->basketId(true);
		$item = $this->_model_item->fetchBasketCard($id);
		if (!$item || !$item->price || !$quant) return false;
		$ex = $this->_model_order_item->fetchRow(array(
			'`parentid` = ?' => $oid,
			'`'.$this->_field_order_item_id.'` = ?' => $id
		));
		if (!$ex) return false;
		$q = $ex->quant - $quant;
		if ($q <= 0) $ok = $this->_model_order_item->delete(array(
			'`id` = ?' => $ex->id
		));
		else $ok = $this->_model_order_item->update(array(
			'quant' => $q
		), array(
			'`id` = ?' => $ex->id
		));
		return $ok;
	}

	function basketDelete($id) {
		$oid = $this->basketId();
		if (!$oid) return false;
		$ok = $this->_model_order_item->delete(array(
			'`parentid` = ?' => $oid,
			'`'.$this->_field_order_item_id.'` = ?' => $id
		));
		return $ok;
	}

	function basketClear() {
		$oid = $this->basketId();
		if (!$oid) return false;
		$ok = $this->_model_order_item->delete(array(
			'`parentid` = ?' => $oid
		));
		return $ok;
	}

	function basketList() {
		$oid = $this->basketId();
		$list = $this->_model_order_item->fetchAll(array('`parentid` = ?' => $oid), 'orderid');
		$ret = array();
		if ($list) {
			foreach ($list as $el) {
				$d = new Zkernel_View_Data($el);
				$item = $this->_model_item->fetchBasketCard($el->{$this->_field_order_item_id});
				$ret[] = new Zkernel_View_Data(array_merge($d->toArray(), $item->toArray()));
			}
		}
		return $ret;
	}

	function basketIds() {
		$oid = $this->basketId();
		return $this->_model_order_item->fetchCol($this->_field_order_item_id, array('`parentid` = ?' => $oid), 'orderid');
	}

	function basketCard() {
		$oid = $this->basketId();
		$res = $this->_model_order->fetchRow(array('`id` = ?' => (int)$oid));
		return $res ? new Zkernel_View_Data($res) : null;
	}

	function basketFinish($data = array()) {
		$data['finished'] = 1;
		$oid = $this->basketSave($data);
		$clean = $this->finishedPriceClean($oid);
		$data = array();
		$meta = $this->_model_order->info('metadata');
		if (isset($meta->price_delivery)) $data['price_delivery'] = $this->fetchDelivery($oid, $clean);
		if (isset($meta->price_pay)) $data['price_pay'] = $this->fetchPay($oid, $clean);
		if ($data) $this->finishedSave($oid, $data);
		return $oid;
	}

	function basketSave($data) {
		$oid = $this->basketId();
		if (!$oid) return false;

		$cash_changed = false;
		if ($this->_model_cash && $this->view->user('id') && isset($data['cash'])) {
			if ($data['cash'] < 0) $data['cash'] = 0;
			$price_clean = $this->basketPriceClean();
			if ($data['cash'] > $price_clean) $data['cash'] = $price_clean;

			$card = $this->_model_order->fetchRow(array(
				'`id` = ?' => $oid
			));
			if ($card && @$data['cash'] != $card->cash) {
				$cash = $this->_model_cash->fetchAmountForBasket($this->view->user('id'), $oid);
				if ($cash) {
					if (@$data['cash'] > $cash) return false;
					else $cash_changed = true;
				}
			}
		}
		$ok = $this->_model_order->update($data, array('`id` = ?' => $oid));
		if ($ok && $cash_changed) $this->_model_cash->saveCashForBasket($this->view->user('id'), $oid, @$data['cash']);
		return $ok ? $oid : false;
	}

	function basketDiscount($uid = null) {
		$uid = $uid ? $uid : $this->view->user('id');
		return $uid ? $this->_model_discount->fetchBasketDiscount((int)$this->finishedPrice(null, null, $uid)) : 0;
	}

	function finishedCard($oid) {
		$res = $this->_model_order->fetchRow(array('`id` = ?' => (int)$oid, '`finished` = ?' => 1));
		return $res ? new Zkernel_View_Data($res) : null;
	}

	function finishedQuant($oid = null, $id = null, $uid = null, $payed = null) {
		$s = $this->_model_order->getAdapter()->select()
			->from(array('i' => $this->_model_order->info('name')), '')
			->joinLeft(array('m' => $this->_model_order_item->info('name')), 'i.id = m.parentid', array(
				'quant' => 'SUM(m.quant)'
			))
			->where('i.active = ?', 1)
			->where('i.finished = ?', 1)
			->group('i.id');

		if ($payed != null) $s->where('i.payed = ?', (int)$payed);
		if ($uid != null) $s->where('i.author = ?', $uid);
		if ($oid != null) $s->where('i.id = ?', $oid);
		if ($id != null) $s->where('m.id = ?', $id);

		$ret = (int)$this->_model_order->getAdapter()->fetchOne($s, 'SUM(`quant`)');
		return $ret;
	}

	function finishedPriceClean($oid = null, $id = null, $uid = null, $payed = null) {
		$meta = $this->_model_order_item->info('metadata');
		$s = $this->_model_order->getAdapter()->select()
			->from(array('i' => $this->_model_order->info('name')), '')
			->joinLeft(array('m' => $this->_model_order_item->info('name')), 'i.id = m.parentid', array(
				'price' => 'SUM(m.price * m.quant)'
			))
			->where('i.active = ?', 1)
			->where('i.finished = ?', 1)
			->group('i.id');
		if (isset($meta['calc_it'])) $s->where('m.calc_it = ?', 1);
		if ($payed != null) $s->where('i.payed = ?', (int)$payed);
		if ($uid != null) $s->where('i.author = ?', $uid);
		if ($oid != null) $s->where('i.id = ?', $oid);
		if ($id != null) $s->where('m.id = ?', $id);
		$ret = (float)$this->_model_order->getAdapter()->fetchOne($s, 'SUM(`price`)');
		return $ret;
	}

	function finishedPrice($oid = null, $id = null, $uid = null, $payed = null) {
		$ret = $this->finishedPriceClean($oid, $id, $uid, $payed);
		if ($oid) $ret += $this->fetchDelivery($oid, $ret);
		if ($oid) $ret += $this->fetchPay($oid, $ret);
		if ($oid) $ret -= $this->fetchCash($oid, false);
		return $ret;
	}

	function finishedList($oid) {
		$list = $this->_model_order_item->fetchAll(array('`parentid` = ?' => $oid), 'orderid');
		$ret = array();
		if ($list) {
			foreach ($list as $el) {
				$d = new Zkernel_View_Data($el);
				$item = $this->_model_item->fetchBasketCard($el->{$this->_field_order_item_id});
				if ($item) {
					$item = $item->toArray();
					unset($item['id']);
				}
				$ret[] = $item ? new Zkernel_View_Data(array_merge($item, $d->toArray())) : new Zkernel_View_Data(array_merge($d->toArray()));
			}
		}
		return $ret;
	}

	function finishedSave($oid, $data) {
		$cash_changed = false;
		if ($this->_model_cash && $this->view->user('id') && isset($data['cash'])) {
			if ($data['cash'] < 0) $data['cash'] = 0;
			$price_clean = $this->finishedPriceClean($oid);
			if ($data['cash'] > $price_clean) $data['cash'] = $price_clean;

			$card = $this->_model_order->fetchRow(array(
				'`id` = ?' => $oid
			));

			if ($card && $data['cash'] != $card->cash) {
				$cash = $this->_model_cash->fetchAmountForBasket($this->view->user('id'), $oid);
				if ($cash) {
					if ($data['cash'] > $cash) return false;
					else $cash_changed = true;
				}
			}
		}

		$ok = $this->_model_order->update($data, array('`id` = ?' => $oid, '`finished` = ?' => 1));
		if ($ok && $cash_changed) $this->_model_cash->saveCashForBasket($this->view->user('id'), $oid, $data['cash']);
		return $ok ? $oid : false;
	}

	function payCard($oid) {
		$ret = $this->finishedCard($oid);
		if ($ret) {
			$ret->total = $this->finishedPrice($ret->id);
		}
		return $ret;
	}
}
