<?php

class Zkernel_Controller_Action_Helper_Preview extends Zend_Controller_Action_Helper_Abstract  {
	public function direct($dir, $name, $param = array()) {
		if (!$dir || !$name) return '';
		$prefix = $param['prefix'] = @$param['prefix'] ? $param['prefix'].'_' : '';
		$ext = strrpos($name, '.');
		$ext = $ext === false ? '' : @substr($name, $ext + 1);
		$ctype = 'image';
		if ($ext == 'avi') $ctype = 'video';
		$modified_o = @filemtime(PUBLIC_PATH.'/upload/'.$dir.'/'.$name);
		if (!$modified_o) return '';
		$modified = @filemtime(PUBLIC_PATH.'/pc/'.$dir.'/'.$prefix.$name);
		if ($modified < $modified_o) {
			if (!@file_exists(PUBLIC_PATH.'/pc/'.$dir)) mkdir(PUBLIC_PATH.'/pc/'.$dir, 0755, true);
			if ($ctype == 'image') {
				$preview = new Zkernel_Image_Preview(
					PUBLIC_PATH.'/pc/'.$dir,
					PUBLIC_PATH.'/upload/'.$dir
				);
				$preview->create($name, $param);
			}
			else if ($ctype == 'video') {
				$preview = new Zkernel_Video_Preview(
					PUBLIC_PATH.'/pc/'.$dir,
					PUBLIC_PATH.'/upload/'.$dir
				);
				$preview->create($name, $param);
			}
			@chmod(PUBLIC_PATH.'/pc/'.$dir.'/'.$prefix.$name, 0755);
	    }
	    return $modified || @file_exists(PUBLIC_PATH.'/pc/'.$dir.'/'.$prefix.$name)
	    	? '/pc/'.$dir.'/'.$prefix.$name
	    	: @$param['default'];
    }
}