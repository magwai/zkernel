<?php

require_once APPLICATION_PATH.'/../library/Zkernel/Other/Lib/Phpthumb/ThumbLib.inc.php';

class Zkernel_Image_Preview {
	var $path;
	var $image_path;

	function __construct($path = '', $image_path = '') {
		$this->path = $path;
		$this->image_path = $image_path ? $image_path : $path;
	}

	function create($name, $param = array()) {
		global $m;
		$bg_color = isset($param['bg_color']) ? $param['bg_color'] : array(255, 255, 255);
		if (!is_array($bg_color)) $bg_color = $m->f_hex2rgb($bg_color);
		$fit = isset($param['fit']) ? $param['fit'] : false;
		$mark = @$param['mark'];
		$align = isset($param['align']) ? $param['align'] : 'cc';
		$align = array(strtolower(substr($align, 0, 1)), strtolower(substr($align, 1, 1)));
		$stretch = isset($param['stretch']) ? $param['stretch'] : false;
		$quality = @(int)$param['quality'] ? (int)$param['quality'] : 95;
		$format = @isset($param['format']) ? $param['format'] : null;
		$width = @(int)$param['width'];
		$height = @(int)$param['height'];
		$prefix = @$param['prefix'];
		$min_width = @(int)$param['min_width'];
		$min_height = @(int)$param['min_height'];
		if ($min_width && $width && $width < $min_width) return false;
		if ($min_height && $height && $height < $min_height) return false;
		if (($min_width || $min_height) && $fit) return false;
		$thumb = PhpThumbFactory::create($this->image_path.'/'.$name, array(
			'jpegQuality' => $quality,
			'resizeUp' => $stretch,
			'correctPermissions' => true
		));

		if ($fit) $thumb->adaptiveResize($width, $height);
		else $thumb->resize($width, $height);

		if ($min_width || $min_height) {
			$dim = $thumb->getCurrentDimensions();
			$n_w = $dim['width'];
			$n_h = $dim['height'];
			if ($min_width > $n_w) $n_w = $min_width;
			if ($min_height > $n_h) $n_h = $min_height;
			if (($min_width > $dim['width'] || $min_height > $dim['height']) && $stretch) {
				$thumb->resize($n_w, $n_h);
				$dim = $thumb->getCurrentDimensions();
			}
			if ($min_width > $dim['width'] || $min_height > $dim['height']) {
				if ($align[0] == 'l') $new_x = 0;
				else if ($align[0] == 'c') $new_x = floor(($n_w - $dim['width']) / 2);
				else if ($align[0] == 'r') $new_x = $n_w - $dim['width'];

				if ($align[1] == 't') $new_y = 0;
				else if ($align[1] == 'c') $new_y = floor(($n_h - $dim['height']) / 2);
				else if ($align[1] == 'b') $new_y = $n_h - $dim['height'];

				$image = $thumb->getOldImage();
				$new_image = imagecreatetruecolor($n_w, $n_h);
				if (!$new_image) return false;
				$color = imagecolorallocate($new_image, $bg_color[0], $bg_color[1], $bg_color[2]);
				imagefilledrectangle($new_image, 0, 0, $n_w - 1, $n_h - 1, $color);
				imagecopyresampled($new_image, $image, $new_x, $new_y, 0, 0, $dim['width'], $dim['height'], $dim['width'], $dim['height']);
				$thumb->setOldImage($new_image);
			}
		}

		if ($mark) {
			$image = $thumb->getOldImage();
			$this->mark($image, $param['mark']);
			$thumb->setOldImage($image);
		}

		$thumb->save($this->path.'/'.$prefix.$name, $format);

		return true;
	}

	private function mark($image, $param) {
		global $m;
		$width = imagesx($image);
		$height = imagesy($image);
		$param['padding_h'] = @(int)$param['padding_h'];
		$param['padding_v'] = @(int)$param['padding_v'];
		$align = isset($param['align']) ? $param['align'] : 'cc';
		$align = array(strtolower(substr($align, 0, 1)), strtolower(substr($align, 1, 1)));

		$png = @imagecreatefrompng($param['file']);
		$p_width = imagesx($png);
		$p_height = imagesy($png);

		if ($align[0] == 'l') $x = $param['padding_h'];
		else if ($align[0] == 'c') $x = floor(($width - $p_width) / 2);
		else if ($align[0] == 'r') $x = $width - $p_width - $param['padding_h'];

		if ($align[1] == 't') $y = $param['padding_v'];
		else if ($align[1] == 'c') $y = floor(($height - $p_height) / 2);
		else if ($align[1] == 'b') $y = $height - $p_height - $param['padding_v'];

		if ($x >= 0 && $x <= ($width - 1) && $y >= 0 && $y <= ($height - 1)) {
			@imagecopy($image, $png, $x, $y, 0, 0, $p_width, $p_height);
		}
		@imagedestroy($png);
	}
}
