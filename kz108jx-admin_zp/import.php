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
			redirect(href_link(FILENAME_IMPORT));
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
			redirect(href_link(FILENAME_IMPORT));
		break;
	}
}
@set_time_limit(100000000);
@ini_set('max_input_time', '100000000');
$action  = isset($_POST['action'])?$_POST['action']:'';
$display = array();
switch ($action) {
	case 'category':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} else {
			$fields = fgetcsv($handle);
			if ($fields[0] != '分类名称') {
				$display[] = '请导入正确的分类数据。';
				break;
			}

			// 获取分类路径
			require(DIR_FS_ADMIN_CLASSES . 'category_tree.php');
			$category_tree    = new category_tree();
			$tempTreeData     = $category_tree->getTree();
			$categoryPathList = array();
			if (!empty($tempTreeData)) {
				foreach ($tempTreeData as $key => $val) {
					$categoryPathList[str_replace(' > ', '/', $val['name'])] = $key;
				}
			}

			$i = 0;
			while ($data = fgetcsv($handle)) {
				$i++;
				// 获取数据
				$categoryName    = trim($data[0]);
				$image           = trim($data[1]);
				$parentSku       = $data[2];
				$sku             = (empty($data[2]) ? '' : $data[2] . '/') . $categoryName;
				$sort            = (int)$data[3];
				$status          = (int)$data[4];
				$metaTitle       = trim($data[5]);
				$metaKeywords    = trim($data[6]);
				$metaDescription = trim($data[7]);
				$url             = trim($data[8]);
				$description     = trim($data[9]);

				// 父级分类ID
				$parentId = 0;
				if (!empty($parentSku)) {
					if (isset($categoryPathList[$parentSku])) {
						$parentId = $categoryPathList[$parentSku];
					}
				}

				// 验证数据
				if (empty($sku)) {
					$display[$i] = '分类型号为空';
					continue;
				} else {
					$category_is_new = true;
					if (key_exists($sku, $categoryPathList)) {
						$display[$i] = '<font color="green">更新</font> 分类名称:' . $categoryName;
						$category_is_new = false;
						$category_id = $result->fields['category_id'];
					} else {
						$display[$i] = '<font color="green">新增</font> 分类名称:' . $categoryName;
						$category_is_new = true;
					}
				}

				// category
				$sql_data_array   = array();
				$sql_data_array[] = array('fieldName' => 'name', 'value' => $categoryName, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'sku', 'value' => $sku, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'description', 'value' => $description, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'image', 'value' => $image, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'url', 'value' => $url, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'parent_id', 'value' => $parentId, 'type' => 'integer');
				$sql_data_array[] = array('fieldName' => 'meta_title', 'value' => $metaTitle, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'meta_keywords', 'value' => $metaKeywords, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'meta_description', 'value' => $metaDescription, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'status', 'value' => $status, 'type' => 'integer');
				$sql_data_array[] = array('fieldName' => 'sort_order', 'value' => $sort, 'type' => 'integer');

				if ($category_is_new) { // 添加分类
					$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
					$db->perform(TABLE_CATEGORY, $sql_data_array);

					// 添加自己到分类路径中
					$categoryId = $db->insert_ID();
					$categoryPathList[$sku] = $categoryId;
				} else { // 更新分类
					$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
					$db->perform(TABLE_CATEGORY, $sql_data_array, 'UPDATE', 'category_id = ' . $category_id);
				}
			}
		}
	break;
	case 'options':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} else {
			$fields = fgetcsv($handle);
			if ($fields[3] != '选项值') {
				$display[] = '请导入正确的选项数据。';
				break;
			}
			$i = 0;
			while ($data = fgetcsv($handle)) {
				$i++;

				// 获取数据
				$nameStr    = trim($data[0]);
				$typeStr    = (int)$data[1];
				$sortStr    = (int)$data[2];
				$valuesJson = trim($data[3]);

				// 验证数据
				if (empty($nameStr)) {
					$display[$i] = '产品属性名称为空';
					continue;
				} else {
					$sql    = "SELECT product_option_id FROM " . TABLE_PRODUCT_OPTION . " WHERE name = :name LIMIT 1";
					$sql    = $db->bindVars($sql, ':name', $nameStr, 'string');
					$result = $db->Execute($sql);
					$product_option_is_new = true;
					if ($result->RecordCount() > 0) {
						$display[$i] = '<font color="green">更新</font> 产品选项名称:' . $nameStr;
						$product_option_is_new = false;
						$product_option_id = $result->fields['product_option_id'];
					} else {
						$display[$i] = '<font color="green">新增</font> 产品选项名称:' . $nameStr;
						$product_option_is_new = true;
					}
				}

				// product_option
				$sql_data_array   = array();
				$sql_data_array[] = array('fieldName' => 'name', 'value' => $nameStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'type', 'value' => empty($typeStr) ? 'text' : 'select', 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'sort_order', 'value' => $sortStr, 'type' => 'integer');

				if ($product_option_is_new) { // 添加新选项
					$db->perform(TABLE_PRODUCT_OPTION, $sql_data_array);
					$product_option_id = $db->insert_ID();
				} else { // 更新选项
					$db->perform(TABLE_PRODUCT_OPTION, $sql_data_array, 'UPDATE', 'product_option_id = ' . (int)$product_option_id);
				}

				if ($product_option_id > 0) {
					$valuesArr = json_decode($valuesJson, true);
					if (empty($typeStr) && empty($valuesArr)) {
						continue;
					}

					$updateMsg = array();
					$addMsg    = array();
					foreach ($valuesArr as $val) {
						$sql = "SELECT product_option_value_id FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE name = :name AND product_option_id = :productOptionId LIMIT 1";
						$sql = $db->bindVars($sql, ':name', $val['name'], 'string');
						$sql = $db->bindVars($sql, ':productOptionId', $product_option_id, 'string');
						$result = $db->Execute($sql);
						$option_value_is_new = true;
						if ($result->RecordCount() > 0) {
							$updateMsg[]             = $val['name'];
							$option_value_is_new     = false;
							$product_option_value_id = $result->fields['product_option_value_id'];
						} else {
							$addMsg[]            = $val['name'];
							$option_value_is_new = true;
						}

						// product_option_value
						$sql_data_array   = array();
						$sql_data_array[] = array('fieldName' => 'product_option_id', 'value' => $product_option_id, 'type' => 'integer');
						$sql_data_array[] = array('fieldName' => 'name', 'value' => $val['name'], 'type' => 'string');
						$sql_data_array[] = array('fieldName' => 'sort_order', 'value' => $val['sort'], 'type' => 'integer');

						if ($option_value_is_new) { // 添加选项值
							$db->perform(TABLE_PRODUCT_OPTION_VALUE, $sql_data_array);
						} else { // 更新选项值
							$db->perform(TABLE_PRODUCT_OPTION_VALUE, $sql_data_array, 'UPDATE', 'product_option_value_id = ' . (int)$product_option_value_id);
						}
					}

					$display[$i] .= count($updateMsg) ? sprintf('; <font color="green">更新</font> 产品选项值:{%s}', implode(',', $updateMsg)) : '';
					$display[$i] .= count($addMsg) ? sprintf('; <font color="green">新增</font> 产品选项值:{%s}', implode(',', $addMsg)) : '';
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
		} else {
			$fields = fgetcsv($handle);
			if ($fields[1] != '分类名称' || count($fields) < 15) {
				$display[] = '请导入正确的产品数据。';
				break;
			}
			$i = 0;

			// 获取分类路径
			require(DIR_FS_ADMIN_CLASSES . 'category_tree.php');
			$category_tree    = new category_tree();
			$tempTreeData     = $category_tree->getTree();
			$categoryPathList = array();
			if (!empty($tempTreeData)) {
				foreach ($tempTreeData as $key => $val) {
					$categoryPathList[str_replace(' > ', '/', $val['name'])] = $key;
				}
			}

			// 获取选项名称
			$sql    = "SELECT product_option_id, name FROM " . TABLE_PRODUCT_OPTION;
			$result = $db->Execute($sql, false, true, 604800);
			$optionNameList = array();
			while (!$result->EOF) {
				$optionNameList[$result->fields['product_option_id']] = $result->fields['name'];
				$result->MoveNext();
			}

			while ($data = fgetcsv($handle)) {
				$i++;

				// 获取数据
				$skuStr              = trim($data[0]);
				$categoryNameStr     = trim($data[1]);
				$nameStr             = trim($data[2]);
				$imageStr            = trim($data[3]);
				$priceStr            = trim($data[4]);
				$specialsPrice       = trim($data[5]);
				$optionJson          = trim($data[6]);
				$sortStr             = (int)$data[7];
				$statusStr           = (int)$data[8];
				$metaTitleStr        = trim($data[9]);
				$metaKeywordsStr     = trim($data[10]);
				$metaDescriptionStr  = trim($data[11]);
				$urlStr              = trim($data[12]);
				$descriptionStr      = trim($data[13]);
				$shortDescriptionStr = trim($data[14]);

				// 验证数据
				if (empty($skuStr)) {
					$display[$i] = '产品型号为空';
					continue;
				} else {
					$sql = "SELECT product_id FROM " . TABLE_PRODUCT . " WHERE sku = :sku LIMIT 1";
					$sql = $db->bindVars($sql, ':sku', $skuStr, 'string');
					$result = $db->Execute($sql);
					$product_is_new = true;
					if ($result->RecordCount() > 0) {
						$display[$i] = '<font color="green">更新</font> 产品型号:' . $skuStr;
						$product_is_new = false;
						$productId = $result->fields['product_id'];
					} else {
						$display[$i] = '<font color="green">新增</font> 产品型号:' . $skuStr;
						$product_is_new = true;
					}
				}
				if (strlen($nameStr) < 1) {
					$display[$i] .= ' <font color="red">不存在</font>产品名';
					continue;
				}
				if (empty($categoryNameStr)) {
					$display[$i] .= ' 分类为空';
					continue;
				} else {
					$categoryId = isset($categoryPathList[$categoryNameStr]) ? $categoryPathList[$categoryNameStr] : 0;
					if (empty($categoryId)) {
						$display[$i] .= ' 分类' . $categoryNameStr . '<font color="red">不存在</font>';
						continue;
					}
				}

				// product
				$sql_data_array   = array();
				$sql_data_array[] = array('fieldName' => 'sku', 'value' => $skuStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'name', 'value' => $nameStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'short_description', 'value' => $shortDescriptionStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'description', 'value' => $descriptionStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'image', 'value' => $imageStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'url', 'value' => $urlStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'price', 'value' => $priceStr, 'type' => 'decimal');
				$sql_data_array[] = array('fieldName' => 'specials_price', 'value' => $specialsPrice, 'type' => 'decimal');
				$sql_data_array[] = array('fieldName' => 'master_category_id', 'value' => $categoryId, 'type' => 'integer');
				$sql_data_array[] = array('fieldName' => 'meta_title', 'value' => $metaTitleStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'meta_keywords', 'value' => $metaKeywordsStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'meta_description', 'value' => $metaDescriptionStr, 'type' => 'string');
				$sql_data_array[] = array('fieldName' => 'sort_order', 'value' => $sortStr, 'type' => 'integer');
				$sql_data_array[] = array('fieldName' => 'status', 'value' => $statusStr, 'type' => 'integer');

				// 过滤数据
				for ($j = 15; $j < 35; $j++) {
					$str = 'filter_' . ($j - 14);
					$sql_data_array[] = array('fieldName' => $str, 'value' => isset($data[$j]) ? $data[$j] : '', 'type' => 'string');
				}

				if ($product_is_new) { // 添加产品
					$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
					$db->perform(TABLE_PRODUCT, $sql_data_array);
					$productId = $db->insert_ID();

					// product_to_category
					$sql_data_array = array(
						array('fieldName' => 'product_id', 'value' => $productId, 'type' => 'integer'),
						array('fieldName' => 'category_id', 'value' => $categoryId, 'type' => 'integer')
					);
					$db->perform(TABLE_PRODUCT_TO_CATEGORY, $sql_data_array);
				} else { // 更新产品
					$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
					$db->perform(TABLE_PRODUCT, $sql_data_array, 'UPDATE', 'product_id = ' . $productId);
				}

				// 产品选项
				$optionArr = json_decode($optionJson, true);
				if (!empty($optionArr)) {
					$selectUpdateMsg = array();
					$selectAddMsg    = array();
					foreach ($optionArr as $optionNameStr => $option) {
						$optionId = array_search($optionNameStr, $optionNameList);
						if (empty($optionId)) {
							$display[$i] .= ' 产品选项:' . $optionNameStr . '<font color="red">不存在</font>';
							continue;
						}

						$sql_data_array = array();
						$requiredStr    = isset($option['required']) && in_array($option['required'], array(0, 1)) ? $option['required'] : 1;
						if ($option['type'] && !empty($option['values'])) { // 选择型
							$updateMsg = array();
							$addMsg    = array();
							foreach ($option['values'] as $optionValueStr => $valueArr) {
								// 获取product_option_value_id
								$sql = "SELECT product_option_value_id FROM " . TABLE_PRODUCT_OPTION_VALUE . " WHERE name = :name AND product_option_id = :productOptionId LIMIT 1";
								$sql = $db->bindVars($sql, ':name', $optionValueStr, 'string');
								$sql = $db->bindVars($sql, ':productOptionId', $optionId, 'string');
								$result = $db->Execute($sql);
								if ($result->RecordCount() > 0) {
									$productOptionValueId = $result->fields['product_option_value_id'];
									$priceStr             = isset($valueArr['price']) ? $valueArr['price'] : '0.00';
									$pricePrefix          = isset($valueArr['price_prefix']) ? $valueArr['price_prefix'] : '+';

									// 添加关系
									$sql_data_array = array(
										array('fieldName' => 'product_id', 'value' => $productId, 'type' => 'integer'),
										array('fieldName' => 'product_option_id', 'value' => $optionId, 'type' => 'integer'),
										array('fieldName' => 'product_option_value_id', 'value' => $productOptionValueId, 'type' => 'integer'),
										array('fieldName' => 'required', 'value' => $requiredStr, 'type' => 'integer'),
										array('fieldName' => 'price', 'value' => $priceStr, 'type' => 'string'),
										array('fieldName' => 'price_prefix', 'value' => $pricePrefix, 'type' => 'string')
									);

									$sql = "SELECT product_id FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_id = :productId AND product_option_id = :productOptionId AND product_option_value_id = :productOptionValueId LIMIT 1";
									$sql = $db->bindVars($sql, ':productId', $productId, 'string');
									$sql = $db->bindVars($sql, ':productOptionId', $optionId, 'string');
									$sql = $db->bindVars($sql, ':productOptionValueId', $productOptionValueId, 'string');
									$result = $db->Execute($sql);
									if ($result->RecordCount() > 0) {
										$updateMsg[] = $optionValueStr;
										$product_attribute_is_new = false;
									} else {
										$addMsg[] = $optionValueStr;
										$product_attribute_is_new = true;
									}

									if ($product_attribute_is_new) {
										$db->perform(TABLE_PRODUCT_ATTRIBUTE, $sql_data_array);
									} else {
										$db->perform(TABLE_PRODUCT_ATTRIBUTE, $sql_data_array, 'UPDATE', sprintf("product_id = %s AND product_option_id = %s AND product_option_value_id = %s", $productId, $optionId, $productOptionValueId));
									}
								} else {
									$display[$i] .= ' 产品选项值:' . $optionValueStr . '<font color="red">不存在</font>';
								}
							}

							$display[$i] .= count($updateMsg) ? sprintf('; <font color="green">更新</font> 产品选项<font color="#b8860b">%s</font>的选项值:{%s}', $optionNameStr, implode(',', $updateMsg)) : '';
							$display[$i] .= count($addMsg) ? sprintf('; <font color="green">新增</font> 产品选项<font color="#b8860b">%s</font>的选项值:{%s}', $optionNameStr, implode(',', $addMsg)) : '';
						} elseif (!$option['type'] && !empty($option['values'])) { // 填写型
							$priceStr = isset($option['values']['price']) ? $option['values']['price'] : '0.00';
							$pricePrefix = isset($option['values']['price_prefix']) ? $option['values']['price_prefix'] : '+';

							// 添加关系
							$sql_data_array = array(
								array('fieldName' => 'product_id', 'value' => $productId, 'type' => 'integer'),
								array('fieldName' => 'product_option_id', 'value' => $optionId, 'type' => 'integer'),
								array('fieldName' => 'product_option_value_id', 'value' => 0, 'type' => 'integer'),
								array('fieldName' => 'required', 'value' => $requiredStr, 'type' => 'integer'),
								array('fieldName' => 'price', 'value' => $priceStr, 'type' => 'string'),
								array('fieldName' => 'price_prefix', 'value' => $pricePrefix, 'type' => 'string')
							);

							$sql = "SELECT product_id FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_id = :productId AND product_option_id = :productOptionId AND product_option_value_id = '0' LIMIT 1";
							$sql = $db->bindVars($sql, ':productId', $productId, 'string');
							$sql = $db->bindVars($sql, ':productOptionId', $optionId, 'string');
							$result = $db->Execute($sql);
							if ($result->RecordCount() > 0) {
								$selectUpdateMsg[] = '<font color="#b8860b">' . $optionNameStr . '</font>';
								$product_attribute_is_new = false;
							} else {
								$selectAddMsg[] = '<font color="#b8860b">' . $optionNameStr . '</font>';
								$product_attribute_is_new = true;
							}

							if ($product_attribute_is_new) {
								$db->perform(TABLE_PRODUCT_ATTRIBUTE, $sql_data_array);
							} else {
								$db->perform(TABLE_PRODUCT_ATTRIBUTE, $sql_data_array, 'UPDATE', sprintf("product_id = %s AND product_option_id = %s AND product_option_value_id = '0'", $productId, $optionId));
							}
						}
					}
					$display[$i] .= count($selectUpdateMsg) ? sprintf('; <font color="green">更新</font> 产品的选择型选项:{%s}', implode(',', $selectUpdateMsg)) : '';
					$display[$i] .= count($selectAddMsg) ? sprintf('; <font color="green">新增</font> 产品的选择型选项{%s}', implode(',', $selectAddMsg)) : '';
				}
			}
		}
	break;
	case 'sub_category':
		$file_location = $_FILES['usrfl']['tmp_name'];
		if (!file_exists($file_location)) {
			$display[] = '文件不存在。';
		} elseif(!($handle = fopen($file_location, "r"))) {
			$display[] = '文件无法读取。';
		} else {
			$fields = fgetcsv($handle);
			if ($fields[1] != '分类名称' || count($fields) != 2) {
				$display[] = '请导入正确的副分类产品数据。';
				break;
			}
			$i = 0;

			// 获取分类路径
			require(DIR_FS_ADMIN_CLASSES . 'category_tree.php');
			$category_tree    = new category_tree();
			$tempTreeData     = $category_tree->getTree();
			$categoryPathList = array();
			if (!empty($tempTreeData)) {
				foreach ($tempTreeData as $key => $val) {
					$categoryPathList[str_replace(' > ', '/', $val['name'])] = $key;
				}
			}

			while ($data = fgetcsv($handle)) {
				$i++;

				// 获取数据
				$skuStr          = trim($data[0]);
				$categoryNameStr = trim($data[1]);

				// 验证数据
				if (empty($skuStr)) {
					$display[$i] = '副分类产品型号为空';
					continue;
				} else {
					$sql = "SELECT product_id, master_category_id FROM " . TABLE_PRODUCT . " WHERE sku = :sku LIMIT 1";
					$sql = $db->bindVars($sql, ':sku', $skuStr, 'string');
					$result = $db->Execute($sql);
					if ($result->RecordCount() > 0) {
						$productId        = $result->fields['product_id'];
						$masterCategoryId = $result->fields['master_category_id'];
					} else {
						$display[$i] = '副分类产品型号:' . $skuStr . '不存在';
						continue;
					}
				}
				if (empty($categoryNameStr)) {
					$display[$i] = sprintf("产品型号:%s的副分类名称为空", $skuStr);
					continue;
				} else {
					$categoryId = isset($categoryPathList[$categoryNameStr]) ? $categoryPathList[$categoryNameStr] : 0;
					if (empty($categoryId)) {
						$display[$i] = sprintf("产品型号:%s的副分类:%s不存在", $skuStr, $categoryNameStr);
						continue;
					}
				}

				// 产品分类关系
				$sql = "SELECT product_id FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE product_id = :productId AND category_id = :categoryId LIMIT 1";
				$sql = $db->bindVars($sql, ':productId', $productId, 'integer');
				$sql = $db->bindVars($sql, ':categoryId', $categoryId, 'integer');
				$result = $db->Execute($sql);
				if ($result->RecordCount() > 0) {
					$display[$i] = sprintf("<font color='red'>存在</font>产品型号:%s的副分类:%s", $skuStr, $categoryNameStr);
				} else {
					$display[$i] = sprintf("<font color='green'>新增</font> 产品型号:%s的副分类:%s", $skuStr, $categoryNameStr);
					$sql_data_array   = array();
					$sql_data_array[] = array('fieldName' => 'product_id', 'value' => $productId, 'type' => 'integer');
					$sql_data_array[] = array('fieldName' => 'category_id', 'value' => $categoryId, 'type' => 'integer');
					$db->perform(TABLE_PRODUCT_TO_CATEGORY, $sql_data_array);
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
							$productId = $result->fields['product_id'];
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
							$productId = $result->fields['product_id'];
							$product_name = $result->fields['name'];
							$display[$i] = '新增评论 产品型号:' . $v_sku;
						} else {
							$display[$i] = '产品型号不存在';
							continue;
						}
					}
				}
				$sql_data_array = array();
				$sql_data_array[] = array('fieldName'=>'product_id', 'value'=>$productId, 'type'=>'integer');
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
<title>导入/导出管理</title>
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
	    			<h1>导入/导出管理</h1>
	    		</div>
	    		<form enctype="multipart/form-data" action="<?php echo href_link(FILENAME_IMPORT); ?>" method="post">
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
									<option value="category">分类</option>
									<option value="options">选项</option>
									<option value="product">产品</option>
									<option value="sub_category">副分类产品</option>
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
								<p><a href="<?php echo href_link(FILENAME_EXPORT, 'action=category'); ?>">导出分类表</a></p>
								<p><a href="<?php echo href_link(FILENAME_EXPORT, 'action=product'); ?>">导出产品表（无选项）</a></p>
								<br />
								<br />
								<h3>清空</h3>
								<p><a href="javascript:;" onclick="if(confirm('清除数据后您将不能恢复，请确定要这么做吗？')){setLocation('<?php echo href_link(FILENAME_IMPORT, 'action=clearOrder'); ?>');}">清除用户和订单数据</a></p>
								<p><a href="javascript:;" onclick="if(confirm('清除数据后您将不能恢复，请确定要这么做吗？')){setLocation('<?php echo href_link(FILENAME_IMPORT, 'action=clearProduct'); ?>');}">清除分类和产品数据</a></p>
							</div>
		    			</div>
					</div>
					<div class="col-2">
						<div class="box">
							<div class="box-title">
								<h2>说明</h2>
							</div>
							<div class="box-content">
								<p>1.导入顺序：分类、选项、产品、副分类和产品评论</p>
								<p>2.分类模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/category.csv">下载</a></p>
								<p>3.选项模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/product_option.csv">下载</a></p>
								<p>4.产品模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/product.csv">下载</a></p>
								<p>5.副分类产品模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/sub_category.csv">下载</a></p>
								<p>6.产品评论模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/product_review.csv">下载</a></p>
								<p>7.订单评论模板表<a href="<?php echo HTTP_SERVER . DIR_WS_ADMIN ?>backups/order_review.csv">下载</a></p>
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