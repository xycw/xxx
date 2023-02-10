<?php
// 去除所有空格(传过来的格式是"1,2,3,4,5;6,7,8,9,10;11,12,13,14,15")
if (!defined('INDEX_CATEGORY_LIST')) define('INDEX_CATEGORY_LIST', '');
$categoryList = preg_replace('/\s/', '', INDEX_CATEGORY_LIST);
$categoryList = explode(';', $categoryList);
// 自定义
$indexCategoryList = array();
$categoryIds   = array();
$categoryArray = array();
$categoryData  = array();

if (!empty($categoryList)) {
	foreach ($categoryList as $key => $val) {
		$tem = explode(',', $val);
		if (!empty($tem)) {
			foreach ($tem as $k => $v) {
				if (!is_numeric($v) || $v < 1) {
					continue;
				}
				$categoryIds[] = $v;
				$categoryArray[$tem[0]][] = $v;
			}
			unset($categoryArray[$tem[0]][0]);
		}
	}
	
	if (!empty($categoryIds)) {
		// 获取类目数据
		$sql = "SELECT category_id, name, image
				FROM   " . TABLE_CATEGORY . "
				WHERE  status = 1
				AND category_id IN (:categoryIDS)";
		$sql = $db->bindVars($sql, ':categoryIDS', implode(',', $categoryIds), 'noquotestring');
		$result = $db->Execute($sql, false, true, 604800);
		while (!$result->EOF) {
			$categoryData[$result->fields['category_id']] = $result->fields;
			$result->MoveNext();
		}
		// 拼接
		foreach ($categoryArray as $key => $val) {
			if (!isset($categoryData[$key])) {
				continue;
			}
			$childrenList = array();
			if (!empty($val)) {
				foreach ($val as $v) {
					if (isset($categoryData[$v])) {
						$childrenList[] = array(
							'category_id' => $categoryData[$v]['category_id'],
							'nameAlt'     => output_string($categoryData[$v]['name']),
							'name'        => $categoryData[$v]['name'],
							'image'       => $categoryData[$v]['image']
						);
					}
				}
			}
			$indexCategoryList[$categoryData[$key]['category_id']] = array(
				'category_id' => $categoryData[$key]['category_id'],
				'nameAlt'     => output_string($categoryData[$key]['name']),
				'name'        => $categoryData[$key]['name'],
				'image'       => $categoryData[$key]['image'],
				'children'    => $childrenList
			);
		}
	}
}

// 释放
unset($categoryIds);
unset($categoryArray);
unset($categoryData);
