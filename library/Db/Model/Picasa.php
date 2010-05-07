<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Picasa {
    public $_name = 'default';
    public $_service;
    public $_google;

    function __construct($options = array()) {
    	$this->_google = Zend_Registry::get('Zkernel_Google');
    	if (isset($this->_google['picasa'][$this->_name]['service'])) $this->_service = $this->_google['picasa'][$this->_name]['service'];
		else {
			$user = @$options['login'] ? $options['login'] : $this->_google['login'];
    		$pass = @$options['password'] ? $options['password'] : $this->_google['password'];
			$this->_service = new Zend_Gdata_Photos(Zend_Gdata_ClientLogin::getHttpClient(
				$user,
				$pass,
				Zend_Gdata_Photos::AUTH_SERVICE_NAME
			));
			$this->_google['picasa'][$this->_name]['service'] = $this->_service;
			Zend_Registry::set('Zkernel_Google', $this->_google);
		}
	}

	function _fetchAlbums() {
		$res = array();
		if (isset($this->_google['picasa'][$this->_name]['album_list'])) $album = $this->_google['picasa'][$this->_name]['album_list'];
		else {
			$album = $this->_service->getUserFeed();
			$this->_google['picasa'][$this->_name]['album_list'] = $album;
			Zend_Registry::set('Zkernel_Google', $this->_google);
		}
		if ($album && count($album) > 0) {
			foreach ($album as $el) {
				$d = $this->_parseAlbum($el);
				$res[] = $d;
				$this->_google['picasa'][$this->_name]['album_card'][$d->id] = $d;
			}
		}
		return $res ? new Zkernel_View_Data($res) : array();
	}

	function _fetchAlbum($id) {
		$res = array();
		if ($id) {
			if (isset($this->_google['picasa'][$this->_name]['album_card'][$id])) $album = $this->_google['picasa'][$this->_name]['album_card'][$id];
			else {
				$query = new Zend_Gdata_Photos_AlbumQuery();
				$query->setAlbumId($id);
				$album = $this->_service->getAlbumEntry($query);
				$this->_google['picasa'][$this->_name]['album_card'][$id] = $album;
				Zend_Registry::set('Zkernel_Google', $this->_google);
			}
			if ($album) $res = $this->_parseAlbum($album);
		}
		return $res ? new Zkernel_View_Data($res) : array();
	}

	function _fetchPhotos($id) {
		$res = array();
		if (isset($this->_google['picasa'][$this->_name]['photo_list'][$id])) $album = $this->_google['picasa'][$this->_name]['photo_list'][$id];
		else {
			$query = new Zend_Gdata_Photos_AlbumQuery();
			$query->setAlbumId($id);
			$photo = $this->_service->getAlbumFeed($query);
			$this->_google['picasa'][$this->_name]['photo_list'][$id] = $photo;
			Zend_Registry::set('Zkernel_Google', $this->_google);
		}
		if ($photo && count($photo) > 0) {
			foreach ($photo as $el) {
				$d = $this->_parsePhoto($el);
				$res[] = $d;
				$this->_google['picasa'][$this->_name]['photo_card'][$d->id] = $d;
			}
		}
		return $res ? new Zkernel_View_Data($res) : array();
	}

	function _fetchPhoto($id, $album) {
		$res = array();
		if ($id) {
			if (isset($this->_google['picasa'][$this->_name]['photo_card'][$id])) $photo = $this->_google['picasa'][$this->_name]['photo_card'][$id];
			else {
				$query = new Zend_Gdata_Photos_PhotoQuery();
				$query->setPhotoId($id);
				$query->setAlbumId($album);
				$photo = $this->_service->getPhotoEntry($query);
				$this->_google['picasa'][$this->_name]['photo_card'][$id] = $photo;
				Zend_Registry::set('Zkernel_Google', $this->_google);
			}
			if ($photo) $res = $this->_parsePhoto($photo);
		}
		return $res ? new Zkernel_View_Data($res) : array();
	}

	function _parseAlbum($el) {
		$content = (array)$el->getMediaGroup()->getContent();
		$content = @$content[0];
		$thumb = (array)$el->getMediaGroup()->getThumbnail();
		$ex = array();
		if ($thumb) foreach ($thumb as $n => $t) {
			$ex['thumb_'.$n.'_url'] = $t->getUrl();
			$ex['thumb_'.$n.'_width'] = $t->getWidth();
			$ex['thumb_'.$n.'_height'] = $t->getHeight();
		}
		return array_merge(array(
			'id' => (string)$el->getGphotoId(),
			'photo_count' => (string)$el->getGphotoNumPhotos(),
			'user' => (string)$el->getGphotoUser(),
			'user_nickname' => (string)$el->getGphotoNickname(),
			'date' => date('Y-m-d H:i:s', (double)(string)$el->getGphotoTimestamp() / 1000),
			'name' => (string)$el->getGphotoName(),
			'title' => (string)$el->getMediaGroup()->getTitle(),
			'description' => (string)$el->getMediaGroup()->getDescription(),
			'image_url' => (string)$content->getUrl()
		), $ex);
	}

	function _parsePhoto($el) {
		$content = (array)$el->getMediaGroup()->getContent();
		$content = @$content[0];
		$thumb = (array)$el->getMediaGroup()->getThumbnail();
		$ex = array();
		if ($thumb) foreach ($thumb as $n => $t) {
			$ex['thumb_'.$n.'_url'] = $t->getUrl();
			$ex['thumb_'.$n.'_width'] = $t->getWidth();
			$ex['thumb_'.$n.'_height'] = $t->getHeight();
		}
		return array_merge(array(
			'id' => (string)$el->getGphotoId(),
			'parentid' => (string)$el->getGphotoAlbumId(),
			'version' => (string)$el->getGphotoAlbumId(),
			'width' => (string)$el->getGphotoWidth(),
			'height' => (string)$el->getGphotoHeight(),
			'size' => (string)$el->getGphotoSize(),
			'checksum' => (string)$el->getGphotoChecksum(),
			'date' => date('Y-m-d H:i:s', (double)(string)$el->getGphotoTimestamp() / 1000),
			'title' => (string)$el->getMediaGroup()->getTitle(),
			'description' => (string)$el->getMediaGroup()->getDescription(),
			'image_url' => (string)$content->getUrl()
		), $ex);
	}

	function _insertAlbum($data) {
		$new_access = $this->_service->newAccess();
		$new_access->text = 'public';
		$entry = new Zend_Gdata_Photos_AlbumEntry();
		$entry->setGphotoAccess($new_access);
		$entry->setGphotoTimestamp($this->_service->newTimestamp((string)strtotime($data['date']).'000'));
		$entry->setTitle($this->_service->newTitle($data['title']));
		$entry = $this->_service->insertAlbumEntry($entry);
		return $entry ? $entry->getGphotoId() : false;
	}

	function _updateAlbum($data, $id) {
		$query = new Zend_Gdata_Photos_AlbumQuery();
		$query->setAlbumId($id);
		$album = $this->_service->getAlbumEntry($query);
		$album->setGphotoTimestamp($this->_service->newTimestamp((string)strtotime($data['date']).'000'));
		$album->setTitle($this->_service->newTitle($data['title']));
		return $album->save();
	}

	function _deleteAlbum($id) {
		$query = new Zend_Gdata_Photos_AlbumQuery();
		$query->setAlbumId($id);
		$album = $this->_service->getAlbumEntry($query);
		$this->_service->deleteAlbumEntry($album);
		return true;
	}

	function _insertPhoto($data) {
		$s = @getimagesize($data['image_url']);
		$fd = $this->_service->newMediaFileSource($data['image_url']);
		$fd->setContentType(@$s['mime']);

		$entry = new Zend_Gdata_Photos_PhotoEntry();
		$entry->setMediaSource($fd);
		$entry->setGphotoTimestamp($this->_service->newTimestamp((string)strtotime($data['date']).'000'));
		//$entry->setTitle($this->_service->newTitle($data['description']));

		$query = new Zend_Gdata_Photos_AlbumQuery();
		$query->setAlbumId($data['parentid']);
		$album = $this->_service->getAlbumEntry($query);
		$entry = $this->_service->insertPhotoEntry($entry, $album);
		return $entry ? $entry->getGphotoId() : false;
	}

	function _updatePhoto($data, $id, $album) {
		$query = new Zend_Gdata_Photos_PhotoQuery();
		$query->setPhotoId($id);
		$query->setAlbumId($album);
		$photo = $this->_service->getPhotoEntry($query);
		if (stripos($data['image_url'], 'http://') === false) {
			$s = @getimagesize($data['image_url']);
			$fd = $this->_service->newMediaFileSource($data['image_url']);
			$fd->setContentType(@$s['mime']);
			$photo->setMediaSource($fd);
		}
		$photo->setGphotoTimestamp($this->_service->newTimestamp((string)strtotime($data['date']).'000'));
		//$photo->setTitle($this->_service->newTitle($data['description']));
		return $photo->save();
	}

	function _deletePhoto($id, $album) {
		$query = new Zend_Gdata_Photos_PhotoQuery();
		$query->setPhotoId($id);
		$query->setAlbumId($album);
		$photo = $this->_service->getPhotoEntry($query);
		$this->_service->deletePhotoEntry($photo);
		return true;
	}
}
