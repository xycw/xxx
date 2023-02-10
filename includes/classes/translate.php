<?php
class translate {
	private $_data = array();
	private $_lineLength= 0;
    private $_delimiter = ',';
    private $_enclosure = '"';
	
	public function __construct()
	{
		$file = DIR_FS_CATALOG_LANGUAGES . STORE_LANGUAGE . '/translate.csv';
		if (file_exists($file)) {
			$fh = fopen($file, 'r');
	        while ($rowData = fgetcsv($fh, $this->_lineLength, $this->_delimiter, $this->_enclosure)) {
	        	if (isset($rowData[0])) {
	        	    $this->_data[$rowData[0]] = isset($rowData[1]) ? $rowData[1] : null;
	        	}
	        }
	        fclose($fh);
		}
	}
	
	public function translate($args)
	{
		$text = array_shift($args);
		if (is_string($text) && ''==$text
            || is_null($text)
            || is_bool($text) && false===$text
            || is_object($text)) {
            return '';
        }
	 	if (array_key_exists($text, $this->_data)) {
            $translated = $this->_data[$text];
        } else {
            $translated = $text;
        }
        $result = @vsprintf($translated, $args);
        if ($result === false) {
            $result = $translated;
        }

        return $result;
	}
}
