<?php
namespace pgood\youtube;

/**
 * Description of channel
 *
 * @author Pavel Khoroshkov
 */
class channel extends transport{
	protected $data,$l10n;
	
	function __construct($id,$apiKey,$arParams = null){
		parent::__construct($apiKey,$arParams);
		$this->init($id);
	}
	function id(){
		return $this->data->id;
	}
	function title(){
		return $this->data->brandingSettings->channel->title;
	}
	function l10n($v = null){
		if($v !== null)
			$this->l10n = strtolower($v);
		return $this->l10n;
	}
	function url(){
		return 'https://www.youtube.com/channel/'.urlencode($this->id());
	}
	function playlists($arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		$arParams['channelId'] = $this->id();
		if(($ob = $this->_playlists($arParams))
			&& !empty($ob->items)
		){
			$l10n = $this->l10n();
			$arParams = $this->params();
			$arResult = array();
			foreach($ob->items as $obList){
				$pl = new playlist($obList,$this->apiKey,$arParams);
				$pl->l10n($l10n);
				$arResult[] = $pl;
			}
			return $arResult;
		}
	}
	function init($id,$arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		$arParams['id'] = $id;
		if(($ob = $this->_channels($arParams))
			&& !empty($ob->items)
			&& ($obChannel = array_shift($ob->items))
		){
			$this->data = $obChannel;
		}else
			throw new \Exception('Init failed');
	}
}
