<?php
namespace pgood\youtube;

/**
 * Description of video
 *
 * @author Pavel Khoroshkov
 */
class video extends snippet{
	
	function __construct($v,$apiKey,$arParams = null){
		parent::__construct($apiKey,$arParams);
		if(is_object($v)){
			if($v instanceof \stdClass && $v->kind == 'youtube#video')
				$this->data = $v;
			else
				throw new \Exception('Invalid init data');
		}else{
			$this->init($v);
		}
	}
	function embedHtml($width,$height,$class){
		ob_start();
		?><iframe<?=$class ? ' class="'.$class.'"' : null?> width="<?=$width?>" height="<?=$height?>" src="https://www.youtube.com/embed/<?=$this->data->id?>" frameborder="0" allowfullscreen></iframe><?
		return ob_get_clean();
	}
	function init($id,$arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		if(($arParams['id'] = $id)
			&& ($ob = $this->_videos($arParams))
			&& !empty($ob->items)
		){
			$this->data = array_shift($ob->items);
		}else
			throw new \Exception('Init failed');
	}
}
