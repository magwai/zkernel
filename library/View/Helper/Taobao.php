<?php

class Zkernel_View_Helper_Taobao extends Zend_View_Helper_Abstract  {
	protected $_config = null;

	function init() {
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		$this->_config = @$config['taobao'] ? $config['taobao'] : array();
		$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		$mt = new Default_Model_Txt;
		$config_db = $mt->fetchCol('key', 'SUBSTRING(`key`, 1, 7) = "taobao_"');
		if ($config_db) {
			foreach ($config_db as $v) {
				$p = explode('_', $v);
				$p0 = array_shift($p);
				if ($p) $p = implode('_', $p);
				if ($p0 && $p) $this->_config[$p] = $view->txt($v);
			}
		}
		$this->_config['url'] = $this->_config['apimode'] == 'test'
			? 'http://gw.api.tbsandbox.com/router/rest'
			: 'http://gw.api.taobao.com/router/rest';
		$this->_config['param'] = array(
			'app_key' => $this->_config['appkey'],
			'format' => 'json',
			'sign_method' => 'md5',
			'v' => '2.0',
			'timestamp' => date('Y-m-d H:i:s')
		);

	}

	function taobao() {
		$this->init();
		return $this;
	}

	function fetchIdByUrl($url) {
		$urlpart = $this->view->urlparser()->getParams($url);
		$id = null;
		if (array_key_exists('item_num_id', $urlpart)) {
			$id = $urlpart['item_num_id'];
		}
		elseif (array_key_exists('id', $urlpart)) {
			$id = $urlpart['id'];
		}
		return $id ? $id : 'url_invalid';
	}

	function _sign($p) {
		$sign = $this->_config['appsecret'];
    	ksort($p);
    	foreach ($p as $k => $v) {
       		if ($k != '' && $v != '') $sign .= $k.$v;
    	}
    	$p['sign'] = strtoupper(md5($sign.$this->_config['appsecret']));
		$str = '';
    	foreach ($p as $k => $v) {
       		if ($k != '' && $v != '') $str .= ($str ? '&' : '').$k.'='.urlencode($v);
    	}
		return $str;
	}

	function fetchCard($id) {
		$p = array_merge($this->_config['param'], array(
			'method' => 'taobao.item.get',
			'fields' => 'detail_url,property_alias,num_iid,title,nick,props,type,cid,created,pic_url,item_img,num,price,post_fee,express_fee,ems_fee',
			'num_iid' => $id
		));
		$res = @json_decode(file_get_contents($this->_config['url'].'?'.$this->_sign($p)));
		$result = @$res->item_get_response->item;
		if ($result) {
			/*$html = file_get_contents($result->detail_url);
			if ($html) {
				preg_match('/\<li\ id\=\"J\_PromoPrice\"[^\>]+\>(.*?)\<\/li\>/si', $html, $res);
				print_r($res);exit();
			}
			print_r($result);exit();
*/
			$prop_val = array();
			$blocks = explode(';', $result->props);
			if (count($blocks)) foreach($blocks as $block) {
				$params = explode(':', $block);
				$prop_val[] = $params[1];
			}

			$prop_alias = array();
			$blocks = explode(';', $result->property_alias);
			if (count($blocks)) foreach($blocks as $block) {
				$params = explode(':', $block);
				if (count($params) == 3) $prop_alias[$params[1]] = $params[2];
			}

			$result->properties = new Zkernel_View_Data(array());
			$result->properties_values = new Zkernel_View_Data(array());
			$result->properties_values_alias = new Zkernel_View_Data(array());
			$p = array_merge($this->_config['param'], array(
				'method' => 'taobao.itemprops.get',
				'fields' => 'pid,name,prop_values',
				'cid' => $result->cid,
				'is_sale_prop' => 'true'
			));
			$prop = @json_decode(file_get_contents($this->_config['url'].'?'.$this->_sign($p)));
			$props = @$prop->itemprops_get_response->item_props->item_prop;
			if ($props) {
				foreach ($props as $el) {
					$result->properties->{$el->pid} = $el->name;
					if (@$el->prop_values->prop_value) {
						$result->properties_values->{$el->pid} = isset($result->properties_values->{$el->pid}) ? $result->properties_values->{$el->pid} : new Zkernel_View_Data(array());
						$result->properties_values_alias->{$el->pid} = isset($result->properties_values_alias->{$el->pid}) ? $result->properties_values_alias->{$el->pid} : new Zkernel_View_Data(array());
						foreach ($el->prop_values->prop_value as $el1) {
							if (in_array($el1->vid, $prop_val)) {
								$result->properties_values->{$el->pid}->{$el1->vid} = $el1->name;
								if (isset($prop_alias[$el1->vid])) $result->properties_values_alias->{$el->pid}->{$el1->vid} = $prop_alias[$el1->vid];
							}
						}
					}
				}
			}
		}
		return $result;
	}

	function xml2array($xml) {
		if (get_class($xml) == 'SimpleXMLElement') {
			$attributes = $xml->attributes();
			foreach($attributes as $k=>$v) {
				if ($v) $a[$k] = (string) $v;
			}
			$x = $xml;
			$xml = get_object_vars($xml);
		}
		if (is_array($xml)) {
			if (count($xml) == 0) return (string) $x; // for CDATA
			foreach($xml as $key=>$value) {
				$r[$key] = $this->xml2array($value);
			}
			if (isset($a)) $r['@attributes'] = $a;    // Attributes
			return new Zkernel_View_Data($r);
		}
		return (string)$xml;
	}
}