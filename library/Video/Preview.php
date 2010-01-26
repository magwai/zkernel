<?php

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
		if (!$width || !$height) return false;
		if (!$max_width || !$max_height) return false;

		if ($width > $max_width) $width = $max_width;
		if ($height > $max_height) $height = $max_height;

		if ($width / 2 != floor($width / 2)) $width++;
		if ($height / 2 != floor($height / 2)) $height++;

		@exec('ffmpeg -i "'.$this->image_path.'/'.$name.'" '.($width && $height ? '-s '.$width.'x'.$height : '').' -y -f flv -acodec pcm_s16le -ac 2 -ar 44100 "'.$this->path.'/'.$prefix.$name.'"');

		return true;
	}
}
