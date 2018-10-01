<?php
namespace pgood\xml;

class nodeList implements \Iterator{
	public $nl;
	private $i;
	function __construct(\DOMNodeList $nl){
		$this->nl = $nl;
		$this->i = 0;
	}
	function __get($name){
		if($name = 'length') return $this->nl->length;
	}
	function item($i){
		$v = $this->nl->item($i);
		switch(true){
			case $v instanceof \DOMElement:
				return new element($v);
			default:
				return $v;
		}
	}
	function rewind(){
		$this->i = 0;
	}
	function current(){
		return $this->item($this->i);
	}
	function key(){
		return $this->i;
	}
	function next(){
		$this->i++;
	}
	function valid(){
		return $this->i < $this->nl->length;
	}
}