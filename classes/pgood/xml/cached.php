<?php
namespace pgood\xml;

class cached extends xml{
	protected static $cache;
	function __construct($v = null){
		if(!is_array(self::$cache))
			self::$cache = array();
		parent::__construct($v);
	}
	function init($v){
		if(!empty($v)
			&& is_string($v)
			&& (preg_match('/\.xml$/i',$v) || filter_var($v,FILTER_VALIDATE_URL) !== false || is_file($v))
			&& ($dd = $this->cache($v))
		){
			return parent::init($dd);
		}
		$res = parent::init($v);
		$this->cache();
		return $res;
	}
	function load($src){
		if($dd = $this->cache($src)){
			$this->dd = $dd;
		}else{
			parent::load($src);
			$this->cache();
		}
	}
	function save($uri = null){
		$old = $this->documentURI();
		if(($res = parent::save($uri)) && $old != $this->documentURI()){
			$this->clearCache($old);
			$this->cache();
		}
		return $res;
	}
	protected function cache($src = false){
		if($src !== false){
			if(($uri = cached::normalizePath($src)) && isset(self::$cache[$uri]))
				return self::$cache[$uri];
		}elseif($uri = cached::normalizePath($this->documentURI())){
			if(!isset(self::$cache)) self::$cache = array();
			self::$cache[$uri] = $this->dd();
		}
	}
	static function clearCache($src){
		if(($uri = cached::normalizePath($src))
		   && isset(self::$cache[$uri])
		) unset(self::$cache[$uri]);
	}
	static function normalizePath($path){
		$m = null;
		if(mb_ereg('^file:\/\/([^\/].*)$',$path,$m))
			$path = 'file:///'.$m[1];
		if($path && (!($url = parse_url($path)) || !isset($url['scheme']))){
			if(substr($path,0,1)!='/'){
				$scriptDir = pathinfo(filter_input(INPUT_SERVER,'SCRIPT_FILENAME'),PATHINFO_DIRNAME);
				$arScriptDir = explode('/',$scriptDir);
				$arPath = explode('/',$path);
				foreach($arPath as $folder){
					if($folder=='..') array_pop($arScriptDir);
					elseif($folder) array_push($arScriptDir,$folder);
				}
				$path = implode('/',$arScriptDir);
			}
			$path = 'file://'.(substr($path,0,1)!='/' ? '/' : null).$path;
		}
		return $path;
	}
}