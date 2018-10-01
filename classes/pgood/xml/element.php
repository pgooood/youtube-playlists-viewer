<?php
namespace pgood\xml;

class element{
	private $__e;
	
	function __construct($v){
		if($v instanceof \DOMElement) $this->__e = $v;
		elseif($v instanceof element) $this->__e = $v->e();
		else throw new \Exception('Wrong value <pre>'.print_r($v,1).'</pre>');
	}
	function e(){
		return $this->__e;
	}
	function name(){
		return strtolower($this->e()->tagName);
	}
	function xml(){
		return new xml($this->e());
	}
	function query($v){
		return $this->xml()->query($v,$this->e());
	}
	function evaluate($v){
		return $this->xml()->evaluate($v,$this->e());
	}
	function getAttribute($name){
		return $this->e()->getAttribute($name);
	}
	function setAttribute($name,$value = null){
		if(is_array($name))
			foreach($name as $n => $v)
				if($n) $this->setAttribute($n,$v);
		if(!is_scalar($value) && !is_null($value))
			throw new \Exception('Value must be scalar');
		return $this->e()->setAttribute($name,$value);
	}
	function hasAttribute($name){
		return $this->e()->hasAttribute($name);
	}
	function removeAttribute($name){
		return $this->e()->removeAttribute($name);
	}
	function text($v = false){
		$e = $this->e();
		if($v !== false){
			foreach($e->childNodes as $n) if($n instanceof \DOMText) $e->removeChild($n);
			$e->appendChild($this->xml()->createTextNode($v));
		}
		$v = '';
		if($e->hasChildNodes())
			foreach($e->childNodes as $n) if($n instanceof \DOMText) $v.= $n->data;
		return $v;
	}
	function append($v){
		if(is_string($v))
			$v = $this->xml()->create($v);
		$eNative = $this->isNative($v);
		if((
				($eNative && ($n = $eNative->e()))
				|| ($n = $this->importNode($v))
			)
			&& ($n = $this->e()->appendChild($n))
		){
			if($eNative && $v instanceof element)
				return $v;
			if($n instanceof \DOMElement)
				return new element($n);
			return $n;
		}
	}
	function before($v){
		if(is_string($v))
			$v = $this->xml()->create($v);
		if($n = $this->importNode($v)){
			$n = $this->e()->parentNode->insertBefore($n,$this->e());
			if($n instanceof \DOMElement)
				return new element($n);
			return $n;
		}
	}
	function insertBefore($v,$refnode = null){
		if(is_string($v))
			$v = $this->xml()->create($v);
		if($n = $this->importNode($v)){
			if($refnode && is_object($refnode)){
				if($refnode instanceof element)
					$refnode = $refnode->e();
				if($refnode instanceof \DOMNode)
					$n = $this->e()->insertBefore($n,$refnode);
			}else
				$n = $this->append($n);
			if($n instanceof \DOMElement)
				return new element($n);
			return $n;
		}
	}
	function after($v){
		if($n = $this->next())
			return $this->parent()->insertBefore($v,$n);
		return $this->parent()->append($v);
	}
	function insertAfter($v,$refnode = null){
		if($refnode && ($n = $refnode->next()))
			return $this->insertBefore($v,$n);
		return $this->append($v);
	}
	function parent(){
		if($e = $this->e()->parentNode)
			return new element($e);
	}
	function next(){
		if($n = $this->e()->nextSibling){
			if($n instanceof \DOMElement)
				return new element($n);
			else
				return $n;
		}
	}
	function prev(){
		if($n = $this->e()->previousSibling){
			if($n instanceof \DOMElement)
				return new element($n);
			else
				return $n;
		}
	}
	function remove(){
		return $this->e()->parentNode->removeChild($this->e());
	}
	function removeChild($v){
		$n = null;
		if($v instanceof \DOMNode) $n = $v;
		elseif($v instanceof element) $n = $v->e();
		if($n) return $this->e()->removeChild($n);
	}
	function cloneNode($deep = false){
		if($e = $this->e()->cloneNode($deep))
			return new element($e);
	}
	/*
	* Возращает обект переданного элемента, если элемент принадлежит к тому же документу
	*/
	private function isNative($v){
		$e = null;
		if($v instanceof \DOMElement) $e = new element($v);
		elseif($v instanceof element) $e = $v;
		if($e && $e->e()->ownerDocument->isSameNode($this->e()->ownerDocument))
			return $e;
	}
	private function importNode($v){
		$xml = $this->xml();
		$n = null;
		if($v instanceof \DOMNode) $n = $v;
		elseif($v instanceof element) $n = $v->e();
		elseif($v instanceof xml && $v->de()) $n = $v->de()->e();
		elseif(is_string($v)){
			try{
				$n = $xml->create($v);
			}catch(\DOMException $e){}
		}
		if($n) return $xml->importNode($n);
	}
	function __set($name,$value){
		switch($name){
			case 'firstChild': return;
		}
		if($value===null || $value===false){
			if($this->hasAttribute($name))
				$this->removeAttribute($name);
		}elseif(is_scalar($value))
			$this->setAttribute($name,$value===true ? $name : $value);
	}
	function __get($name){
		switch($name){
			case 'firstChild':
				return new element($this->e()->firstChild);
		}
		return $this->getAttribute($name);
	}
	function __isset($name){
		return $this->e()->hasAttribute($name);
	}
	function __unset($name){
		if($this->e()->hasAttribute($name))
			$this->e()->removeAttribute($name);
	}
}