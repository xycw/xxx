<?php
class breadcrumb {
	private $_trail;
	
	public function __construct()
	{
		$this->reset();
	}
	
	public function reset()
	{
		$this->_trail = array();
	}
	
	public function add($title, $class = '', $link = '')
	{
		$this->_trail[] = array('title' => $title, 'link' => $link, 'class' => $class);
	}
	
	public function trail()
	{
		if (count($this->_trail)) {
			return $this->_trail;
		}
		return false;
	}
}
