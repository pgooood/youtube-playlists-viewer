<?php
namespace pgood\youtube;

/**
 * Description of transport
 *
 * @author Pavel Khoroshkov
 */
class transport{
	protected $apiKey,$cachePath,$cacheTime = 43200,$noCache = false,$cacheName;
	function __construct($apiKey,$arParams = null){
		$this->apiKey($apiKey);
		if($arParams)
			$this->params($arParams);
	}
	function cacheTime($v = null){
		if($v !== null)
			$this->cacheTime = intval($v);
		return $this->cacheTime;
	}
	function cacheName($v = null){
		if($v !== null)
			$this->cacheName = intval($v);
		return $this->cacheName;
	}
	function cachePath($v = null){
		if($v !== null)
			$this->cachePath = $v;
		return $this->cachePath;
	}
	function apiKey($v = null){
		if($v !== null)
			$this->apiKey = $v;
		return $this->apiKey;
	}
	function noCache($v = null){
		if($v !== null)
			$this->noCache = !!$v;
		return $this->noCache;
	}
	function params($arParams = null){
		if($arParams === null){
			return [
				'cache-path' => $this->cachePath()
				,'cache-time' => $this->cacheTime()
				,'cache-name' => $this->cacheName()
				,'no-cache' => $this->noCache()
			];
		}elseif(is_array($arParams)){
			if(isset($arParams['cache-path']))
				$this->cachePath($arParams['cache-path']);
			if(isset($arParams['cache-time']))
				$this->cacheTime($arParams['cache-time']);
			if(isset($arParams['cache-name']))
				$this->cacheName($arParams['cache-name']);
			if(isset($arParams['no-cache']))
				$this->noCache($arParams['no-cache']);
		}
	}
	protected function query($name,$arParams){
		if(!is_array($arParams))
			$arParams = array();
		$this->cacheCleanup();
		if($arParams['key'] = $this->apiKey()){
			if(!$this->noCache && ($v = $this->getCache($name,$arParams)))
				return json_decode($v);
			if(($v = file_get_contents('https://www.googleapis.com/youtube/v3/'.$name.'?'.http_build_query($arParams)))
				&& ($response = json_decode($v))
			){
				$this->cacheResult($name,$arParams,$v);
				return $response;
			}else{
				throw new \Exception('Request failed');
			}
		}else
			throw new \Exception('no API key');
	}
	protected function _channels($arParams){
		if(!is_array($arParams))
			$arParams = array();
		if(empty($arParams['part']))
			$arParams['part'] = 'brandingSettings';
		if(empty($arParams['maxResults']))
			$arParams['maxResults'] = 50;
		return $this->query('channels',$arParams);
	}
	protected function _playlists($arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		if(empty($arParams['part']))
			$arParams['part'] = 'id,snippet,localizations';
		if(empty($arParams['maxResults']))
			$arParams['maxResults'] = 50;
		return $this->query('playlists',$arParams);
	}
	protected function _playlistItems($arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		if(empty($arParams['part']))
			$arParams['part'] = 'id,snippet';//'id,snippet,contentDetails,status';
		if(empty($arParams['maxResults']))
			$arParams['maxResults'] = 50;
		return $this->query('playlistItems',$arParams);
	}
	protected function _videos($arParams = null){
		if(!is_array($arParams))
			$arParams = array();
		if(empty($arParams['part']))
			$arParams['part'] = 'id,snippet,localizations';
		if(empty($arParams['maxResults']))
			$arParams['maxResults'] = 50;
		return $this->query('videos',$arParams);
	}
	private function cacheId($name,$arParams){
		return md5(implode('&',$arParams));
	}
	private function cacheXml(){
		$path = ($this->cacheName() ? $this->cacheName() : 'youtube-cache').'.xml';
		if($this->cachePath())
			$path = $this->cachePath().(substr($this->cachePath(),-1) == '/' ? null : '/').$path;
		$xml = new \pgood\xml\cached($path);
		$xml->de('youtube-cache');
		return $xml;
	}
	private function getCache($name,$arParams){
		$xml = $this->cacheXml();
		if($v = $xml->evaluate('string(/*/'.$name.'/item[@id="'.$this->cacheId($name,$arParams).'"])'))
			return $v;
	}
	private function cacheResult($name,$arParams,$strResponse){
		$xml = $this->cacheXml();
		if(($eCont = $xml->query('/*/'.$name)->item(0))
			|| ($eCont = $xml->de()->append($name))
		){
			$e = $eCont->append('item');
			$e->id = $this->cacheId($name,$arParams);
			$e->date = time();
			$e->text($strResponse);
			$xml->save();
		}
	}
	private function cacheCleanup(){
		$now = time();
		$xml = $this->cacheXml();
		$ns = $xml->query('/*/*/item');
		foreach($ns as $e)
			if(!($date = intval($e->date)) || $now - $date > $this->cacheTime())
				$e->remove();
		$xml->save();
	}
}
