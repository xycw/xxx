<?php
class payment_method {
	private $_code;
	private $_account;
	private $_md5key;
	private $_submit_url;
	private $_return_url;
	private $_is_inside;
	private $_mark1;
	private $_mark2;
	private $_mark3;
	private $_obj;
	
	public function __construct($code)
	{
		global $db;
		$sql = "SELECT code, account, md5key,
					   submit_url, return_url,
					   is_inside, mark1, mark2, mark3
				FROM   " . TABLE_PAYMENT_METHOD . "
				WHERE  code = :code
				LIMIT  1";
		$sql = $db->bindVars($sql, ':code', $code, 'string');
		$result = $db->Execute($sql);
		if ($result->RecordCount()) {
			$this->_code = $result->fields['code'];
			$this->_account = $result->fields['account'];
			$this->_md5key = $result->fields['md5key'];
			$this->_submit_url = $result->fields['submit_url'];
			$this->_return_url = $result->fields['return_url'];
			$this->_is_inside = $result->fields['is_inside'];
			$this->_mark1 = $result->fields['mark1'];
			$this->_mark2 = $result->fields['mark2'];
			$this->_mark3 = $result->fields['mark3'];
			if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $this->_code . '.php')) {
				require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $this->_code . '.php');
				$this->_obj = new $this->_code();
			}
		}
	}
	
	public function before()
	{
		if (is_a($this->_obj, $this->_code) && is_callable(array($this->_obj, 'before'))) {
			return $this->_obj->before($this);
		} else {
			return '';
		}
	}
	
	public function after()
	{
		if (is_a($this->_obj, $this->_code) && is_callable(array($this->_obj, 'after'))) {
			return $this->_obj->after($this);
		} else {
			return false;
		}
	}
	
	public function process()
	{
		if (is_a($this->_obj, $this->_code) && is_callable(array($this->_obj, 'process'))) {
			$this->_obj->process($this);
		} else {
			redirect(href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL'));
		}
	}
	
	public function result()
	{
		if (is_a($this->_obj, $this->_code) && is_callable(array($this->_obj, 'result'))) {
			return $this->_obj->result($this);
		} else {
			return 1;
		}
	}
	
	public function get_code()
	{
		return $this->_code;
	}
	
	public function get_account()
	{
		return $this->_account;
	}
	
	public function get_md5key()
	{
		return $this->_md5key;
	}
	
	public function get_submit_url()
	{
		return not_null($this->_submit_url)?$this->_submit_url:href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
	}
	
	public function get_return_url()
	{
		return not_null($this->_return_url)?$this->_return_url:href_link(FILENAME_CHECKOUT_RESULT, '', 'SSL');
	}

	public function get_is_inside()
	{
		return $this->_is_inside;
	}
	
	public function get_mark1()
	{
		return $this->_mark1;
	}
	
	public function get_mark2()
	{
		return $this->_mark2;
	}
	
	public function get_mark3()
	{
		return $this->_mark3;
	}
}
