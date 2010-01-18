<?php

/**
 * @zk_title   		Сжатие JS и CSS
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Minify extends Zend_Controller_Action {
	public function indexAction() {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
		$request = $this->getRequest();
		$response = $this->getResponse();

	   	$ext = $request->getParam('ext');
	    $path = PUBLIC_PATH.'/'.$request->getParam('path').'.'.$ext;

	    $file = basename($path);

		$content_type = '';

		if ($path && $file) {
			$res = '';

			$content_type = $ext == 'js'
				? 'application/javascript'
				: 'text/css';

			$modified = gmdate('D, d M Y H:i:s', filemtime($path)).' GMT';

			$expires = gmdate('D, d M Y H:i:s', time() + 2592000).' GMT';

			if ($modified && @$_SERVER['HTTP_IF_MODIFIED_SINCE'] === $modified) $response->setHttpResponseCode(304);
			else {
				$res = file_get_contents($path);

				if ($res) {
					$md5 = md5($path.$modified);

					$cache = Zend_Cache::factory('Core', 'Memcached');

					$res_1 = $cache->test($md5)
						? $cache->load($md5)
						: '';

					if ($res_1) $res = $res_1;
					else {
						$process = popen('java -client -Xmx64m', 'r');
						$ret = false;
						if (is_resource($process)) {
							$read = fread($process, 1024);
							pclose($process);
							if ($read) $ret = true;
						}
						if ($ret) {
							$desc = array(
							   0 => array("pipe", "r"),
							   1 => array("pipe", "w")
							);
							$process = proc_open('java -client -Xmx64m -jar "'.realpath(APPLICATION_PATH.'/../library/Zkernel/Other/Jar/yuicompressor.jar').'" --charset utf-8 --type '.$ext, $desc, $pipes);
							if (is_resource($process)) {
								fwrite($pipes[0], $res);
								fclose($pipes[0]);
								$res_1 = stream_get_contents($pipes[1]);
								fclose($pipes[1]);
								if ($res_1) $res = "/* yuicompressor */\n".$res_1;
								proc_close($process);
							}
						}
						$cache->save($res, $md5);
					}
				}
				echo $res;
			}
		}
		else {
			$modified = gmdate('D, d M Y H:i:s').' GMT';
			$expires = $modified;
			$response->setHttpResponseCode(404);
		}
		$response	->setHeader('Cache-Control', 'no-cache')
					->setHeader('Pragma', 'no-cache')
					->setHeader('Last-Modified', $modified)
					->setHeader('Expires', $expires);
		if ($content_type) $response
					->setHeader('Content-type', $content_type);

    }
}

