<?php
class currencies {
	private $_code;
	private $_data;
	
	public function __construct($code)
	{
		global $db;
		$this->_code = $code;
		$sql = "SELECT name, code, symbol_left, symbol_right, decimal_point,
					   thousands_point, decimal_places, value
				FROM   " . TABLE_CURRENCY . "
				ORDER BY sort_order";
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$this->_data[$result->fields['code']] = array(
				'name'            => $result->fields['name'],
				'symbol_left'     => $result->fields['symbol_left'],
				'symbol_right'    => $result->fields['symbol_right'],
				'thousands_point' => $result->fields['thousands_point'],
				'decimal_point'   => $result->fields['decimal_point'],
				'decimal_places'  => $result->fields['decimal_places'],
				'value'           => $result->fields['value']
			);
			$result->MoveNext();
		}
	}
	
	public function getData()
	{
		return $this->_data;
	}
	
	public function display_price($price, $code='', $value='')
	{
		if ($code == '' || !array_key_exists($code, $this->_data)) $code = $this->_code;
		if ($value == '') $value = $this->_data[$code]['value'];
		return $this->_data[$code]['symbol_left'] . number_format($price * $value, $this->_data[$code]['decimal_places'], $this->_data[$code]['decimal_point'], $this->_data[$code]['thousands_point']) . $this->_data[$code]['symbol_right'];
	}
	
	public function get_price($price, $code='', $value='')
	{
		if ($code == '' || !array_key_exists($code, $this->_data)) $code = $this->_code;
		if ($value == '') $value = $this->_data[$code]['value'];
		return number_format($price * $value, $this->_data[$code]['decimal_places'], $this->_data[$code]['decimal_point'], '');
	}
	
	public function get_code()
	{
		return $this->_code;
	}
	
	public function get_value($code='')
	{
		if ($code == '' || !array_key_exists($code, $this->_data)) $code = $this->_code;
		return $this->_data[$code]['value'];
	}
}
