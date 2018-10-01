<?php
namespace pgood\xml;

class siblingList extends element implements \Iterator{
	private $__list;
	private $__position;
	private $__tag;
	function __construct($v,$tag){
		parent::__construct($v);
		$this->__tag = $tag;
		$this->rewind();
	}
	function add($attrs = null,$value = null){
		$res = $this->append($this->xml()->create($this->__tag,$attrs,$value));
		$this->__list = $this->query('./'.$this->__tag);
		return $res;
	}
	function move($posFrom,$posTo){
		if($posFrom < $posTo) $posTo++;
		if($posFrom != $posTo){
			if($posTo >= $this->count()
				&& ($e1 = $this->item($posFrom))
			) return $this->append($e1);
			if(($e1 = $this->item($posFrom))
				&& ($e2 = $this->item($posTo))
			) return $e2->before($e1);
		}
	}
	function item($i){
		if($e = $this->__list->item($i)) return new element($e);
	}
	function count(){
		return $this->__list->length;
	}
	function clear(){
		foreach($this as $e)
			$this->removeChild($e);
		$this->rewind();
	}
	/**
	* Iterator
	*/
	function rewind(){
		$this->__list = $this->query('./'.$this->__tag);
		$this->__position = 0;
	}
	function current(){
		return $this->item($this->__position);
	}
	function key(){
		return $this->__position;
	}
	function next(){
		++$this->__position;
	}
	function valid(){
		return $this->__position < $this->count();
	}
}