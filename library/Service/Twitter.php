<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Service_Twitter extends Zend_Service_Twitter {
    protected $_methodTypes = array(
        'status',
        'user',
        'directMessage',
        'friendship',
        'account',
        'favorite',
        'block',
		'search'
    );

	public function statusSearchTimeline($q, array $params = array())
    {
        $this->_init();
		$this->setUri('http://search.twitter.com');
        $path = '/search';
        $_params = array(
			'q' => $q,
			'result_type' => 'mixed'
		);
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
				case 'result_type':
                    $_params['result_type'] = (string) $value;
                    break;
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                default:
                    break;
            }
        }
        $path .= '.json';
        $response = $this->_get($path, $_params);
        return json_decode($response->getBody());
    }
}
