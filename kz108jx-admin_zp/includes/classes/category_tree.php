<?php
class category_tree {
	private $_data = array();
	private $_db;
	
	public function __construct()
	{
		global $db;
		$this->_db = $db;
		$sql = "SELECT category_id, name,
					   parent_id, sort_order, status, top
				FROM   " . TABLE_CATEGORY . "
				ORDER BY sort_order, name";
		$result = $this->_db->Execute($sql);
		while (!$result->EOF) {
			$this->_data[$result->fields['parent_id']][] = array(
				'id'         => $result->fields['category_id'],
				'name'       => $result->fields['name'],
				'sort_order' => $result->fields['sort_order'],
				'top'        => $result->fields['top'],
				'status'     => $result->fields['status']
			);
			$result->MoveNext();
		}
	}
	
	private function _build($parent_id, $result = array())
	{
		if (isset($this->_data[$parent_id])) {
			foreach ($this->_data[$parent_id] as $category) {
				if ($parent_id==0) {
					$result[$category['id']]['name'] = $category['name'];
				} else {
					$result[$category['id']]['name'] = $result[$parent_id]['name'] . ' > ' . $category['name'];
				}
				$result[$category['id']]['sort_order'] = $category['sort_order'];
				$result[$category['id']]['status']     = $category['status'];
				$result[$category['id']]['top']        = $category['top'];
				if (isset($this->_data[$category['id']])) {
					$result = $this->_build($category['id'], $result);
				}
			}
		}
		return $result;
	}
	
	public function getTree()
	{
		return $this->_build(0);
	}
	
	public function getSubcategories($subcategories, $parent_id = 0)
	{
		if (!is_array($subcategories)) $subcategories = array();
		if (isset($this->_data[$parent_id])) {
			foreach ($this->_data[$parent_id] as $category) {
				array_unshift($subcategories, $category['id']);
				if (isset($this->_data[$category['id']])) {
					$subcategories = $this->getSubcategories($subcategories, $category['id']);
				}
			}
		}

		return $subcategories;
	}
}
