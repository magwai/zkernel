<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Video_Preview {
	var $path;
	var $image_path;

	function __construct($path = '', $image_path = '') {
		$this->path = $path;
		$this->image_path = $image_path ? $image_path : $path;
	}

	function create($name, $param = array()) {
		global $m;
		$width = @(int)$param['width'];
		$height = @(int)$param['height'];
		$prefix = @$param['prefix'];
		$max_width = 0;
		$max_height = 0;

		$desc = array(
			2 => array("pipe", "w")
		);
		$process = @proc_open('ffmpeg -i "'.$this->image_path.'/'.$name.'"', $desc, $pipes);
		if (is_resource($process)) {
			$info = stream_get_contents($pipes[2]);
			preg_match('/\,\ (\d*)x(\d*)(\,|\ )/si', $info, $res);
			$max_width = @(int)$res[1];
			$max_height = @(int)$res[2];
			fclose($pipes[2]);
			proc_close($process);
		}
		if (@$param['time']) {
			$name = explode('.', $name);
			array_pop($name);
			$name = implode('.', $name);
			@exec('ffmpeg -i "'.$this->image_path.'/'.$name.'.avi" -an -ss '.(int)$param['time'].' -r 1 -vframes 1 '.($max_width && $max_height ? '-s '.$max_width.'x'.$max_height : '').' -y -f mjpeg "'.$this->path.'/'.$prefix.$name.'.jpg"');
			$preview = new Zkernel_Image_Preview(
				$this->path,
				$this->path
			);
			$param['prefix'] = '';
			$preview->create($prefix.$name.'.jpg', $param);
			copy($this->path.'/'.$prefix.$name.'.jpg', $this->path.'/'.$prefix.$name.'.avi');
			unlink($this->path.'/'.$prefix.$name.'.jpg');
			@chmod($this->path.'/'.$prefix.$name.'.avi', 0777);
		}
		else {
			if (!$width || !$height) return false;
			if (!$max_width || !$max_height) return false;

			if ($width > $max_width) $width = $max_width;
			if ($height > $max_height) $height = $max_height;

			if ($width / 2 != floor($width / 2)) $width++;
			if ($height / 2 != floor($height / 2)) $height++;

			@exec('ffmpeg -i "'.$this->image_path.'/'.$name.'" '.($width && $height ? '-s '.$width.'x'.$height : '').' -y -f flv -acodec libmp3lame -ac 2 -ar 44100 "'.$this->path.'/'.$prefix.$name.'"');
			if (!file_exists($this->path.'/'.$prefix.$name)) @exec('ffmpeg -i "'.$this->image_path.'/'.$name.'" '.($width && $height ? '-s '.$width.'x'.$height : '').' -y -f flv -ac 2 -ar 44100 "'.$this->path.'/'.$prefix.$name.'"');
		}

		return true;
	}
}
