<?php
class category_tree {
	private $_data = array();
	private $_category = array();
	private $_parent_group_start_string = '<ul%s>';
	private $_parent_group_end_string = '</ul>';
	private $_child_start_string = '<li%s>';
	private $_child_end_string = '</li>';
	
	public function __construct()
	{
		global $db;
		$sql = "SELECT category_id, template_dir, name, description,
					   image, parent_id, top
				FROM   " . TABLE_CATEGORY . "
				WHERE  status = 1
				ORDER BY sort_order, name";
		$result = $db->Execute($sql, false, true, 604800);
		while (!$result->EOF) {
			$this->_data[$result->fields['parent_id']][] = array(
				'id'          => $result->fields['category_id'],
				'name'        => $result->fields['name'],
				'nameAlt'     => output_string($result->fields['name']),
				'description' => $result->fields['description'],
				'image'       => $result->fields['image'],
				'top'         => $result->fields['top'] ? true : false
			);
			$this->_category[$result->fields['category_id']] = array(
				'name'         => $result->fields['name'],
				'parent_id'    => $result->fields['parent_id'],
				'template_dir' => $result->fields['template_dir']
			);
			$result->MoveNext();
		}
		$parcategories = array();
		if (isset($_GET['cID'])) {
			$parcategories   = $this->getParcategories('', $_GET['cID']);
			$parcategories[] = $_GET['cID'];
		}
		if (!empty($this->_data)) {
			foreach ($this->_data as $key => $val) {
				foreach ($val as $k => $v) {
					$this->_data[$key][$k]['current'] = in_array($v['id'], $parcategories) ? true : false;
				}
			}
		}
	}
	
	private function _buildBranch($parent_id, $level=0, $max_level, $show_all=false, $isTop = false)
	{
		$level++;
		$result = sprintf($this->_parent_group_start_string, ' class="level' . $level . '"');
		
		if (isset($this->_data[$parent_id])) {
			foreach ($this->_data[$parent_id] as $category) {
				if ($isTop && !$category['top']) {
					continue;
				}
				
				if (isset($this->_data[$category['id']])) {
					$result .= sprintf($this->_child_start_string, ' class="category-top"');
				} else {
					$result .= sprintf($this->_child_start_string, ' class="category-product"');
				}
				
				$result .= '<a class="level' . $level . ' nav-' . $category['id'] . ($category['current']==true?' current':'') . '" href="' . href_link(FILENAME_CATEGORY, 'cID=' . $category['id']) . '"><span>' . $category['name'] . '</span></a>';
				
				if (isset($this->_data[$category['id']])) {
					$result .= '<span class="icon-sub-menu"></span>';
				}
				
				if (isset($this->_data[$category['id']])
					&& (($max_level=='0') || ($max_level>$level))
					&& ($show_all==true || $category['current']==true)) {
					$result .= $this->_buildBranch($category['id'], $level, $max_level, $show_all, $isTop);
				}
				$result .= $this->_child_end_string;
			}
		}
		
		$result .= $this->_parent_group_end_string;
		
		return $result;
	}
	
	public function buildTree($parent_id=0, $max_level=5)
	{
		return $this->_buildBranch($parent_id, 0, $max_level, false);
	}
	
	public function buildAllTree($parent_id=0, $max_level=5)
	{
		return $this->_buildBranch($parent_id, 0, $max_level, true);
	}
	
	public function buildHeaderTree($parent_id=0, $max_level=5)
	{
		return $this->_buildBranch($parent_id, 0, $max_level, true, true);
	}

	public function getData()
	{
		return $this->_data;
	}
	
	public function getTemplateDir($category_id)
	{
		$template_dir = 'default';
		if (isset($this->_category[$category_id])) {
			$category = $this->_category[$category_id];
			$template_dir = $category['template_dir'];
			if ($category['parent_id'] == '0') {
				return $template_dir;
			}

			if ($template_dir == 'default') {
				return $this->getTemplateDir($category['parent_id']);
			}
		}

		return $template_dir;
	}

	public function getParcategory($category_id)
	{
		return isset($this->_category[$category_id]['parent_id']) ? $this->_category[$category_id]['parent_id'] : false;
	}

	public function getCategoryName($category_id)
	{
		return isset($this->_category[$category_id]['name']) ? $this->_category[$category_id]['name'] : false;
	}

	public function getParcategories($parcategories, $category_id)
	{
		if (!is_array($parcategories)) $parcategories = array();
		if (isset($this->_category[$category_id])) {
			$category = $this->_category[$category_id];
			if ($category['parent_id'] != 0) {
				array_unshift($parcategories, $category['parent_id']);
				if ($category['parent_id'] != $category_id) {
					$parcategories = $this->getParcategories($parcategories, $category['parent_id']);
				}
			}
		}

		return $parcategories;
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
