<?php
class seo_url {
	private $_data = array();
	private $_attributes = array();
	
	public function __construct()
	{
		$this->_attributes = array(
			'URL_REWRITE_TYPE' => defined('URL_REWRITE_TYPE')?URL_REWRITE_TYPE:0,
			'URL_REWRITE_CONNECTOR' => defined('URL_REWRITE_CONNECTOR')?URL_REWRITE_CONNECTOR:'-',
			'URL_REWRITE_CATEGORY_LEVEL' => defined('URL_REWRITE_CATEGORY_LEVEL')?URL_REWRITE_CATEGORY_LEVEL:0,
			'URL_REWRITE_PRODUCT_LEVEL' => defined('URL_REWRITE_PRODUCT_LEVEL')?URL_REWRITE_PRODUCT_LEVEL:0,
			'URL_REWRITE_CATEGORY_HTML' => defined('URL_REWRITE_CATEGORY_HTML')?URL_REWRITE_CATEGORY_HTML:0,
			'URL_REWRITE_PRODUCT_HTML' => defined('URL_REWRITE_PRODUCT_HTML')?URL_REWRITE_PRODUCT_HTML:0
		);
		$this->generate_category_name();
		$this->generate_product_name();
		$this->generate_cms_page_name();
		if ($this->_attributes['URL_REWRITE_TYPE'] == 2 && isset($_GET['main_page'])) {
			if (in_array($_GET['main_page'], $this->_data['category'])) {
				$_GET['cID'] = array_search($_GET['main_page'], $this->_data['category']);
				$_GET['main_page'] = 'category';
			} elseif (in_array($_GET['main_page'], $this->_data['product'])) {
				$_GET['pID'] = array_search($_GET['main_page'], $this->_data['product']);
				$_GET['main_page'] = 'product';
			} elseif (in_array($_GET['main_page'], $this->_data['cms_page'])) {
				$_GET['cpID'] = array_search($_GET['main_page'], $this->_data['cms_page']);
				$_GET['main_page'] = 'cms_page';
			}
		}
	}
	
	public function href_link($main_page='index', $parameters='', $connection='NOSSL')
	{
		if ($connection == 'SSL' && ENABLE_SSL == 'true') {
			$link = HTTPS_SERVER;
	    } else {
	    	$link = HTTP_SERVER;
	    }
		$link .= DIR_WS_CATALOG;
		
		if (strstr($parameters, '=')) {
			$p = explode('&', $parameters);
			krsort($p);
			foreach ($p as $val) {
				if (strstr($val, '=')) {
					$p1 = explode('=', $val);
					$p2[$p1[0]] = $p1[1];
				}
			}
			unset($p2['']);
			unset($p);
		}
		switch ($main_page) {
			case FILENAME_INDEX:
			break;
			case FILENAME_CATEGORY:
				if (isset($p2['cID'])) {
					$cID = $p2['cID'];
					unset($p2['cID']);
					$link .= $this->_data['category'][$cID] . ($this->_attributes['URL_REWRITE_TYPE']==2? '':'-c_' . $cID) . '.html';
				}
			break;
			case FILENAME_CMS_PAGE:
				if (isset($p2['cpID'])) {
					$cpID = $p2['cpID'];
					unset($p2['cpID']);
					$link .= $this->_data['cms_page'][$cpID] . ($this->_attributes['URL_REWRITE_TYPE']==2?'':'-cp_' . $cpID) . '.html';
				}
			break;
			case FILENAME_PRODUCT:
				if (isset($p2['pID'])) {
					$pID = $p2['pID'];
					unset($p2['pID']);
					$link .= $this->_data['product'][$pID] . ($this->_attributes['URL_REWRITE_TYPE']==2?'':'-p_' . $pID) . '.html';
				}
			break;
			default:
				$link .= $main_page . '.html';
			break;
		}
		
		if (strstr($link, '?')) {
			$separator = '&';
		} else {
			$separator = '?';
		}
		
		if (isset($p2)) {
			foreach ($p2 as $key => $val) {
				$p[] = $key . '=' . $val;
			}
			
			if (isset($p)) {
				$link .= $separator . implode('&', $p);
			}
		}
		return $link;
	}
	
	public function generate_category_name()
	{
		global $db;
		$sql = "SELECT category_id, name, url, parent_id FROM " . TABLE_CATEGORY;
		$result = $db->Execute($sql, false, true, 604800);
		$data = array();
		while (!$result->EOF) {
			$data[$result->fields['parent_id']][] = array(
				'id'   => $result->fields['category_id'],
				'name' => $this->_strip($result->fields['name']),
				'url'  => $result->fields['url']
			);
			$result->MoveNext();
		}
		$this->_data['category'] = array();
		$this->_generate_category_name(0, $data);
	}
	
	private function _generate_category_name($parent_id, $data)
	{
		if (isset($data[$parent_id])) {
			foreach ($data[$parent_id] as $category) {
				if ($parent_id>0
					&& $this->_attributes['URL_REWRITE_CATEGORY_LEVEL']==1) {
						
					$this->_data['category'][$category['id']] = $this->_data['category'][$parent_id] . '/' . (strlen($category['url']) > 0 ? $category['url']:$category['name']);
				} else {
					$this->_data['category'][$category['id']] = (strlen($category['url']) > 0 ? $category['url']:$category['name']);
				}
				if (isset($data[$category['id']])) {
					$this->_generate_category_name($category['id'], $data);
				}
			}
		}
	}
	
	public function generate_product_name()
	{
		global $db;
		$sql = "SELECT product_id, name, url, master_category_id FROM " . TABLE_PRODUCT;
		$result = $db->Execute($sql, false, true, 604800);
		$this->_data['product'] = array();
		while (!$result->EOF) {
			if ($this->_attributes['URL_REWRITE_PRODUCT_LEVEL']==1) {
				$this->_data['product'][$result->fields['product_id']] = $this->_data['category'][$result->fields['master_category_id']] . '/' . (strlen($result->fields['url']) > 0 ? $result->fields['url']:$this->_strip($result->fields['name']));
			} else {
				$this->_data['product'][$result->fields['product_id']] = (strlen($result->fields['url']) > 0 ? $result->fields['url']:$this->_strip($result->fields['name']));
			}
			$result->MoveNext();
		}
	}
	
	public function generate_cms_page_name()
	{
		global $db;
		$sql = "SELECT cms_page_id, name FROM " . TABLE_CMS_PAGE;
		$result = $db->Execute($sql, false, true, 604800);
		$this->_data['cms_page'] = array();
		while (!$result->EOF) {
			$this->_data['cms_page'][$result->fields['cms_page_id']] = $this->_strip($result->fields['name']);
			$result->MoveNext();
		}
	}
	
	private function _strip($string)
	{
		$pattern = "([[:punct:]])";
		$anchor = preg_replace($pattern, '', strtolower($string));
		$pattern = "([[:space:]]|[[:blank:]])";
		$anchor = preg_replace($pattern, $this->_attributes['URL_REWRITE_CONNECTOR'], $anchor);
		if ($this->_attributes['URL_REWRITE_TYPE']==1) $anchor = md5(STORE_WEBSITE . $anchor);
		
		return $anchor;
	}
}
