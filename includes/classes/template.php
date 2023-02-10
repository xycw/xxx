<?php
class template {
	function __construct($template_dir='default')
	{
		$this->info = array('default' => $template_dir);
	}

	function get_template_part($page_directory, $template_part, $file_extension='.php')
	{
		$directory_array = array();
		if ($dir = @dir($page_directory)) {
			while ($file = $dir->read()) {
				if (!is_dir($page_directory . $file)) {
					if (substr($file, strrpos($file, '.')) == $file_extension && preg_match($template_part, $file)) {
						$directory_array[] = $file;
					}
				}
			}
			sort($directory_array);
			$dir->close();
		}
		return $directory_array;
	}

	function get_template_dir($template_code, $current_template, $current_page, $template_dir, $debug=false)
	{
		if (substr($current_page, 0, 7) == 'account'
			|| substr($current_page, 0, 7) == 'address') {
			$current_page = 'account';
		}
		if ($this->file_exists($current_template . $current_page, $template_code)) {
			return $current_template . $current_page . '/';
		} elseif ($this->file_exists($current_template . $template_dir, preg_replace('/\//', '', $template_code), $debug)) {
			return $current_template . $template_dir . '/';
		} elseif ($this->file_exists(DIR_WS_CATALOG_TEMPLATES . $this->info['default'] . '/' . $current_page, preg_replace('/\//', '', $template_code), $debug)) {
			return DIR_WS_CATALOG_TEMPLATES . $this->info['default'] . '/' . $current_page . '/';
		} else {
			return DIR_WS_CATALOG_TEMPLATES . $this->info['default'] . '/' . $template_dir . '/';
		}
	}
	
	function file_exists($file_dir, $file_pattern, $debug=false)
	{
		$file_found = false;
		$file_pattern = '/'.str_replace("/", "\/", $file_pattern).'$/';
		if ($mydir = @dir($file_dir)) {
			while ($file = $mydir->read()) {
				if (preg_match($file_pattern, $file)) {
					$file_found = true;
					break;
				}
			}
			$mydir->close();
		}
		return $file_found;
	}
}
