<?php

class Zkernel_Form extends Zend_Form
{
	function init() {
		$this->addPrefixPath(
			'Zkernel_Form_',
			'Zkernel/Form'
		);
	}

	function translateError($e) {
		$d = array(
			'isEmpty' => 'обязательно для заполнения',
			'regexNotMatch' => 'недопустимые символы',
			'fileImageSizeWidthTooBig' => 'картинка слишком широкая',
			'fileImageSizeHeightTooBig' => 'картинка слишком высокая',
			'fileMimeTypeFalse' => 'неверный тип файла',
			'fileMimeTypeNotReadable' => 'невозможно определить тип файла',
			'fileUploadErrorIniSize' => 'размер файла слишком большой',
			'fileImageSizeNotReadable' => 'невозможно прочитать размер картинки',
			'fileImageSizeNotDetected' => 'невозможно определить размер картинки',
			'fileExtensionFalse' => 'Запрещено загружать файлы этого типа',
			'recordFound' => 'Дублирующееся значение',
			'emailAddressInvalidFormat' => 'неверный e-mail адрес'
		);
		return isset($d[$e]) ? $d[$e] : $e;
	}

	function translateErrors($e) {
		if ($e) foreach ($e as &$el) $el = self::translateError($el);
		return $e;
	}

	function getErrors() {
		$e = parent::getErrors();
		if ($e) {
			foreach ($e as &$el) {
				if ($el) {
					foreach ($el as &$el_1) $el_1 = $this->translateError($el_1);
				}
			}
		}
		return $e;
	}
}
