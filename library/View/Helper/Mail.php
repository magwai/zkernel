<?php

class Zkernel_View_Helper_Mail extends Zkernel_View_Helper_Override  {
	function mail($data) {
		$mail = new Zend_Mail('utf-8');
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$body = $this->view->partial('mail/frame.phtml', array(
			'message' => $this->view->partial('mail/'.$data['view'].'.phtml', $data)
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
		$mail->setFrom(
			$from,
			@$data['from_name'] ? $data['from_name'] : ($from == $fm ? $this->view->txt('site_title') : $from)
		);
		$mail->addTo(
			$to,
			@$data['to_name'] ? $data['to_name'] : $to
		);
		$mail->setSubject(
			$this->view->txt('site_title').($data['subject'] ? ' — '.$data['subject'] : '')
		);
		$ok = true;
		try {
			$mail->send();
		}
		catch (Zend_Mail_Transport_Exception $e) {
			$ok = false;
		}
		return $ok;
	}
}