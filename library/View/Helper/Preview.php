<?php

class Zkernel_View_Helper_Preview extends Zend_View_Helper_Abstract  {
	public function preview($dir, $name, $param = array()) {
    	if (!$dir || !$name) return @$param['default'];
		$prefix = $param['prefix'] = @$param['prefix'] ? $param['prefix'].'_' : '';
		$ext = strrpos($name, '.');
		$ext = $ext === false ? '' : @substr($name, $ext + 1);
		$ctype = 'image';
		if ($ext == 'avi') $ctype = 'video';
		$modified_o = @filemtime(PUBLIC_PATH.'/upload/'.$dir.'/'.$name);
		if (!$modified_o) return @$param['default'];
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