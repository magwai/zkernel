<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Mail extends Zkernel_View_Helper_Override  {
	function mail($data) {
		$mail = new Zend_Mail('utf-8');
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
		$body = $this->view->partial('mail/frame.phtml', array(
			'message' => @$data['body'] ? $data['body'] : $this->view->partial('mail/'.$data['view'].'.phtml', $data)
		));
		preg_match_all('/src\=\"\/(img|upload\/mce\/image)\/([^\"]+)\"/si', $body, $res);
		if (@$res[1]) {
			$r = array();
			foreach ($res[1] as $k => $v) {
				$fn = PUBLIC_PATH.'/'.$res[1][$k].'/'.$res[2][$k];
				$s = getimagesize($fn);
				if ($s) {
					$cid = md5($res[1][$k].'/'.$res[2][$k]);
					$at = $mail->createAttachment(
						file_get_contents($fn),
						$s['mime'],
						Zend_Mime::DISPOSITION_INLINE,
                        Zend_Mime::ENCODING_BASE64
					);
					$at->id = $cid;
					$r[] = 'src="cid:'.$cid.'"';
				}
				else $r[] = $res[0][$k];
			}
			$body = str_ireplace($res[0], $r, $body);
		}
		$mail->setBodyHtml(
			$body
		);
		$fm = $this->view->txt('site_mail');
		$from = @$data['from'] ? $data['from'] : $this->view->txt('site_mail');
		$to = @$data['to'] ? $data['to'] : $this->view->txt('site_mail');
		$to = preg_split('/(\;|\,)/i', $to);

		$reply_to = @$data['reply_to'];
		$from_name = @$data['from_name'] ? $data['from_name'] : ($from == $fm ? $this->view->txt('site_title') : $from);
		if ($reply_to) $mail->setReplyTo(
			$reply_to,
			$from_name
		);
		$mail->setFrom(
			$from,
			$from_name
		);
		$tn = @$data['to_name'] ? $data['to_name'] : $to;
		foreach ($to as $n => $el) {
			$el = trim($el);
			if (!$el) continue;
			$tn_el = is_array($tn) ? (isset($tn[$n]) ? $tn[$n] : @$tn[0]) : $tn;
			$mail->addTo(
				$el,
				$tn_el
			);
		}
		if (@$data['subject_full']) $mail->setSubject(
			$data['subject_full']
		);
		else $mail->setSubject(
			$this->view->txt('site_title').($data['subject'] ? ' — '.$data['subject'] : '')
		);
		$ok = true;
		try {
			$tr = null;
			$bt = Zend_Controller_Front::getInstance()->getParam('bootstrap');
			if ($bt) {
				$config = $bt->getOptions();
				if (@$config['mail']) {
					if (@$config['mail']['transports'] && @$config['mail']['transports']['transport']) {
						foreach ($config['mail']['transports']['transport'] as $k => $v) {
							$class = 'Zend_Mail_Transport_'.ucfirst($v);
							$tr = new $class($config['mail']['transports'][$v]['host'][$k], array(
								'host' => $config['mail']['transports'][$v]['host'][$k],
								'port' => $config['mail']['transports'][$v]['port'][$k],
								'auth' => $config['mail']['transports'][$v]['auth'][$k],
								'username' => $config['mail']['transports'][$v]['username'][$k],
								'password' => $config['mail']['transports'][$v]['password'][$k],
								'ssl' => $config['mail']['transports'][$v]['ssl'][$k]
							));
							try {
								$ok = true;
								$mail->send($tr);
								break;
							}
							catch (Exception $e) {
								$ok = false;
								//@file_put_contents(DATA_PATH.'/mail-'.time().microtime(true).'.txt', var_export($e, 1));
							}
						}
						$tr = null;
					}
					else if (@$config['mail']['transport']) {
						$k = $config['mail']['transport'];
						if (@$config['mail'][$k] && @$config['mail'][$k]['host']) {
							try {
								$class = 'Zend_Mail_Transport_'.ucfirst($k);
								$tr = new $class($config['mail']['smtp']['host'], $config['mail'][$k]);
							}
							catch (Exception $e) {
								$tr = null;
								$ok = false;
							}
						}
					}
				}
			}
			if (!$ok) $mail->send($tr);
		}
		catch (Zend_Mail_Transport_Exception $e) {
			$ok = false;
		}
		return $ok;
	}
}
