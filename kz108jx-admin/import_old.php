<?php require('includes/application_top.php'); ?>
<?php
//清空
if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'clearOrder':
			$db->Execute("TRUNCATE " . TABLE_ADDRESS . ";");
			$db->Execute("TRUNCATE " . TABLE_CUSTOMER . ";");
			$db->Execute("TRUNCATE " . TABLE_ORDERS . ";");
			$db->Execute("TRUNCATE " . TABLE_ORDER_PRODUCT . ";");
			$db->Execute("TRUNCATE " . TABLE_ORDER_STATUS_HISTORY . ";");
			$message_stack->add_session('import', '用户和订单数据清除成功。', 'success');
			redirect(href_link(FILENAME_IMPORT_OLD));
		break;
		case 'clearProduct':
			$db->Execute("TRUNCATE " . TABLE_CATEGORY . ";");
			$db->Execute("TRUNCATE " . TABLE_PRODUCT . ";");
			$db->Execute("TRUNCATE " . TABLE_PRODUCT_ATTRIBUTE . ";");
			$db->Execute("TRUNCATE " . TABLE_PRODUCT_OPTION . ";");
			$db->Execute("TRUNCATE " . TABLE_PRODUCT_OPTION_VALUE . ";");
			$db->Execute("TRUNCATE " . TABLE_PRODUCT_REVIEW . ";");
			$db->Execute("TRUNCATE " . TABLE_PRODUCT_TO_CATEGORY . ";");
			$message_stack->add_session('import', '分类和产品数据清除成功。', 'success');
			redirect(href_link(FILENAME_IMPORT_OLD));
		break;
	}
}
@set_time_limit(100000000);
@ini_set('max_input_time', '100000000');
$action = isset($_POST['action'])?$_POST['action']:'';
$display = array();
switch ($action) {
	case 'product_option':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} elseif($fields = array_flip(fgetcsv($handle))) {
			$i=0;
			while ($_data = fgetcsv($handle)) {
				$i++;
				foreach ($fields as $key => $val) {
					$$key = db_prepare_input($_data[$val]);
				}
				if (!isset($v_name) || !not_null($v_name)) {
					$display[$i] = '产品属性名称为空';
					continue;
				} else {
					$sql = "SELECT product_option_id FROM " . TABLE_PRODUCT_OPTION . " WHERE name = :name LIMIT 1";
					$sql = $db->bindVars($sql, ':name', $v_name, 'string');
					$result = $db->Execute($sql);
					$product_option_is_new = true;
					if ($result->RecordCount() > 0) {
						$display[$i] = '更新 产品属性名称:' . $v_name;
						$product_option_is_new = false;
						$product_option_id = $result->fields['product_option_id'];
					} else {
						$display[$i] = '新增 产品属性名称:' . $v_name;
						$product_option_is_new = true;
					}
				}
				//product_option
				$sql_data_array = array();
				if ($product_option_is_new) {
					//name
					$sql_data_array[] = array('fieldName'=>'name', 'value'=>$v_name, 'type'=>'string');
					//type
					if (isset($v_type)
						&& in_array($v_type, array('select', 'text', 'radio', 'checkbox', 'list'))) {
					} else {
						$v_type = 'select';
					}
					$sql_data_array[] = array('fieldName'=>'type', 'value'=>$v_type, 'type'=>'string');
					//sort_order
					if (isset($v_sort_order)) {
						$sql_data_array[] = array('fieldName'=>'sort_order', 'value'=>$v_sort_order, 'type'=>'integer');
					}
					$db->perform(TABLE_PRODUCT_OPTION, $sql_data_array);
					$product_option_id = $db->insert_ID();
				} else {
					//name
					if (isset($v_name) && not_null($v_name)) {
						$sql_data_array[] = array('fieldName'=>'name', 'value'=>$v_name, 'type'=>'string');
					}
					//type
					if (isset($v_type)
						&& in_array($v_type, array('select', 'text', 'radio', 'checkbox', 'list'))) {
					} else {
						$v_type = 'select';
					}
					//sort_order
					if (isset($v_sort_order)) {
						$sql_data_array[] = array('fieldName'=>'sort_order', 'value'=>$v_sort_order, 'type'=>'integer');
					}
					$db->perform(TABLE_PRODUCT_OPTION, $sql_data_array, 'UPDATE', 'product_option_id = ' . (int)$product_option_id);
				}
				if ($product_option_id > 0) {
					$db->Execute("DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_option_id = " . (int)$product_option_id);
					$db->Execute("DELETE FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE product_option_id = " . (int)$product_option_id);
					//product_option_value
					if (isset($v_value) && $v_type!='text') {
						$value_array = explode(';', $v_value);
						foreach ($value_array as $value) {
							$tmp = explode(':', $value);
							$error = false;
							if (strlen($tmp[0]) < 1) {
								$error = true;
							} else {
								$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE name = :name AND product_option_id = " . (int)$product_option_id;
								$sql = $db->bindVars($sql, ':name', $tmp[0], 'string');
								$check_product_option_value = $db->Execute($sql);
								if ($check_product_option_value->fields['total'] > 0) {
									$error = true;
								}
							}
							if ($error==true) {
								//nothing
							} else {
								$sql_data_array = array(
									array('fieldName'=>'product_option_id', 'value'=>$product_option_id, 'type'=>'integer'),
									array('fieldName'=>'name', 'value'=>$tmp[0], 'type'=>'string'),
									array('fieldName'=>'sort_order', 'value'=>$tmp[1], 'type'=>'integer')
								);
								$db->perform(TABLE_PRODUCT_OPTION_VALUE, $sql_data_array);
							}
						}
					}
				}
			}
		}
	break;
	case 'category':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} elseif($fields = array_flip(fgetcsv($handle))) {
			$i=0;
			while ($_data = fgetcsv($handle)) {
				$i++;
				foreach ($fields as $key => $val) {
					$$key = db_prepare_input($_data[$val]);
				}
				if (!isset($v_sku) || !not_null($v_sku)) {
					$display[$i] = '分类型号为空';
					continue;
				} else {
					$sql = "SELECT category_id FROM " . TABLE_CATEGORY . " WHERE sku = :sku LIMIT 1";
					$sql = $db->bindVars($sql, ':sku', $v_sku, 'string');
					$result = $db->Execute($sql);
					$category_is_new = true;
					if ($result->RecordCount() > 0) {
						$display[$i] = '更新 分类型号:' . $v_sku;
						$category_is_new = false;
						$category_id = $result->fields['category_id'];
					} else {
						$display[$i] = '新增 分类型号:' . $v_sku;
						$category_is_new = true;
					}
				}
				//parent_category
				$parent_id = 0;
				if (isset($v_parent_sku) && not_null($v_parent_sku)) {
					$sql = "SELECT category_id FROM " . TABLE_CATEGORY . " WHERE sku = :sku LIMIT 1";
					$sql = $db->bindVars($sql, ':sku', $v_parent_sku, 'string');
					$result = $db->Execute($sql);
					if ($result->RecordCount() > 0) {
						$parent_id = $result->fields['category_id'];
					}
				}
				//category
				$sql_data_array = array();
				if ($category_is_new) {
					//sku
					$sql_data_array[] = array('fieldName'=>'sku', 'value'=>$v_sku, 'type'=>'string');
					//name
					if (!isset($v_name) || !not_null($v_name)) {
						$display[$i] .= ' 不存在分类名';
						continue;
					}
					$sql_data_array[] = array('fieldName'=>'name', 'value'=>$v_name, 'type'=>'string');
					//description
					if (isset($v_description)) {
						$sql_data_array[] = array('fieldName'=>'description', 'value'=>$v_description, 'type'=>'string');
					}
					//image
					if (isset($v_image)) {
						$sql_data_array[] = array('fieldName'=>'image', 'value'=>$v_image, 'type'=>'string');
					}
					//url
					if (isset($v_url)) {
						$sql_data_array[] = array('fieldName'=>'url', 'value'=>$v_url, 'type'=>'string');
					}
					//parent_id
					if (isset($parent_id)) {
						$sql_data_array[] = array('fieldName'=>'parent_id', 'value'=>$parent_id, 'type'=>'integer');
					}
					//meta_title
					if (isset($v_meta_title)) {
						$sql_data_array[] = array('fieldName'=>'meta_title', 'value'=>$v_meta_title, 'type'=>'string');
					}
					//meta_keywords
					if (isset($v_meta_keywords)) {
						$sql_data_array[] = array('fieldName'=>'meta_keywords', 'value'=>$v_meta_keywords, 'type'=>'string');
					}
					//meta_description
					if (isset($v_meta_description)) {
						$sql_data_array[] = array('fieldName'=>'meta_description', 'value'=>$v_meta_description, 'type'=>'string');
					}
					//status
					if (isset($v_status)) {
						$sql_data_array[] = array('fieldName'=>'status', 'value'=>$v_status, 'type'=>'integer');
					}
					//sort_order
					if (isset($v_sort_order)) {
						$sql_data_array[] = array('fieldName'=>'sort_order', 'value'=>$v_sort_order, 'type'=>'integer');
					}
					//date_added
					if (isset($v_date_added) && validate_datetime($v_date_added)) {
						$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>$v_date_added, 'type'=>'string');
					} else {
						$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
					}
					$db->perform(TABLE_CATEGORY, $sql_data_array);
					$category_id = $db->insert_ID();
				} else {
					//name
					if (isset($v_name) && not_null($v_name)) {
						$sql_data_array[] = array('fieldName'=>'name', 'value'=>$v_name, 'type'=>'string');
					}
					//description
					if (isset($v_description)) {
						$sql_data_array[] = array('fieldName'=>'description', 'value'=>$v_description, 'type'=>'string');
					}
					//image
					if (isset($v_image)) {
						$sql_data_array[] = array('fieldName'=>'image', 'value'=>$v_image, 'type'=>'string');
					}
					//url
					if (isset($v_url)) {
						$sql_data_array[] = array('fieldName'=>'url', 'value'=>$v_url, 'type'=>'string');
					}
					//parent_id
					if (isset($parent_id)) {
						$sql_data_array[] = array('fieldName'=>'parent_id', 'value'=>$parent_id, 'type'=>'integer');
					}
					//meta_title
					if (isset($v_meta_title)) {
						$sql_data_array[] = array('fieldName'=>'meta_title', 'value'=>$v_meta_title, 'type'=>'string');
					}
					//meta_keywords
					if (isset($v_meta_keywords)) {
						$sql_data_array[] = array('fieldName'=>'meta_keywords', 'value'=>$v_meta_keywords, 'type'=>'string');
					}
					//meta_description
					if (isset($v_meta_description)) {
						$sql_data_array[] = array('fieldName'=>'meta_description', 'value'=>$v_meta_description, 'type'=>'string');
					}
					//status
					if (isset($v_status)) {
						$sql_data_array[] = array('fieldName'=>'status', 'value'=>$v_status, 'type'=>'integer');
					}
					//sort_order
					if (isset($v_sort_order)) {
						$sql_data_array[] = array('fieldName'=>'sort_order', 'value'=>$v_sort_order, 'type'=>'integer');
					}
					//date_added
					if (isset($v_date_added) && validate_datetime($v_date_added)) {
						$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>$v_date_added, 'type'=>'string');
					}
					//last_modified
					$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
					$db->perform(TABLE_CATEGORY, $sql_data_array, 'UPDATE', 'category_id = ' . $category_id);
				}
			}
		}
	break;
	case 'product':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} elseif($fields = array_flip(fgetcsv($handle))) {
			// 过滤
			$productFilterFields = array(
				'filter_1' => 'brand_filter',
				'filter_2' => 'class_filter',
				'filter_3' => 'color_filter',
				'filter_4' => 'gender_filter',
				'filter_5' => 'material_filter',
				'filter_6' => 'origin_filter',
				'filter_7' => 'series_filter',
				'filter_8' => 'spec_filter',
				'filter_9' => 'year_filter'
			);
			$i=0;
			while ($_data = fgetcsv($handle)) {
				$i++;
				foreach ($fields as $key => $val) {
					$$key = db_prepare_input($_data[$val]);
				}
				if (!isset($v_sku) || !not_null($v_sku)) {
					$display[$i] = '产品型号为空';
					continue;
				} else {
					$sql = "SELECT product_id FROM " . TABLE_PRODUCT . " WHERE sku = :sku LIMIT 1";
					$sql = $db->bindVars($sql, ':sku', $v_sku, 'string');
					$result = $db->Execute($sql);
					$product_is_new = true;
					if ($result->RecordCount() > 0) {
						$display[$i] = '更新 产品型号:' . $v_sku;
						$product_is_new = false;
						$product_id = $result->fields['product_id'];
					} else {
						$display[$i] = '新增 产品型号:' . $v_sku;
						$product_is_new = true;
					}
				}
				//category
				$category_id = 0;
				if (isset($v_category_sku) && not_null($v_category_sku)) {
					$sql = "SELECT category_id FROM " . TABLE_CATEGORY . " WHERE sku = :sku LIMIT 1";
					$sql = $db->bindVars($sql, ':sku', $v_category_sku, 'string');
					$result = $db->Execute($sql);
					if ($result->RecordCount() > 0) {
						$category_id = $result->fields['category_id'];
					}
				}
				//product
				$sql_data_array = array();
				if ($product_is_new) {
					//sku
					$sql_data_array[] = array('fieldName'=>'sku', 'value'=>$v_sku, 'type'=>'string');
					//name
					if (!isset($v_name) || !not_null($v_name)) {
						$display[$i] .= ' 不存在产品名';
						continue;
					}
					$sql_data_array[] = array('fieldName'=>'name', 'value'=>$v_name, 'type'=>'string');
					//short_description
					if (isset($v_short_description)) {
						$sql_data_array[] = array('fieldName'=>'short_description', 'value'=>$v_short_description, 'type'=>'string');
					}
					//description
					if (isset($v_description)) {
						$sql_data_array[] = array('fieldName'=>'description', 'value'=>$v_description, 'type'=>'string');
					}
					//image
					if (isset($v_image)) {
						$sql_data_array[] = array('fieldName'=>'image', 'value'=>$v_image, 'type'=>'string');
					}
					//url
					if (isset($v_url)) {
						$sql_data_array[] = array('fieldName'=>'url', 'value'=>$v_url, 'type'=>'string');
					}
					//price
					if (isset($v_price)) {
						$sql_data_array[] = array('fieldName'=>'price', 'value'=>$v_price, 'type'=>'decimal');
					}
					//specials_price
					if (isset($v_specials_price)) {
						$sql_data_array[] = array('fieldName'=>'specials_price', 'value'=>$v_specials_price, 'type'=>'decimal');
					}
					//specials_expire_date
					if (isset($v_specials_expire_date) && validate_date($v_specials_expire_date)) {
						$sql_data_array[] = array('fieldName'=>'specials_expire_date', 'value'=>$v_specials_expire_date, 'type'=>'date');
					}
					//master_category_id
					if (isset($category_id) && $category_id > 0) {
						$sql_data_array[] = array('fieldName'=>'master_category_id', 'value'=>$category_id, 'type'=>'integer');
					} else {
						$display[$i] .= ' 不存在分类';
						continue;
					}
					//meta_title
					if (isset($v_meta_title)) {
						$sql_data_array[] = array('fieldName'=>'meta_title', 'value'=>$v_meta_title, 'type'=>'string');
					}
					//meta_keywords
					if (isset($v_meta_keywords)) {
						$sql_data_array[] = array('fieldName'=>'meta_keywords', 'value'=>$v_meta_keywords, 'type'=>'string');
					}
					//meta_description
					if (isset($v_meta_description)) {
						$sql_data_array[] = array('fieldName'=>'meta_description', 'value'=>$v_meta_description, 'type'=>'string');
					}
					//Product Filter
					foreach ($productFilterFields as $key => $field) {
						$v_tmp = 'v_' . $field;
						if (isset($$v_tmp)) {
							$sql_data_array[] = array('fieldName'=>$key, 'value'=>$$v_tmp, 'type'=>'string');
						}
					}
					//in_stock
					if (isset($v_in_stock)) {
						$sql_data_array[] = array('fieldName'=>'in_stock', 'value'=>$v_in_stock, 'type'=>'integer');
					}
					//status
					if (isset($v_status)) {
						$sql_data_array[] = array('fieldName'=>'status', 'value'=>$v_status, 'type'=>'integer');
					}
					//sort_order
					if (isset($v_sort_order)) {
						$sql_data_array[] = array('fieldName'=>'sort_order', 'value'=>$v_sort_order, 'type'=>'integer');
					}
					//group_name
					if (isset($v_group_name)) {
						$sql_data_array[] = array('fieldName'=>'group_name', 'value'=>$v_group_name, 'type'=>'string');
					}
					//date_added
					if (isset($v_date_added) && validate_datetime($v_date_added)) {
						$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>$v_date_added, 'type'=>'string');
					} else {
						$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
					}
					$db->perform(TABLE_PRODUCT, $sql_data_array);
					$product_id = $db->insert_ID();
				} else {
					//name
					if (isset($v_name) && not_null($v_name)) {
						$sql_data_array[] = array('fieldName'=>'name', 'value'=>$v_name, 'type'=>'string');
					}
					//short_description
					if (isset($v_short_description) && not_null($v_short_description)) {
						$sql_data_array[] = array('fieldName'=>'short_description', 'value'=>$v_short_description, 'type'=>'string');
					}
					//description
					if (isset($v_description)) {
						$sql_data_array[] = array('fieldName'=>'description', 'value'=>$v_description, 'type'=>'string');
					}
					//image
					if (isset($v_image)) {
						$sql_data_array[] = array('fieldName'=>'image', 'value'=>$v_image, 'type'=>'string');
					}
					//url
					if (isset($v_url)) {
						$sql_data_array[] = array('fieldName'=>'url', 'value'=>$v_url, 'type'=>'string');
					}
					//price
					if (isset($v_price)) {
						$sql_data_array[] = array('fieldName'=>'price', 'value'=>$v_price, 'type'=>'decimal');
					}
					//specials_price
					if (isset($v_specials_price)) {
						$sql_data_array[] = array('fieldName'=>'specials_price', 'value'=>$v_specials_price, 'type'=>'decimal');
					}
					//specials_expire_date
					if (isset($v_specials_expire_date) && validate_date($v_specials_expire_date)) {
						$sql_data_array[] = array('fieldName'=>'specials_expire_date', 'value'=>$v_specials_expire_date, 'type'=>'date');
					} else {
						$sql_data_array[] = array('fieldName'=>'specials_expire_date', 'value'=>'NULL', 'type'=>'noquotestring');
					}
					//meta_title
					if (isset($v_meta_title)) {
						$sql_data_array[] = array('fieldName'=>'meta_title', 'value'=>$v_meta_title, 'type'=>'string');
					}
					//meta_keywords
					if (isset($v_meta_keywords)) {
						$sql_data_array[] = array('fieldName'=>'meta_keywords', 'value'=>$v_meta_keywords, 'type'=>'string');
					}
					//meta_description
					if (isset($v_meta_description)) {
						$sql_data_array[] = array('fieldName'=>'meta_description', 'value'=>$v_meta_description, 'type'=>'string');
					}
					//Product Filter
					foreach ($productFilterFields as $key => $field) {
						$v_tmp = 'v_' . $field;
						if (isset($$v_tmp)) {
							$sql_data_array[] = array('fieldName'=>$key, 'value'=>$$v_tmp, 'type'=>'string');
						}
					}
					//in_stock
					if (isset($v_in_stock)) {
						$sql_data_array[] = array('fieldName'=>'in_stock', 'value'=>$v_in_stock, 'type'=>'integer');
					}
					//status
					if (isset($v_status)) {
						$sql_data_array[] = array('fieldName'=>'status', 'value'=>$v_status, 'type'=>'integer');
					}
					//sort_order
					if (isset($v_sort_order)) {
						$sql_data_array[] = array('fieldName'=>'sort_order', 'value'=>$v_sort_order, 'type'=>'integer');
					}
					//group_name
					if (isset($v_group_name)) {
						$sql_data_array[] = array('fieldName'=>'group_name', 'value'=>$v_group_name, 'type'=>'string');
					}
					//date_added
					if (isset($v_date_added) && validate_datetime($v_date_added)) {
						$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>$v_date_added, 'type'=>'string');
					}
					//last_modified
					$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
					$db->perform(TABLE_PRODUCT, $sql_data_array, 'UPDATE', 'product_id = ' . $product_id);
				}
				//product_to_category
				if (isset($category_id) && $category_id > 0) {
					$sql_data_array = array(
						array('fieldName'=>'product_id', 'value'=>$product_id, 'type'=>'integer'),
						array('fieldName'=>'category_id', 'value'=>$category_id, 'type'=>'integer')
					);
					$db->perform(TABLE_PRODUCT_TO_CATEGORY, $sql_data_array);
				}
				//product_attribute
				if (isset($v_attribute)) {
					$sql = "DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_id = :productID";
					$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
					$db->Execute($sql);
					$attr_array = explode(';', $v_attribute);
					foreach ($attr_array as $attr) {
						$tmp = explode('#', $attr);
						$sql = "SELECT product_option_id, type
								FROM " . TABLE_PRODUCT_OPTION . "
								WHERE name = :name";
						$sql = $db->bindVars($sql, ':name', $tmp[0], 'string');
						$optionResult = $db->Execute($sql);
						if ($optionResult->RecordCount() > 0) {
							if ($optionResult->fields['type'] == 'text') {
								$sql_data_array = array(
									array('fieldName'=>'product_id', 'value'=>$product_id, 'type'=>'integer'),
									array('fieldName'=>'product_option_id', 'value'=>$optionResult->fields['product_option_id'], 'type'=>'integer'),
									array('fieldName'=>'product_option_value_id', 'value'=>0, 'type'=>'integer')
								);
								$db->perform(TABLE_PRODUCT_ATTRIBUTE, $sql_data_array);
							} elseif (isset($tmp[1])) {
								$optionValue = explode(':', $tmp[1]);
								foreach ($optionValue as $val) {
									$sql = "SELECT product_option_value_id
											FROM   " . TABLE_PRODUCT_OPTION_VALUE . "
											WHERE  product_option_id = :product_option_id
											AND    name = :name";
									$sql = $db->bindVars($sql, ':product_option_id', $optionResult->fields['product_option_id'], 'integer');
									$sql = $db->bindVars($sql, ':name', $val, 'string');
									$optionValueResult = $db->Execute($sql);
									while (!$optionValueResult->EOF) {
										$sql_data_array = array(
											array('fieldName'=>'product_id', 'value'=>$product_id, 'type'=>'integer'),
											array('fieldName'=>'product_option_id', 'value'=>$optionResult->fields['product_option_id'], 'type'=>'integer'),
											array('fieldName'=>'product_option_value_id', 'value'=>$optionValueResult->fields['product_option_value_id'], 'type'=>'integer')
										);

										// 复选框为非必填,价格+1.99
										if ($optionResult->fields['type'] == 'checkbox') {
											$sql_data_array[] = array('fieldName'=>'required', 'value'=>'0', 'type'=>'integer');
											$sql_data_array[] = array('fieldName'=>'price', 'value'=>'1.99', 'type'=>'decimal');
										}
										$db->perform(TABLE_PRODUCT_ATTRIBUTE, $sql_data_array);
										$optionValueResult->MoveNext();
									}
								}
							}
						}
					}
				}
			}
		}
	break;
	case 'product_review':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} elseif($fields = array_flip(fgetcsv($handle))) {
			$i=0;
			while ($_data = fgetcsv($handle)) {
				$i++;
				foreach ($fields as $key => $val) {
					$$key = db_prepare_input($_data[$val]);
				}
				if (!isset($v_sku) || !not_null($v_sku)) {
					$display[$i] = '产品型号为空';
					continue;
				} else {
					if ($v_sku=='随机') {
						$sql = "SELECT product_id, sku, name FROM " . TABLE_PRODUCT;
						$result = $db->ExecuteRandomMulti($sql, 1);
						if ($result->RecordCount() > 0) {
							$product_id = $result->fields['product_id'];
							$product_name = $result->fields['name'];
							$display[$i] = '新增评论 产品型号(随机):' . $result->fields['sku'];
						} else {
							$display[$i] = '产品型号不存在';
							continue;
						}
					} else {
						$sql = "SELECT product_id, name FROM " . TABLE_PRODUCT . " WHERE sku = :sku LIMIT 1";
						$sql = $db->bindVars($sql, ':sku', $v_sku, 'string');
						$result = $db->Execute($sql);
						if ($result->RecordCount() > 0) {
							$product_id = $result->fields['product_id'];
							$product_name = $result->fields['name'];
							$display[$i] = '新增评论 产品型号:' . $v_sku;
						} else {
							$display[$i] = '产品型号不存在';
							continue;
						}
					}
				}
				$sql_data_array = array();
				$sql_data_array[] = array('fieldName'=>'product_id', 'value'=>$product_id, 'type'=>'integer');
				//nickname
				if (!isset($v_nickname) || !not_null($v_nickname)) {
					$display[$i] .= ' 不存在产品评论作者';
					continue;
				}
				$sql_data_array[] = array('fieldName'=>'nickname', 'value'=>$v_nickname, 'type'=>'string');
				//rating
				if (!isset($v_rating) || $v_rating<1 || $v_rating>5) {
					$v_rating = 5;
				}
				$sql_data_array[] = array('fieldName'=>'rating', 'value'=>$v_rating, 'type'=>'integer');
				//content
				if (!isset($v_content) || !not_null($v_content)) {
					$display[$i] .= ' 不存在产品评论内容';
					continue;
				}
				$v_content = str_replace('{产品名}', $product_name, $v_content);
				$sql_data_array[] = array('fieldName'=>'content', 'value'=>$v_content, 'type'=>'string');
				//status
				if (isset($v_status)) {
					$sql_data_array[] = array('fieldName'=>'status', 'value'=>$v_status, 'type'=>'integer');
				}
				//date_added
				if (isset($v_date_added) && validate_datetime($v_date_added)) {
					$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>$v_date_added, 'type'=>'string');
				} else {
					$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				}
				$db->perform(TABLE_PRODUCT_REVIEW, $sql_data_array);
			}
		}
	break;
	case 'order_review':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} elseif($fields = array_flip(fgetcsv($handle))) {
			$i=0;
			while ($_data = fgetcsv($handle)) {
				$i++;
				foreach ($fields as $key => $val) {
					$$key = db_prepare_input($_data[$val]);
				}
				$display[$i] = '新增评论(' . $i. ')';
				$sql_data_array = array();
				$sql_data_array[] = array('fieldName'=>'order_id', 'value'=>$v_order_id, 'type'=>'integer');
				//quality
				if (!isset($v_quality) || $v_quality<1 || $v_quality>5) {
					$v_quality = 5;
				}
				$sql_data_array[] = array('fieldName'=>'quality', 'value'=>$v_quality, 'type'=>'integer');
				//ship
				if (!isset($v_ship) || $v_ship<1 || $v_ship>5) {
					$v_ship = 5;
				}
				$sql_data_array[] = array('fieldName'=>'ship', 'value'=>$v_ship, 'type'=>'integer');
				//service
				if (!isset($v_service) || $v_service<1 || $v_service>5) {
					$v_service = 5;
				}
				$sql_data_array[] = array('fieldName'=>'service', 'value'=>$v_service, 'type'=>'integer');
				//email_address
				if (!isset($v_email_address)) {
					$display[$i] .= ' 订单评论邮件格式错误';
					continue;
				}
				$sql_data_array[] = array('fieldName'=>'email_address', 'value'=>$v_email_address, 'type'=>'string');
				//content
				if (!isset($v_content) || !not_null($v_content)) {
					$display[$i] .= ' 不存在订单评论内容';
					continue;
				}
				$sql_data_array[] = array('fieldName'=>'content', 'value'=>$v_content, 'type'=>'string');
				//date_added
				if (isset($v_date_added) && validate_datetime($v_date_added)) {
					$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>$v_date_added, 'type'=>'string');
				} else {
					$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				}
				$db->perform(TABLE_ORDER_REVIEW, $sql_data_array);
			}
		}
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>导入/导出管理(老版)</title>
	<meta name="robot" content="noindex, nofollow" />
	<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_ADMIN; ?>" />
	<link href="css/styles.css" type="text/css" rel="stylesheet" />
	<link href="css/styles-ie.css" type="text/css" rel="stylesheet" />
	<script src="js/jquery/jquery.js" type="text/javascript"></script>
	<script src="js/jquery/base.js" type="text/javascript"></script>
</head>
<body>
<div class="wrapper">
	<?php require(DIR_FS_ADMIN_INCLUDES . 'noscript.php'); ?>
	<div class="page">
		<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
		<div class="main-container">
			<div class="main">
				<?php if ($message_stack->size('import') > 0) echo $message_stack->output('import'); ?>
				<div class="page-title">
					<h1>导入/导出管理(老版)</h1>
				</div>
				<form enctype="multipart/form-data" action="<?php echo href_link(FILENAME_IMPORT_OLD); ?>" method="post">
					<div class="no-display">
						<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
						<input type="hidden" value="100000000" name="MAX_FILE_SIZE" />
					</div>
					<div class="col2-set">
						<div class="col-1">
							<div class="box">
								<div class="box-title">
									<h2>操作</h2>
								</div>
								<div class="box-content">
									<select name="action">
										<option value="product_option">选项</option>
										<option value="category">分类</option>
										<option value="product">产品</option>
										<option value="product_review">产品评论</option>
										<option value="order_review">订单评论</option>
									</select>
									<input type="file" name="usrfl" />
									<button type="submit" class="button"><span><span>导入</span></span></button>
									<br />
									<br />
									<h3>导出</h3>
									<p><a href="<?php echo href_link(FILENAME_EXPORT, 'action=categorylinks'); ?>">导出分类链接</a></p>
									<p><a href="<?php echo href_link(FILENAME_EXPORT, 'action=productlinks'); ?>">导出产品链接</a></p>
									<p><a href="<?php echo href_link(FILENAME_EXPORT, 'action=category_old'); ?>">导出分类表</a></p>
									<p><a href="<?php echo href_link(FILENAME_EXPORT, 'action=product_old'); ?>">导出产品表（无选项）</a></p>
									<br />
									<br />
									<h3>清空</h3>
									<p><a href="javascript:;" onclick="if(confirm('清除数据后您将不能恢复，请确定要这么做吗？')){setLocation('<?php echo href_link(FILENAME_IMPORT_OLD, 'action=clearOrder'); ?>');}">清除用户和订单数据</a></p>
									<p><a href="javascript:;" onclick="if(confirm('清除数据后您将不能恢复，请确定要这么做吗？')){setLocation('<?php echo href_link(FILENAME_IMPORT_OLD, 'action=clearProduct'); ?>');}">清除分类和产品数据</a></p>
								</div>
							</div>
						</div>
						<div class="col-2">
							<div class="box">
								<div class="box-title">
									<h2>说明</h2>
								</div>
								<div class="box-content">
									<p>1.导入顺序：选项、分类、产品和产品评论</p>
									<p>2.选项模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/product_option_old.csv">下载</a></p>
									<p>3.分类模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/category_old.csv">下载</a></p>
									<p>4.产品模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/product_old.csv">下载</a></p>
									<p>5.产品评论模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/product_review_old.csv">下载</a></p>
									<p>6.订单评论模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/order_review_old.csv">下载</a></p>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div class="box">
					<div class="box-title">
						<h2>结果集</h2>
					</div>
					<div class="box-content">
						<?php foreach ($display as $dis) { ?>
							<p><?php echo $dis; ?></p>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>