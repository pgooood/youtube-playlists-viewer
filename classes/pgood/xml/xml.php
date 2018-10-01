<?php
namespace pgood\xml;

class xml{
	protected $dd;
	private $xpc;
	
	function __construct($v = null){
		$this->init($v);
	}
	function __toString(){
		return strval($this->dd()->saveXML());
	}
	protected function init($v){
		if($v){
			if(is_object($v)){
				if($v instanceof \DOMDocument) $this->dd = $v;
				elseif($v instanceof \DOMElement) $this->dd = $v->ownerDocument;
				elseif($v instanceof element) $this->dd = $v->xml()->dd();
				elseif($v instanceof xml) $this->dd = $v->dd();
			}elseif(is_string($v)){
				if(self::isXMLString($v)){
					$this->dd = @\DOMDocument::loadXML($v);
				}elseif(is_file($v)||filter_var($v,FILTER_VALIDATE_URL)!==false){
					$this->load($v);
				}elseif(preg_match('/\.xml$/i',$v)){
					$this->dd = new \DOMDocument('1.0','utf-8');
					$this->documentURI($v);
				}else{
					try{
						$e = new \DOMElement($v);
						$this->dd = new \DOMDocument('1.0','utf-8');
						$this->dd->appendChild($e);
					}catch(\DOMException $e){}
				}
			}
		}else $this->dd = new \DOMDocument('1.0', 'utf-8');
		if($this->dd) $this->xpc = new \DOMXPath($this->dd);
		else throw new \Exception('xml::init - wrong init value <pre>'.print_r($v,1).'</pre>');
	}
	function dd(){
		return $this->dd;
	}
	function de($name = null){
		if($this->dd()->documentElement)
			return new element($this->dd()->documentElement);
		elseif($name && $this->append($name))
			return $this->de();
	}
	function load($src){
		$this->dd = @\DOMDocument::load($src);
		if(!$this->dd) throw new \Exception('xml::load failed <pre>'.print_r($src,1).'</pre>');
		$this->xpc = new \DOMXPath($this->dd);
	}
	function loadHTML($html){
		$this->dd = @\DOMDocument::loadHTML($html);
		if(!$this->dd) throw new \Exception('xml::loadHTML failed <pre>'.print_r($html,1).'</pre>');
		$this->xpc = new \DOMXPath($this->dd);
	}
	function importNode($n,$deep = true){
		if($n instanceof xml && $n->de()) $n = $n->de()->e();
		if($n instanceof element) $n = $n->e();
		elseif(is_string($n)){
			try{
				return $this->create($n)->e();
			}catch(\DOMException $e){}
		}
		return $this->dd()->importNode($n,$deep);
	}
	function append($v){
		if(($n = $this->importNode($v))
			&& ($n = $this->dd()->appendChild($n))
		){
			if($n instanceof \DOMElement)
				return new element($n);
			return $n;
		}
	}
	static function fixUri($v){
		$m = null;
		if(preg_match('/file:\/([^\/]+.*)$/',$v,$m))
			$v = 'file://'.$m[1];
		return $v;
	}
	function documentURI($v = null){
		if($v) $this->dd()->documentURI = $v;
		return self::fixUri($this->dd()->documentURI);
	}
	function registerNameSpace($prefix,$uri){
		if(!$this->xpc && $this->dd()) $this->xpc = new \DOMXPath($this->dd());
		return $this->xpc->registerNamespace($prefix,$uri);
	}
	function query($query,$node = null){
		if($node){
			if($node instanceof \DOMNode || ($node instanceof element && ($node = $node->e())))
				$v = $this->xpc->query($query,$node);
		}else
			$v = $this->xpc->query($query);
		if(!$v) throw new \Exception($query);
		if($v && $v instanceof \DOMNodeList)
			$v = new nodeList($v);
		return $v;
	}
	function evaluate($query,$node = null){
		if($node){
			if($node instanceof \DOMNode || ($node instanceof element && ($node = $node->e())))
				$v = $this->xpc->evaluate($query,$node);
		}else
			$v = $this->xpc->evaluate($query);
		//if(!$v) throw new \Exception($query);
		if($v && $v instanceof \DOMNodeList)
			$v = new nodeList($v);
		return $v;
	}
	function getElementById($id,$attr = 'id'){
		return $this->query('//*[@'.$attr.'="'.$id.'"]')->item(0);
	}
	function createElement($name,$attrs = null,$text = null){
		$e = new element($this->dd()->createElement($name));
		if(is_array($attrs)) foreach($attrs as $attr => $value) if($value!==null) $e->$attr = $value;
		if($text!==null) $e->text($text);
		return $e;
	}
	function create($name,$attrs = null,$text = null){
		return $this->createElement($name,$attrs,$text);
	}
	function createTextNode($v){
		return $this->dd()->createTextNode($v);
	}
	function xmlInclude($v,$cont = null){
		if($e = $this->importNode($v)){
			if($cont){
				if(is_object($cont) || (
					is_string($cont)
					&& ($cont = $this->query($cont)->item(0))
				)){
					$xeCont = new element($cont);
				}
			}
			if(isset($xeCont) || ($xeCont = $this->de()))
				return $xeCont->append($e);
			return $this->dd()->appendChild($e);
		}else throw new \Exception('Wrong value: "'.htmlspecialchars(print_r($v,1)).'"');
	}
	function transform($xsltTemplate){
		$xsl = new xml($xsltTemplate);
		$proc = new \XSLTProcessor;
		$proc->importStyleSheet($xsl->dd());
		return $proc->transformToXML($this->dd());
	}
	function save($uri = null){
		if(!$uri) $uri = $this->documentURI();
		if($uri){
			if(!file_exists($uri) && is_writable(pathinfo($uri,PATHINFO_DIRNAME)))
				fclose(fopen($uri,'w+'));
			if(file_exists($uri) && is_writable($uri)){
				if(!parse_url($uri,PHP_URL_SCHEME)) $uri = realpath($uri);
				return $this->dd->save($uri);
			}
		}
		return false;
	}
	static function isXMLString($str){
		return is_string($str) && substr($str,0,5)=='<?xml';
	}
	static function elementText(\DOMElement $e,$text = '___UNDEFINED___'){
		if($text === '___UNDEFINED___'){
			$text = '';
			if($e->hasChildNodes()) foreach($e->childNodes as $node)
				if($node instanceof \DOMText) $text.= $node->data;
			return $text;
		}
		foreach($e->childNodes as $chn) if($chn instanceof \DOMText) $e->removeChild($chn);
		$e->appendChild($e->ownerDocument->createTextNode($text));
	}
}