<?php
namespace pgood\youtube;

/**
 * Description of playlist
 *
 * @author Pavel Khoroshkov
 */
class playlist extends snippet{
	protected $data;
	
	function __construct($v,$apiKey,$arParams = null){
		parent::__construct($apiKey,$arParams);
		if(is_object($v)){
			if($v instanceof \stdClass && $v->kind == 'youtube#playlist')
				$this->data = $v;
			else
				throw new \Exception('Invalid init data');
		}else{
			$this->init($v);
		}
	}
	function videos($arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		$arParams['playlistId'] = $this->id();
		$ob = null;
		$arResult = array();
		$l10n = $this->l10n();
		
		do{
			$arIds = array();
			
			if($ob && $ob->nextPageToken)
				$arParams['pageToken'] = $ob->nextPageToken;
			if(($ob = $this->_playlistItems($arParams))
				&& !empty($ob->items)
			){
				foreach($ob->items as $obVideo){
					$arIds[] = $obVideo->snippet->resourceId->videoId;
				}
			}else
				break;
			
			
			if(($obVideos = $this->_videos(array('id' => implode(',',$arIds))))
				&& !empty($obVideos->items)
			){
				foreach($obVideos->items as $obVideo){
					$video = new video($obVideo,$this->apiKey);
					$video->l10n($l10n);
					$arResult[] = $video;
				}
			}else
				break;

		}while(!empty($ob->nextPageToken));
		
		return $arResult;
		
	}
	function init($id,$arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		if(($arParams['id'] = $id)
			&& ($ob = $this->_playlists($arParams))
			&& !empty($ob->items)
		){
			$this->data = array_shift($ob->items);
		}else
			throw new \Exception('Init failed');
	}
}
