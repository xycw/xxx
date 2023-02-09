<?php
if (!defined(PRODUCT_LIST_SORT)) @define('PRODUCT_LIST_SORT', 'position_asc');
if (!defined(ADDITIONAL_IMAGE_LIMIT)) @define('ADDITIONAL_IMAGE_LIMIT', 10);

$sp_category_list   = array(); // 返回结果数据列表
$temp_category_list = $category_tree->getData();
$cid                = isset($_GET['cID']) ? $_GET['cID'] : 0; // 默认一级分类
$temp_category_list = isset($temp_category_list[$cid]) ? $temp_category_list[$cid] : array();
$temp_class         = 1;

if ($cid > 0 && isset($categoryInfo)) {
	// 无子类就有产品，有子类无产品
	if (empty($temp_category_list)) {
		$temp_category_list[] = array(
			'id'          => $categoryInfo['category_id'],
			'name'        => $categoryInfo['name'],
			'nameAlt'     => $categoryInfo['nameAlt'],
			'description' => $categoryInfo['description'],
			'image'       => $categoryInfo['image']
		);
	}

	$categoryInfo['banner_list'] = array();
	$categoryInfo['size_list']   = array();

	// 获取图片数组
	if (!empty($categoryInfo['image'])) {
		if (strrpos($categoryInfo['image'], '/')) {
			$temp_image_directory = substr($categoryInfo['image'], 0, strrpos($categoryInfo['image'], '/') + 1);
		} else {
			$temp_image_directory = '';
		}

		$temp_image_extension = substr($categoryInfo['image'], strrpos($categoryInfo['image'], '.')); // 后缀
		$temp_image_match     = str_replace(array($temp_image_directory, $temp_image_extension), '', $categoryInfo['image']); // 匹配
		$temp_image_path      = DIR_FS_CATALOG_IMAGES . 'banners/' . $temp_image_directory;

		// 匹配banner
		if ($dir = @dir($temp_image_path)) {
			while ($file = $dir->read()) {
				if ($file == '.' || $file == '..') continue;
				if (!is_dir($temp_image_path . $file)) {
					if (substr($file, strrpos($file, '.')) == $temp_image_extension) {
						if (preg_match("/" . $temp_image_match . "/i", $file) == 1) {
							if ($temp_image_match . str_replace($temp_image_match, '', $file) == $file) {
								if (count($categoryInfo['banner_list']) >= ADDITIONAL_IMAGE_LIMIT) break;
								$categoryInfo['banner_list'][] = 'banners/' . $temp_image_directory . $file;
							}
						}
					}
				}
			}
			sort($categoryInfo['banner_list']);
			$dir->close();
		}

		$temp_image_match .= '_';
		$temp_image_path   = DIR_FS_CATALOG_IMAGES . 'sizes/' . $temp_image_directory;

		// 匹配size
		if ($dir = @dir($temp_image_path)) {
			while ($file = $dir->read()) {
				if ($file == '.' || $file == '..') continue;
				if (!is_dir($temp_image_path . $file)) {
					if (substr($file, strrpos($file, '.')) == $temp_image_extension) {
						if (preg_match("/" . $temp_image_match . "/i", $file) == 1) {
							if ($temp_image_match . str_replace($temp_image_match, '', $file) == $file) {
								if (count($categoryInfo['size_list']) >= ADDITIONAL_IMAGE_LIMIT) break;
								$temp_size_name = str_replace(array($temp_image_match, $temp_image_extension), '', $file);
								$categoryInfo['size_list'][$temp_size_name] = 'sizes/' . $temp_image_directory . $file;
							}
						}
					}
				}
			}
			$dir->close();
		}
	}
}

if (!empty($temp_category_list)) {

	// 获取产品
	$temp_order_list = array(
		'position_asc'    => ' ORDER BY sort_order ASC',
		'ordered_desc'    => ' ORDER BY ordered DESC, sort_order ASC',
		'date_added_desc' => ' ORDER BY date_added DESC, sort_order ASC',
		'price_asc'       => ' ORDER BY IF(specials_price, specials_price, price) ASC, sort_order ASC',
		'price_desc'      => ' ORDER BY IF(specials_price, specials_price, price) DESC, sort_order ASC',
		'viewed_desc'     => ' ORDER BY viewed DESC, sort_order ASC',
	);
	$sql = "SELECT product_id, sku, name, price, master_category_id, filter_1, image, specials_price
			FROM   " . TABLE_PRODUCT . "
			WHERE  status = 1
			AND    in_stock = 1";

	if ($cid > 0) {
		// 获取该分类下所有子级分类
		$temp_sub_cids = $category_tree->getSubcategories(array(), $cid);
		$temp_sub_cids[] = $cid;
		$temp_sub_cids = implode(',', $temp_sub_cids);
		$sql .= " AND master_category_id IN ({$temp_sub_cids})";
	} else {
		$sql .= ' AND master_category_id > 0';
	}

	$sql   .= $temp_order_list[PRODUCT_LIST_SORT];
	$result = $db->Execute($sql, false, true, 604800);
	$temp_product_list = array();
	while (!$result->EOF) {
		$result->fields['color_image'] = '';
		if (!empty($result->fields['image'])) {
			$temp_color_image = 'colors/' . $result->fields['image'];

			if (is_file(ROOT_IMAGE . $temp_color_image)) {
				$result->fields['color_image'] = $temp_color_image;
			} elseif (is_file(DIR_FS_CATALOG_IMAGES . $temp_color_image)) {
				$result->fields['color_image'] = $temp_color_image;
			} else {
				$result->fields['color_image'] = $result->fields['image'];
			}
		}

		$result->fields['additional_image'] = get_additional_image($result->fields['image']);
		$result->fields['attribute']        = get_product_attribute($result->fields['product_id']);
		$result->fields['save_off']         = ($_isShowSaveOff==1?round(100-($result->fields['specials_price']/$result->fields['price']*100)):0);

		if ($temp_class == 1 && !empty($result->fields['attribute'])) {
			$temp_class = 2;
		}

		$temp_product_list[$result->fields['product_id']] = $result->fields;
		$result->MoveNext();
	}

	if (!empty($temp_product_list)) {
		foreach ($temp_category_list as $key => $val) {
			$sp_category_list[$val['id']] = array(
				'id'           => $val['id'],
				'name'         => $val['name'],
				'nameAlt'      => $val['nameAlt'],
				'description'  => $val['description'],
				'image'        => $val['image'],
				'product_list' => array()
			);

			// 键名为类目ID, 键值为1级类目ID
			$temp_category_ids[$val['id']] = $val['id'];

			// 子级类目
			$temp = $category_tree->getSubcategories(array(), $val['id']);
			if (!empty($temp)) {
				foreach ($temp as $v) {
					$temp_category_ids[$v] = $val['id'];
				}
			}
		}

		foreach ($temp_product_list as $product) {
			if (isset($temp_category_ids[$product['master_category_id']])) {
				$sp_category_list[$temp_category_ids[$product['master_category_id']]]['product_list'][] = $product;
			}
		}

		// 删除没有产品的分类
		foreach ($sp_category_list as $key => $val) {
			if (empty($val['product_list'])) {
				unset($sp_category_list[$key]);
				continue;
			}
		}

		if (count($sp_category_list) > 1) {
			$temp_class++;
		}
	}
}
