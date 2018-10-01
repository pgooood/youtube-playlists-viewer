<?php
namespace pgood\youtube;

/**
 * Description of snippet
 *
 * @author Pavel Khoroshkov
 */
abstract class snippet extends transport{
	protected $data,$l10n;
	
	function id(){
		return $this->data->id;
	}
	function title(){
		if($l10n = $this->localization())
			return $l10n->title;
		return $this->data->snippet->title;
	}
	function description(){
		if($l10n = $this->localization())
			return nl2br($l10n->description);
		return nl2br($this->data->snippet->description);
	}
	function date(){
		return $this->data->snippet->publishedAt;
	}
	/**
	 * Image
	 * @param string $size size of the image could be: default, medium, high, standard
	 * @return string
	 */
	function image($size = 'high'){
		if(isset($this->data->snippet->thumbnails->{$size})
			&& !strpos($this->data->snippet->thumbnails->{$size}->url,'no_thumbnail')
		){
			return $this->data->snippet->thumbnails->{$size}->url;
		}
	}
	function l10n($v = null){
		if($v !== null)
			$this->l10n = strtolower($v);
		return $this->l10n;
	}
	protected function localization(){
		if($this->l10n()
			&& $this->data->localizations
			&& $this->data->localizations->{$this->l10n()}
		){
			return $this->data->localizations->{$this->l10n()};
		}
	}
	/**
	 * Initialize $data property using transport's class queries
	 */
	abstract function init($id,$arParams = null);
}
