<?php require('includes/application_top.php'); ?>
<?php
$product_id = isset($_GET['product_id'])?$_GET['product_id']:0;
$category_id = isset($_GET['category_id'])?$_GET['category_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabInStock = array('0'=>'无', '1'=>'有');
$availabStatus = array('0'=>'禁用', '1'=>'启用');
$productFilterFields = array();
$productFields = array_keys($db->metaColumns('product'));
foreach ($productFields as $field) {
	if (strstr($field, 'filter_')) {
		$productFilterFields[] = $field;
	}
}
//category_tree
require(DIR_FS_ADMIN_CLASSES . 'category_tree.php');
$category_tree = new category_tree();
$availabCategoryList = $category_tree->getTree();
switch ($action) {
	case 'save':
		$error             = false;
		$product           = db_prepare_input($_POST['product']);
		$productToCategory = isset($_POST['product_to_category']) ? db_prepare_input($_POST['product_to_category']) : array();
		$options           = isset($_POST['options']) && is_array($_POST['options']) ? db_prepare_input($_POST['options']) : array();
		$optionRequireds   = isset($_POST['option_requireds']) && is_array($_POST['option_requireds']) ? db_prepare_input($_POST['option_requireds']) : array();
		$optionValues      = isset($_POST['option_values']) && is_array($_POST['option_values']) ? db_prepare_input($_POST['option_values']) : array();
		$optionvalue       = isset($_POST['option_value']) && is_array($_POST['option_value']) ? db_prepare_input($_POST['option_value']) : array();
		$securityToken     = isset($_POST['securityToken']) ? db_prepare_input($_POST['securityToken']) : '';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('product', '产品设置保存时出现安全错误。');
		}
		if (strlen($product['sku']) < 1) {
			$error = true;
			$message_stack->add('product', '产品型号不能为空。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT . " WHERE sku = :sku AND product_id <> :productID";
			$sql = $db->bindVars($sql, ':sku', $product['sku'], 'string');
			$sql = $db->bindVars($sql, ':productID', isset($product['product_id'])?$product['product_id']:0, 'integer');
			$check_product = $db->Execute($sql);
			if ($check_product->fields['total'] > 0) {
				$error = true;
				$message_stack->add('product', '产品型号存在相同。');
			}
		}
		if (strlen($product['name']) < 1) {
			$error = true;
			$message_stack->add('product', '产品名称不能为空。');
		}
		if (!array_key_exists($product['in_stock'], $availabInStock)) $product['in_stock'] = 1;
		if (!array_key_exists($product['status'], $availabStatus)) $product['status'] = 0;
		if (strlen($product['group_name']) < 1) {
			$error = true;
			$message_stack->add('product', '产品名称不能为空。');
		} else if (!ctype_alnum($product['group_name'])) {
			$error = true;
			$message_stack->add('product', '产品分组名称只能由大小写字母与数字组成。');
		}
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'sku', 'value'=>$product['sku'], 'type'=>'string'),
				array('fieldName'=>'name', 'value'=>$product['name'], 'type'=>'string'),
				array('fieldName'=>'short_description', 'value'=>$product['short_description'], 'type'=>'string'),
				array('fieldName'=>'description', 'value'=>$product['description'], 'type'=>'string'),
				array('fieldName'=>'image', 'value'=>$product['image'], 'type'=>'string'),
				array('fieldName'=>'url', 'value'=>$product['url'], 'type'=>'string'),
				array('fieldName'=>'price', 'value'=>$product['price'], 'type'=>'string'),
				array('fieldName'=>'specials_price', 'value'=>$product['specials_price'], 'type'=>'string'),
				array('fieldName'=>'master_category_id', 'value'=>$product['master_category_id'], 'type'=>'integer'),
				array('fieldName'=>'meta_title', 'value'=>$product['meta_title'], 'type'=>'string'),
				array('fieldName'=>'meta_keywords', 'value'=>$product['meta_keywords'], 'type'=>'string'),
				array('fieldName'=>'meta_description', 'value'=>$product['meta_description'], 'type'=>'string'),
				array('fieldName'=>'stock_qty', 'value'=>$product['stock_qty'], 'type'=>'integer'),
				array('fieldName'=>'in_stock', 'value'=>$product['in_stock'], 'type'=>'integer'),
				array('fieldName'=>'status', 'value'=>$product['status'], 'type'=>'integer'),
				array('fieldName'=>'sort_order', 'value'=>$product['sort_order'], 'type'=>'integer'),
				array('fieldName'=>'viewed', 'value'=>$product['viewed'], 'type'=>'integer'),
				array('fieldName'=>'ordered', 'value'=>$product['ordered'], 'type'=>'integer'),
				array('fieldName'=>'group_name', 'value'=>$product['group_name'], 'type'=>'string')
			);
			//specials_expire_date
			if (not_null($product['specials_expire_date']) && validate_date($product['specials_expire_date'])) {
				$sql_data_array[] = array('fieldName'=>'specials_expire_date', 'value'=>$product['specials_expire_date'], 'type'=>'string');
			} else {
				$sql_data_array[] = array('fieldName'=>'specials_expire_date', 'value'=>'NULL', 'type'=>'noquotestring');
			}
			//Product Filter
			foreach ($productFilterFields as $field) {
				$sql_data_array[] = array('fieldName'=>$field, 'value'=>$product[$field], 'type'=>'string');
			}
			if ($product['product_id'] > 0) {
				$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_PRODUCT, $sql_data_array, 'UPDATE', 'product_id = ' . $product['product_id']);
			} else {
				$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_PRODUCT, $sql_data_array);
				$product['product_id'] = $db->Insert_ID();
			}
			
			if ($product['product_id'] > 0) {
				//product_to_category
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE product_id = " . (int)$product['product_id']);
				if (!in_array($product['master_category_id'], $productToCategory)
					&& $product['master_category_id'] > 0) {
					$productToCategory[] = $product['master_category_id'];
				}
				foreach ($productToCategory as $val) {
					$sql_data_array = array(
						array('fieldName'=>'product_id', 'value'=>$product['product_id'], 'type'=>'integer'),
						array('fieldName'=>'category_id', 'value'=>$val, 'type'=>'integer')
					);
					$db->perform(TABLE_PRODUCT_TO_CATEGORY, $sql_data_array);
				}
				
				//product_attribute
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_id = " . (int)$product['product_id']);
				//product_option
				$sql = "SELECT product_option_id, `type`
						FROM   " . TABLE_PRODUCT_OPTION . "
						ORDER BY sort_order ASC";
				$optionResult   = $db->Execute($sql);
				$optionTypeList = array();
				while (!$optionResult->EOF) {
					$optionTypeList[$optionResult->fields['product_option_id']] = $optionResult->fields['type'];
					$optionResult->MoveNext();
				}
				if (!empty($options)) {
					foreach ($options as $optionId) {
						if (!isset($optionTypeList[$optionId])
							|| ($optionTypeList[$optionId] != 'text' && (!isset($optionValues[$optionId]) || !is_array($optionValues[$optionId])))) {
							continue;
						}
						$values = $optionTypeList[$optionId] != 'text' ? $optionValues[$optionId] : array('0');
						if (!empty($values))  {
							foreach ($values as $optionValueId) {
								$value = isset($optionvalue[$optionId][$optionValueId]) && is_array($optionvalue[$optionId][$optionValueId]) ? $optionvalue[$optionId][$optionValueId] : array();
								$sql_data_array = array(
									array('fieldName' => 'product_id', 'value' => $product['product_id'], 'type' => 'integer'),
									array('fieldName' => 'product_option_id', 'value' => $optionId, 'type' => 'integer'),
									array('fieldName' => 'product_option_value_id', 'value' => $optionValueId, 'type' => 'integer'),
									array('fieldName' => 'required', 'value' => in_array($optionId, $optionRequireds) ? 1 : 0, 'type' => 'integer'),
									array('fieldName' => 'price', 'value' => isset($value['price']) ? $value['price'] : '0.00', 'type' => 'string'),
									array('fieldName' => 'price_prefix', 'value' => isset($value['price_prefix']) && in_array($value['price_prefix'], array('+', '-')) ? $value['price_prefix'] : '+', 'type' => 'string'),
								);
								$db->perform(TABLE_PRODUCT_ATTRIBUTE, $sql_data_array);
							}
						}
					}
				}
			}
			$message_stack->add_session('product', '产品设置已保存。', 'success');
			redirect(href_link(FILENAME_PRODUCT, 'product_id=' . $product['product_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('product', '删除产品时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_ATTRIBUTE . " WHERE product_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_PRODUCT_TO_CATEGORY . " WHERE product_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_PRODUCT . " WHERE product_id = " . (int)$val);
			}
			$message_stack->add_session('product', '产品已删除。', 'success');
		}
		redirect(href_link(FILENAME_PRODUCT, get_all_get_params(array('action'))));
	break;
	case 'set_in_stock':
		$db->Execute("UPDATE " . TABLE_PRODUCT . " SET in_stock = IF(in_stock = 1, 0, 1) WHERE product_id = " . (int)$product_id);
		redirect(href_link(FILENAME_PRODUCT));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_PRODUCT . " SET status = IF(status = 1, 0, 1) WHERE product_id = " . (int)$product_id);
		redirect(href_link(FILENAME_PRODUCT));
	break;
	default:
		if ($product_id > 0) {
			$sql = "SELECT product_id, sku, name, short_description, description,
						   image, url, price, specials_price, specials_expire_date,
						   master_category_id, meta_title, meta_keywords,meta_description,
						   stock_qty, in_stock, status, sort_order, viewed, ordered, group_name, " . implode(', ', $productFilterFields) . "
					FROM   " . TABLE_PRODUCT . "
					WHERE  product_id = :productID";
			$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$product = array(
					'product_id'           => $result->fields['product_id'],
					'sku'                  => $result->fields['sku'],
					'name'                 => $result->fields['name'],
					'short_description'    => $result->fields['short_description'],
					'description'          => $result->fields['description'],
					'image'                => $result->fields['image'],
					'url'                  => $result->fields['url'],
					'price'                => $result->fields['price'],
					'specials_price'       => $result->fields['specials_price'],
					'specials_expire_date' => $result->fields['specials_expire_date'],
					'master_category_id'   => $result->fields['master_category_id'],
					'meta_title'           => $result->fields['meta_title'],
					'meta_keywords'        => $result->fields['meta_keywords'],
					'meta_description'     => $result->fields['meta_description'],
					'stock_qty'            => $result->fields['stock_qty'],
					'in_stock'             => $result->fields['in_stock'],
					'status'               => $result->fields['status'],
					'sort_order'           => $result->fields['sort_order'],
					'viewed'               => $result->fields['viewed'],
					'ordered'              => $result->fields['ordered'],
					'group_name'           => $result->fields['group_name']
				);
				//Product Filter
				foreach ($productFilterFields as $field) {
					$product[$field] = $result->fields[$field];
				}
				//product_to_category
				$sql = "SELECT category_id
						FROM   " . TABLE_PRODUCT_TO_CATEGORY . "
						WHERE  product_id = :productID";
				$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
				$categoryResult = $db->Execute($sql);
				$productToCategory = array();
				while (!$categoryResult->EOF) {
					$productToCategory[] = $categoryResult->fields['category_id'];
					$categoryResult->MoveNext();
				}
				//product_attribute
				$sql = "SELECT product_option_id, product_option_value_id, required, price, price_prefix
						FROM   " . TABLE_PRODUCT_ATTRIBUTE . "
						WHERE  product_id = :productID";
				$sql = $db->bindVars($sql, ':productID', $product_id, 'integer');
				$attributResult = $db->Execute($sql);
				while (!$attributResult->EOF) {
					$productAttribute[$attributResult->fields['product_option_id']]['required'] = $attributResult->fields['required'];
					$productAttribute[$attributResult->fields['product_option_id']]['optionValues'][$attributResult->fields['product_option_value_id']] = array(
						'product_option_id'       => $attributResult->fields['product_option_id'],
						'product_option_value_id' => $attributResult->fields['product_option_value_id'],
						'required'                => $attributResult->fields['required'],
						'price'                   => $attributResult->fields['price'],
						'price_prefix'            => $attributResult->fields['price_prefix'],
					);
					$attributResult->MoveNext();
				}
			}
		} else {
			//Filter
			$productListFilter = '';
			if (isset($_GET['filter_id']) && not_null($_GET['filter_id'])) {
				$sql = " AND p.product_id = :id";
				$productListFilter .= $db->bindVars($sql, ':id', trim($_GET['filter_id']), 'integer');
			}
			if (isset($_GET['filter_sku']) && not_null($_GET['filter_sku'])) {
				$sql = " AND sku LIKE '%:sku%'";
				$productListFilter .= $db->bindVars($sql, ':sku', trim($_GET['filter_sku']), 'noquotestring');
			}
			if (isset($_GET['filter_name']) && not_null($_GET['filter_name'])) {
				$sql = " AND name LIKE '%:name%'";
				$productListFilter .= $db->bindVars($sql, ':name', trim($_GET['filter_name']), 'noquotestring');
			}
			if (isset($_GET['filter_price']) && not_null($_GET['filter_price'])) {
				$sql = " AND price LIKE '%:price%'";
				$productListFilter .= $db->bindVars($sql, ':price', trim($_GET['filter_price']), 'noquotestring');
			}
			if (isset($_GET['filter_specials_price']) && not_null($_GET['filter_specials_price'])) {
				$sql = " AND specials_price LIKE '%:specials_price%'";
				$productListFilter .= $db->bindVars($sql, ':specials_price', trim($_GET['filter_specials_price']), 'noquotestring');
			}
			if (isset($_GET['filter_in_stock']) && not_null($_GET['filter_in_stock'])) {
				$sql = " AND in_stock = :in_stock";
				$productListFilter .= $db->bindVars($sql, ':in_stock', trim($_GET['filter_in_stock']), 'integer');
			}
			if (isset($_GET['filter_status']) && not_null($_GET['filter_status'])) {
				$sql = " AND status = :status";
				$productListFilter .= $db->bindVars($sql, ':status', trim($_GET['filter_status']), 'integer');
			}
			if (isset($_GET['filter_category_id']) && not_null($_GET['filter_category_id'])) {
				//subcategories
				$subcategories = $category_tree->getSubcategories('', trim($_GET['filter_category_id']));
				if (count($subcategories) > 0) {
					$sql = " AND ptc.category_id IN (:categoryIDS)";
					$productListFilter .= $db->bindVars($sql, ':categoryIDS', implode(',', $subcategories), 'noquotestring');
				} else {
					$sql = " AND ptc.category_id = :categoryID";
					$productListFilter .= $db->bindVars($sql, ':categoryID', trim($_GET['filter_category_id']), 'integer');
				}
			}
			$productListQuery = "SELECT DISTINCT(p.product_id), p.sku, p.name, p.image, p.price,
										p.specials_price, p.stock_qty, p.in_stock, p.status, p.sort_order
								 FROM   " . TABLE_PRODUCT . " p LEFT JOIN " . TABLE_PRODUCT_TO_CATEGORY . " ptc ON p.product_id = ptc.product_id
								 WHERE  1=1" . $productListFilter . "
								 ORDER BY p.sort_order, p.name";
			//Pos Query
			$pos_to = strlen($productListQuery);
			$pos_from = strpos($productListQuery, ' FROM', 0);
			$posQuery = substr($productListQuery, $pos_from, ($pos_to - $pos_from));
			//Total
			$sql = "SELECT COUNT(DISTINCT p.product_id) AS total " . $posQuery;
			$result = $db->Execute($sql);
			$pagerConfig = array(
				'total'          => $result->fields['total'],
				'availableLimit' => array(50, 200, 500),
				'currentLimit'   => 50
			);
			require(DIR_FS_ADMIN_CLASSES . 'pager.php');
			$pager = new pager($pagerConfig);
			$result = $db->Execute($productListQuery, $pager->getLimitSql());
			$productList = array();
			while (!$result->EOF) {
				$productList[] = array(
					'product_id'     => $result->fields['product_id'],
					'image'          => $result->fields['image'],
					'sku'            => $result->fields['sku'],
					'name'           => $result->fields['name'],
					'price'          => $result->fields['price'],
					'specials_price' => $result->fields['specials_price'],
					'stock_qty'      => $result->fields['stock_qty'],
					'in_stock'       => $result->fields['in_stock'],
					'status'         => $result->fields['status']
				);
				$result->MoveNext();
			}
		}
	break;
}

if ($action == 'new' || $action == 'save' || $product_id > 0) {
	if (!isset($productAttribute) || !is_array($productAttribute)) $productAttribute = array();
	//product_option
	$sql = "SELECT product_option_id, `type`, `name`
			FROM   " . TABLE_PRODUCT_OPTION . "
			ORDER BY sort_order ASC";
	$optionResult  = $db->Execute($sql);
	$productOption = array();
	while (!$optionResult->EOF) {
		$productOption[] = array(
			'option_id' => $optionResult->fields['product_option_id'],
			'type'      => $optionResult->fields['type'],
			'name'      => $optionResult->fields['name']
		);
		$optionResult->MoveNext();
	}
	//product_option_value
	$sql = "SELECT product_option_value_id, product_option_id, `name`
			FROM   " . TABLE_PRODUCT_OPTION_VALUE . "
			ORDER BY sort_order ASC";
	$optionValueResult  = $db->Execute($sql);
	$productOptionValue = array();
	while (!$optionValueResult->EOF) {
		$productOptionValue[] = array(
			'option_value_id' => $optionValueResult->fields['product_option_value_id'],
			'option_id'       => $optionValueResult->fields['product_option_id'],
			'name'            => $optionValueResult->fields['name']
		);
		$optionValueResult->MoveNext();
	}
	$optionTypeNameList = array(
		'select'    => '下拉',
		'text'      => '文本',
		'radio'     => '单选',
		'checkbox'  => '复选',
		'list'      => '列表',
		'wholesale' => '批发'
	);
}

//currency
$new_currency = currency_exists(STORE_CURRENCY);
if ($new_currency == false) $new_currency = currency_exists(STORE_CURRENCY, true);
require(DIR_FS_ADMIN_CLASSES . 'currencies.php');
$currencies = new currencies($new_currency);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>产品管理</title>
<meta name="robot" content="noindex, nofollow" />
<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_ADMIN; ?>" />
<link href="css/styles.css" type="text/css" rel="stylesheet" />
<link href="css/styles-ie.css" type="text/css" rel="stylesheet" />
<link href="css/ui.custom.css" type="text/css" rel="stylesheet" />
<link href="js/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet" />
<script src="js/jquery/jquery.js" type="text/javascript"></script>
<script src="js/jquery/base.js" type="text/javascript"></script>
<script src="js/jquery/tabs.js" type="text/javascript"></script>
<script src="js/jquery/ui.custom.min.js" type="text/javascript"></script>
<script src="js/umeditor/umeditor.config.js" type="text/javascript"></script>
<script src="js/umeditor/umeditor.min.js" type="text/javascript"></script>
<script src="js/umeditor/lang/zh-cn/zh-cn.js" type="text/javascript"></script>
</head>
<body>
<div class="wrapper">
	<?php require(DIR_FS_ADMIN_INCLUDES . 'noscript.php'); ?>
	<div class="page">
    	<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
    	<div class="main-container">
    		<div class="main">
    		<?php if ($message_stack->size('product') > 0) echo $message_stack->output('product'); ?>
    		<?php if ($action == 'new' || $action == 'save' || $product_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_PRODUCT, 'action=save'); ?>" method="post">
				<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
					<input type="hidden" value="<?php echo isset($product['product_id'])?$product['product_id']:''; ?>" name="product[product_id]" />
				</div>
    			<div class="page-title title-buttons">
	    			<h1>产品管理</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_PRODUCT); ?>');"><span><span>取消</span></span></button>
	    		</div>
			    <div class="columns">
				    <div id="vtabs" class="vtabs">
					    <a class="product" href="#tab-product">产品信息</a>
					    <a class="attribute" href="#tab-attribute">属性</a>
					    <a class="option" href="#tab-option">选项</a>
					    <a class="product-group" href="#tab-group">相关产品</a>
				    </div>
				    <div class="main-col">
					    <div id="tab-general" class="main-col-inner">
						    <div id="tab-product">
							    <table class="form-list">
								    <tbody>
								    <tr>
									    <td class="label"><label for="product-sku">产品型号 <span class="required">*</span></label></td>
									    <td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($product['sku'])?$product['sku']:''; ?>" name="product[sku]" id="product-sku" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-name">产品名称 <span class="required">*</span></label></td>
									    <td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($product['name'])?$product['name']:''; ?>" name="product[name]" id="product-name" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-short_description">产品简短描述 </label></td>
									    <td class="value"><textarea cols="15" rows="2" class="required-entry" name="product[short_description]" id="product-short_description"><?php echo isset($product['short_description'])?$product['short_description']:''; ?></textarea></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-description">产品描述</label></td>
									    <td class="value" id="umWrap">
											<textarea name="product[description]" id="product-description"><?php echo isset($product['description'])?$product['description']:''; ?></textarea>
										</td>
										<script type="text/javascript">
											$(function(){
												var fatherWidth = $('#umWrap').width();
												var um = UM.getEditor('product-description', {
													initialFrameWidth: fatherWidth * 0.98 //设置编辑器宽度
													,initialFrameHeight:300
													,toolbar:[
														'source | undo redo | bold italic underline strikethrough horizontal | superscript subscript | forecolor backcolor | removeformat |',
														'insertorderedlist insertunorderedlist | selectall cleardoc paragraph | fontfamily fontsize' ,
														'| justifyleft justifycenter justifyright justifyjustify'
													]
												});
											});
										</script>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-image">产品图片</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['image'])?$product['image']:''; ?>" name="product[image]" id="product-image" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-url">产品路径</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['url'])?$product['url']:''; ?>" name="product[url]" id="product-url" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-price">原价(<?php echo $currencies->get_code(); ?>) <span class="required">*</span></label></td>
									    <td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($product['price'])?$product['price']:''; ?>" name="product[price]" id="product-price" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-specials_price">特价(<?php echo $currencies->get_code(); ?>)</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['specials_price'])?$product['specials_price']:''; ?>" name="product[specials_price]" id="product-specials_price" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-specials_expire_date">特价结束日期</label></td>
									    <td class="value"><input type="text" class="input-text date" value="<?php echo isset($product['specials_expire_date'])?$product['specials_expire_date']:''; ?>" name="product[specials_expire_date]" id="product-specials_expire_date" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-master_category_id">产品主分类</label></td>
									    <td class="value">
										    <select class="required-entry" name="product[master_category_id]" id="product-master_category_id">
											    <option value="0"> --- 无 --- </option>
											    <?php foreach ($availabCategoryList as $key => $val) { ?>
												    <option<?php if (isset($product['master_category_id'])&&$product['master_category_id']==$key) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
											    <?php } ?>
										    </select>
									    </td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product_to_category">产品分类</label></td>
									    <td class="value">
										    <select class="multiselect" multiple="multiple" name="product_to_category[]" id="product_to_category">
											    <?php foreach ($availabCategoryList as $key => $val) { ?>
												    <option<?php if (isset($productToCategory)&&in_array($key, $productToCategory)) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
											    <?php } ?>
										    </select>
									    </td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-meta_title">Meta标签标题</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['meta_title'])?$product['meta_title']:''; ?>" name="product[meta_title]" id="product-meta_title" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-meta_keywords">Meta标签关键词</label></td>
									    <td class="value"><textarea cols="15" rows="2" name="product[meta_keywords]" id="product-meta_keywords"><?php echo isset($product['meta_keywords'])?$product['meta_keywords']:''; ?></textarea></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-meta_description">Meta标签描述</label></td>
									    <td class="value"><textarea cols="15" rows="2" name="product[meta_description]" id="product-meta_description"><?php echo isset($product['meta_description'])?$product['meta_description']:''; ?></textarea></td>
								    </tr>
									<tr>
									    <td class="label"><label for="product-stock_qty">库存数量(0:不限)</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['stock_qty'])?$product['stock_qty']:'0'; ?>" name="product[stock_qty]" id="product-stock_qty" /></td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-in_stock">库存</label></td>
									    <td class="value">
										    <select name="product[in_stock]" id="product-in_stock">
											    <?php foreach ($availabInStock as $_key=>$_val) { ?>
												    <option<?php if (isset($product['in_stock'])&&$product['in_stock']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
											    <?php } ?>
										    </select>
									    </td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-status">状态</label></td>
									    <td class="value">
										    <select name="product[status]" id="product-status">
											    <?php foreach ($availabStatus as $_key=>$_val) { ?>
												    <option<?php if (isset($product['status'])&&$product['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
											    <?php } ?>
										    </select>
									    </td>
								    </tr>
								    <tr>
									    <td class="label"><label for="product-sort_order">排序</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['sort_order'])?$product['sort_order']:'0'; ?>" name="product[sort_order]" id="product-sort_order" /></td>
								    </tr>
									<tr>
									    <td class="label"><label for="product-viewed">浏览量</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['viewed'])?$product['viewed']:'0'; ?>" name="product[viewed]" id="product-viewed" /></td>
								    </tr>
									<tr>
									    <td class="label"><label for="product-ordered">销售量</label></td>
									    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product['ordered'])?$product['ordered']:'0'; ?>" name="product[ordered]" id="product-ordered" /></td>
								    </tr>
								    </tbody>
							    </table>
						    </div>
						    <div id="tab-attribute">
							    <table class="form-list">
								    <tbody>
								    <?php //Product Filter ?>
								    <?php foreach ($productFilterFields as $field) { ?>
									    <?php if (defined('PRODUCT_' . strtoupper($field))) { ?>
										    <tr>
											    <td class="label"><label for="product-<?php echo $field?>"><?php echo $field; ?> <?php eval('$str = PRODUCT_' . strtoupper($field) . ';'); ?><?php echo empty($str) ? '' : sprintf("(%s)", $str); ?></label></td>
											    <td class="value"><input type="text" class="input-text" value="<?php echo isset($product[$field])?$product[$field]:''; ?>" name="product[<?php echo $field?>]" id="product-<?php echo $field?>" /></td>
										    </tr>
									    <?php } ?>
								    <?php } ?>
								    </tbody>
							    </table>
						    </div>
						    <div id="tab-option">
							    <table id="product-attribute" class="data-table">
								    <thead>
								    <tr>
									    <th>选项名称</th>
									    <th>选项值</th>
									    <th class="a-center">价格</th>
								    </tr>
								    </thead>
								    <?php foreach ($productOption as $option) {?>
									    <tbody>
									    <tr>
										    <td>
											    <input type="checkbox" name="options[]" value="<?php echo $option['option_id']?>" id="<?php echo $option['option_id']?>"<?php echo isset($productAttribute[$option['option_id']]) ? ' checked="checked"' : ''; ?> class="checkbox" onclick="choseOp(this)" />
											    <label for="<?php echo $option['option_id']?>"><?php echo $option['name']; ?></label> (<?php echo $optionTypeNameList[$option['type']]; ?>)
										    </td>
										    <td>
											    <input type="checkbox" class="checkbox" name="option_requireds[]" value="<?php echo $option['option_id']?>"<?php echo isset($productAttribute[$option['option_id']]['required']) && $productAttribute[$option['option_id']]['required'] ? ' checked="checked"' : '' ; ?> />必需
										    </td>
										    <?php if ($option['type'] == 'text') { ?>
											    <td>
												    <select style="width: 35px" name="option_value[<?php echo $option['option_id']; ?>][0][price_prefix]">
													    <option value="+"<?php echo isset($productAttribute[$option['option_id']]['optionValues'][0]['price_prefix']) && $productAttribute[$option['option_id']]['optionValues'][0]['price_prefix'] == '+' ? ' selected="selected"' : ''; ?> >+</option>
													    <option value="-"<?php echo isset($productAttribute[$option['option_id']]['optionValues'][0]['price_prefix']) && $productAttribute[$option['option_id']]['optionValues'][0]['price_prefix'] == '-' ? ' selected="selected"' : ''; ?> >-</option>
												    </select>
												    <input type="text" class="input-text" name="option_value[<?php echo $option['option_id']; ?>][0][price]" value="<?php echo isset($productAttribute[$option['option_id']]['optionValues'][0]['price']) ? $productAttribute[$option['option_id']]['optionValues'][0]['price'] : '0.00'; ?>" />
											    </td>
										    <?php } ?>
									    </tr>
									    </tbody>
									    <?php if ($option['type'] != 'text') { ?>
										    <tbody id="option-<?php echo $option['option_id'] ?>">
										    <?php foreach ($productOptionValue as $optionValue) { ?>
											    <?php if ($optionValue['option_id'] == $option['option_id']) { ?>
												    <tr id="tr-option-<?php echo $option['option_id']; ?>-<?php echo $optionValue['option_value_id']; ?>"  >
													    <td></td>
													    <td>
														    <input type="checkbox" class="checkbox" name="option_values[<?php echo $option['option_id']; ?>][]" value="<?php echo $optionValue['option_value_id']; ?>" id="option-<?php echo $option['option_id']; ?>-<?php echo $optionValue['option_value_id']; ?>"<?php echo isset($productAttribute[$option['option_id']]['optionValues'][$optionValue['option_value_id']]) ? ' checked="checked"' : ''; ?> />
														    <?php echo $optionValue['name']; ?>
													    </td>
													    <td>
														    <select style="width:35px;" name="option_value[<?php echo $option['option_id']; ?>][<?php echo $optionValue['option_value_id']; ?>][price_prefix]">
															    <option value="+"<?php echo isset($productAttribute[$option['option_id']]['optionValues'][$optionValue['option_value_id']]['price_prefix']) && $productAttribute[$option['option_id']]['optionValues'][$optionValue['option_value_id']]['price_prefix'] == '+' ? ' selected="selected"' : ''; ?> >+</option>
															    <option value="-"<?php echo isset($productAttribute[$option['option_id']]['optionValues'][$optionValue['option_value_id']]['price_prefix']) && $productAttribute[$option['option_id']]['optionValues'][$optionValue['option_value_id']]['price_prefix'] == '-' ? ' selected="selected"' : ''; ?> >-</option>
														    </select>
														    <input type="text" class="input-text" name="option_value[<?php echo $option['option_id']; ?>][<?php echo $optionValue['option_value_id']; ?>][price]" value="<?php echo isset($productAttribute[$option['option_id']]['optionValues'][$optionValue['option_value_id']]['price']) ? $productAttribute[$option['option_id']]['optionValues'][$optionValue['option_value_id']]['price'] : '0.00'; ?>" />
													    </td>
												    </tr>
											    <?php }?>
										    <?php }?>
										    </tbody>
									    <?php } ?>
								    <?php } ?>
							    </table>
						    </div>
							<div id="tab-group">
								<table class="form-list">
									<tbody>
									<tr>
										<td class="label"><label for="group-name">产品分组名称<br>（只能由大小写字母与数字组成，填0则不分组） <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($product['group_name'])?$product['group_name']:''; ?>" name="product[group_name]" id="group-name" /></td>
									</tr>
									</tbody>
								</table>
							</div>
					    </div>
				    </div>
			    </div>
    			</form>
<script type="text/javascript"><!--
$('.date').datepicker({dateFormat: 'yy-mm-dd'});
$('#vtabs a').tabs();
function choseOp(checkbox) {
	if (!checkbox.checked) {
		$('input[id^=option-'+checkbox.id.toString()+'-]').prop("checked", false);
	}
}
//--></script>
    		<?php } else { ?>
    			<form action="<?php echo href_link(FILENAME_PRODUCT, get_all_get_params(array('action')) . 'action=delete'); ?>" method="post" id="productFm">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>产品管理</h1>
	    			分类:
	    			<select onchange="filter();" id="filter_category_id">
						<option value="">全部</option>
						<?php foreach ($availabCategoryList as $key => $val) { ?>
						<option<?php if (isset($_GET['filter_category_id']) && $_GET['filter_category_id']==$key) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
						<?php } ?>
					</select>
    				<button type="button" class="button button-new" onclick="setLocation('<?php echo href_link(FILENAME_PRODUCT, 'action=new'); ?>');"><span><span>新增</span></span></button>
    				<button type="button" class="button" onclick="if(confirm('删除或卸载后您将不能恢复，请确定要这么做吗？')) $('#productFm').submit();"><span><span>删除</span></span></button>
	    		</div>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col width="60" />
	    			<col width="60" />
	    			<col />
	    			<col />
	    			<col width="80" />
					<col width="80" />
	    			<col width="80" />
	    			<col width="60" />
	    			<col width="60" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th class="a-center">图片</th>
	    				<th>ID#</th>
	    				<th>产品型号</th>
	    				<th>产品名称</th>
	    				<th class="a-right">价格</th>
	    				<th class="a-right">特价</th>
						<th class="a-center">库存数量</th>
	    				<th class="a-center">库存</th>
	    				<th class="a-center">状态</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<tbody>
	    			<tr class="filter">
						<td></td>
						<td></td>
						<td class="value"><input type="text" class="input-text" size="1" value="<?php echo isset($_GET['filter_id'])?$_GET['filter_id']:''; ?>" id="filter_id" /></td>
						<td class="value"><input type="text" class="input-text" size="30" value="<?php echo isset($_GET['filter_sku'])?$_GET['filter_sku']:''; ?>" id="filter_sku" /></td>
						<td class="value"><input type="text" class="input-text" size="30" value="<?php echo isset($_GET['filter_name'])?$_GET['filter_name']:''; ?>" id="filter_name" /></td>
						<td class="value a-right"><input type="text" class="input-text a-right" size="5" value="<?php echo isset($_GET['filter_price'])?$_GET['filter_price']:''; ?>" id="filter_price" /></td>
						<td class="value a-right"><input type="text" class="input-text a-right" size="5" value="<?php echo isset($_GET['filter_specials_price'])?$_GET['filter_specials_price']:''; ?>" id="filter_specials_price" /></td>
						<td class="value a-center"></td>
						<td class="value a-center">
							<select id="filter_in_stock">
								<option value=''>全部</option>
								<?php foreach ($availabInStock as $key => $val) { ?>
								<option<?php if (isset($_GET['filter_in_stock']) && $_GET['filter_in_stock']==$key) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>
								<?php } ?>
							</select>
						</td>
						<td class="value a-center">
							<select id="filter_status">
								<option value=''>全部</option>
								<?php foreach ($availabStatus as $key => $val) { ?>
								<option<?php if (isset($_GET['filter_status']) && $_GET['filter_status']==$key) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>
								<?php } ?>
							</select>
						</td>
						<td class="a-center"><button type="button" class="button" onclick="filter();"><span><span>筛选</span></span></button></td>
		            </tr>
		        </tbody>
	    		<?php if (count($productList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($productList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['product_id']; ?>" name="selected[]" /></td>
	    				<td><img width="<?php echo ADMIN_IMAGE_WIDTH; ?>" height="<?php echo ADMIN_IMAGE_HEIGHT; ?>" src="<?php echo get_image($val['image'], ADMIN_IMAGE_WIDTH, ADMIN_IMAGE_HEIGHT); ?>" /></td>
	    				<td><?php echo $val['product_id']; ?></td>
	    				<td><?php echo $val['sku']; ?></td>
	    				<td><?php echo $val['name']; ?></td>
	    				<td class="a-right"><?php echo $currencies->display_price($val['price']); ?></td>
	    				<td class="a-right"><?php echo $currencies->display_price($val['specials_price']); ?></td>
						<td class="a-center"><?php echo $val['stock_qty'] == 0 ? '不限' : $val['stock_qty']; ?></td>
	    				<td class="a-center">[ <a title="点击 库存:<?php echo $val['in_stock']==1?$availabInStock[0]:$availabInStock[1]; ?>" href="<?php echo href_link(FILENAME_PRODUCT, 'action=set_in_stock&product_id=' . $val['product_id']); ?>"><?php echo $availabInStock[$val['in_stock']]; ?></a> ]</td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_PRODUCT, 'action=set_status&product_id=' . $val['product_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_PRODUCT, 'product_id=' . $val['product_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="11">没有结果！</td>
					</tr>
				</tbody>
	    		<?php } ?>
	    		</table>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		</form>
<script type="text/javascript"><!--
$(function(){
	$(document).keydown(function(event){
		if(event.keyCode==13){
			filter();
		}
	});
});
function filter() {
	var url = '<?php echo href_link(FILENAME_PRODUCT); ?>';
	var key = '';
	var val = '';
	$("[id^='filter_']").each(function(){
		key = $(this).attr('id');
		val = $(this).val();
		if (val) {
			if (url.indexOf('?')>0) {
				url += '&' + key + '=' + encodeURIComponent(val);
			} else {
				url += '?' + key + '=' + encodeURIComponent(val);
			}
		}
	});
	setLocation(url);
}
//--></script>
    		<?php } ?>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>