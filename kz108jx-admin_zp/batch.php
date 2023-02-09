<?php require('includes/application_top.php'); ?>
<?php
@set_time_limit(0);

// 产品表20个过滤字段
$filterList = array();
for ($i = 1; $i <= 20; $i++) {
	$field = 'filter_' . $i;
	if (defined('PRODUCT_'. strtoupper($field))) {
		eval('$filterList[PRODUCT_'. strtoupper($field) . '] = $field;');
	}
}

// 产品修改
$productFilter = array(
	'产品名称'         => 'product_name',
	'产品标题(Meta)'   => 'meta_title',
	'产品关键字(Meta)' => 'meta_keywords',
	'产品描述(Meta)'   => 'meta_description',
	'产品URL'          => 'product_url'
);

// 类目修改
$categoryFilter = array(
	'分类名称'         => 'category_name',
	'分类标题(Meta)'   => 'meta_title',
	'分类关键字(Meta)' => 'meta_keywords',
	'分类描述(Meta)'   => 'meta_description',
	'分类URL'          => 'category_url'
);

// 类目修改
$priceFilter = array(
	'加法' => 1,
	'减法' => 2,
	'乘法' => 3
);

// category_tree
require(DIR_FS_ADMIN_CLASSES . 'category_tree.php');
$category_tree = new category_tree();
$availabCategoryList = $category_tree->getTree();

// POST
$post   = db_prepare_input($_POST);
$action = isset($post['action']) ? $post['action'] : '';
switch ($action) {
	case 'category':
		$category = isset($post['category']) ? $post['category'] : array();
		$categoriesIds = array();
		
		$urlSql = "SELECT category_id, url FROM " . TABLE_CATEGORY;
		$urlResult = $db->Execute($urlSql);
		$urlList = array();
		while (!$urlResult->EOF) {
			$urlList[$urlResult->fields['category_id']] = $urlResult->fields['url'];
			$urlResult->MoveNext();
		}
		
		$sql = "SELECT category_id, sku, name FROM " . TABLE_CATEGORY;
		
		// 获取类目ids
		if (!empty($category) && is_array($category)) {
			foreach ($category as $val) {
				if (array_key_exists($val, $categoriesIds)) {
					continue;
				}
				// 根据类目ID获取其子孙级类目Ids
				$categoriesIds[$val] = $val;
				$categoriesIds = $category_tree->getSubcategories($categoriesIds, $val);
			}
				
			if (empty($categoriesIds)) {
				$message_stack->add('batch', '该类目下没有类目');
				break;
			}
				
			$categoriesIds = array_keys($categoriesIds);
			$sql .= " where category_id in (:categoryId)";
			$sql = $db->bindVars($sql, ':categoryId', implode(',', $categoriesIds), 'noquotestring');
		}
		
		$result = $db->Execute($sql);
		while (!$result->EOF) {
			$updateData = array();
			if (!empty($post['category_name'])) {
				$name = str_ireplace('{category_name}', $result->fields['name'], $post['category_name']);
				$name = trim(preg_replace('/\s(?=\s)/', '', $name));
				$updateData[] = array('fieldName'=>'name', 'value'=>$name, 'type'=>'string');
			}
			if (!empty($post['meta_title'])) {
				$title = str_ireplace('{category_name}', $result->fields['name'], $post['meta_title']);
				$title = trim(preg_replace('/\s(?=\s)/', '', $title));
				$updateData[] = array('fieldName'=>'meta_title', 'value'=>$title, 'type'=>'string');
			}
			if (!empty($post['meta_keywords'])) {
				$keywords = str_ireplace('{category_name}', $result->fields['name'], $post['meta_keywords']);
				$keywords = trim(preg_replace('/\s(?=\s)/', '', $keywords));
				$updateData[] = array('fieldName'=>'meta_keywords', 'value'=>$keywords, 'type'=>'string');
			}
			if (!empty($post['meta_description'])) {
				$description = str_ireplace('{category_name}', $result->fields['name'], $post['meta_description']);
				$description = trim(preg_replace('/\s(?=\s)/', '', $description));
				$updateData[] = array('fieldName'=>'meta_description', 'value'=>$description, 'type'=>'string');
			}
			if (!empty($post['category_url'])) {
				$url = str_ireplace('{category_name}', $result->fields['name'], $post['category_url']);
				$pattern = "([[:punct:]])";
				$url = preg_replace($pattern, '', strtolower($url));
				$url = trim(preg_replace('/\s(?=\s)/', '', $url));
				$pattern = "([[:space:]]|[[:blank:]])";
				$url = preg_replace($pattern, URL_REWRITE_CONNECTOR, $url);
				
				$tempUrl = $urlList[$result->fields['category_id']];
				unset($urlList[$result->fields['category_id']]);
				if (in_array($url, $urlList)) {
					$url .= $result->fields['category_id'];
				}
				$urlList[$result->fields['category_id']] = $tempUrl;
				
				$updateData[] = array('fieldName'=>'url', 'value'=>$url, 'type'=>'string');
			}
			if (!empty($updateData)) {
				$updateData[] = array('fieldName'=>'last_modified', 'value'=> date('Y-m-d H:i:s'), 'type'=>'date');
				if (!$db->perform(TABLE_CATEGORY, $updateData, 'UPDATE', 'category_id=' . $result->fields['category_id'])) {
					$message_stack->add('batch', '分类SKU: ' . $result->fields['sku'] . ' 更新失败 - ' . $result->fields['category_id']);
				} else {
					if (!empty($post['category_url'])) {
						$urlList[$result->fields['category_id']] = $url;
					}
				}
			}
			$result->MoveNext();
		}
		$message_stack->add('batch', '分类更新完成', 'success');
		break;
	case 'product':
		$productCategory = isset($post['category']) ? $post['category'] : array();
		$categoriesIds = array();
		$original = array('{product_name}');
		$str = array();
		for ($i = 1; $i <= 20; $i++) {
			$original[] = '{filter_' . $i . '}';
			$str[] = 'filter_' . $i;
		}

		$sql = "SELECT product_id, sku, name, " . implode(', ', $str) . "
				FROM   " . TABLE_PRODUCT;
		// 获取类目ids
		if (!empty($productCategory) && is_array($productCategory)) {
			foreach ($productCategory as $val) {
				if (array_key_exists($val, $categoriesIds)) {
					continue;
				}
				// 根据类目ID获取其子孙级类目Ids
				$categoriesIds[$val] = $val;
				$categoriesIds = $category_tree->getSubcategories($categoriesIds, $val);
			}
			
			if (empty($categoriesIds)) {
				$message_stack->add('batch', '该类目下没有产品');
				break;
			}
			
			$categoriesIds = array_keys($categoriesIds);
			$sql .= " where master_category_id in (:masterIds)";
			$sql = $db->bindVars($sql, ':masterIds', implode(',', $categoriesIds), 'noquotestring');
		}
		
		$urlSql = "SELECT product_id, url FROM " . TABLE_PRODUCT;
		$urlResult = $db->Execute($urlSql);
		$urlList = array();
		while (!$urlResult->EOF) {
			$urlList[$urlResult->fields['product_id']] = $urlResult->fields['url'];
			$urlResult->MoveNext();
		}

		$rows = 500;
		for ($i = 1; $i <= 1000; $i++) {
			$limit = ' LIMIT ' . (($i - 1) * $rows) . ', ' . $rows;
			$result = $db->Execute($sql . $limit);
			
			if ($result->RecordCount() < 1) {
				break;
			}
			
			while (!$result->EOF) {
				$replace = array($result->fields['name']);
				for ($j = 1; $j <= 20; $j++) {
					$replace[] = !empty($result->fields['filter_' . $i]) ? $result->fields['filter_' . $i] : '';
				}
				$updateData = array();
				if (!empty($post['product_name'])) {
					$name = str_ireplace($original, $replace, $post['product_name']);
					$name = trim(preg_replace('/\s(?=\s)/', '', $name));
					$updateData[] = array('fieldName'=>'name', 'value'=>$name, 'type'=>'string');
				}
				if (!empty($post['meta_title'])) {
					$title = str_ireplace($original, $replace, $post['meta_title']);
					$title = trim(preg_replace('/\s(?=\s)/', '', $title));
					$updateData[] = array('fieldName'=>'meta_title', 'value'=>$title, 'type'=>'string');
				}
				if (!empty($post['meta_keywords'])) {
					$keywords = str_ireplace($original, $replace, $post['meta_keywords']);
					$keywords = trim(preg_replace('/\s(?=\s)/', '', $keywords));
					$updateData[] = array('fieldName'=>'meta_keywords', 'value'=>$keywords, 'type'=>'string');
				}
				if (!empty($post['meta_description'])) {
					$description = str_ireplace($original, $replace, $post['meta_description']);
					$description = trim(preg_replace('/\s(?=\s)/', '', $description));
					$updateData[] = array('fieldName'=>'meta_description', 'value'=>$description, 'type'=>'string');
				}
				if (!empty($post['product_url'])) {
					$url = str_ireplace($original, $replace, $post['product_url']);
					$pattern = "([[:punct:]])";
					$url = preg_replace($pattern, '', strtolower($url));
					$url = trim(preg_replace('/\s(?=\s)/', '', $url));
					$pattern = "([[:space:]]|[[:blank:]])";
					$url = preg_replace($pattern, URL_REWRITE_CONNECTOR, $url);
					
					$tempUrl = $urlList[$result->fields['product_id']];
					unset($urlList[$result->fields['product_id']]);
					if (in_array($url, $urlList)) {
						$url .= $result->fields['product_id'];
					}
					$urlList[$result->fields['product_id']] = $tempUrl;
					
					$updateData[] = array('fieldName'=>'url', 'value'=>$url, 'type'=>'string');
				}
				if (!empty($updateData)) {
					$updateData[] = array('fieldName'=>'last_modified', 'value'=> date('Y-m-d H:i:s'), 'type'=>'date');
					if (!$db->perform(TABLE_PRODUCT, $updateData, 'UPDATE', 'product_id=' . $result->fields['product_id'])) {
						$message_stack->add('batch', '产品SKU: ' . $result->fields['sku'] . ' 更新失败 - ' . $result->fields['product_id']);
					} else {
						$urlList[$result->fields['product_id']] = $url;
					}
				}
				$result->MoveNext();
			}
		}
		$message_stack->add('batch', '产品更新完成', 'success');
		break;
	case 'price':
		$priceCategory = isset($post['category']) ? $post['category'] : array();
		$categoriesIds = array();
		
		// 全部为空则跳出
		if (strlen($post['price']) <= 0 && strlen($post['specials_price']) <= 0) {
			$message_stack->add('batch', '您输入的格式错误');
			break;
		}
		
		$sql = "SELECT product_id, sku, price, specials_price FROM " . TABLE_PRODUCT;
		
		if (!empty($priceCategory) && is_array($priceCategory)) {
			foreach ($priceCategory as $val) {
				if (array_key_exists($val, $categoriesIds)) {
					continue;
				}
				// 根据类目ID获取其子孙级类目Ids
				$categoriesIds[$val] = $val;
				$categoriesIds = $category_tree->getSubcategories($categoriesIds, $val);
			}
			
			if (empty($categoriesIds)) {
				$message_stack->add('batch', '该类目下没有产品');
				break;
			}
			
			$categoriesIds = array_keys($categoriesIds);
			$sql .= " where master_category_id in (:masterIds)";
			$sql = $db->bindVars($sql, ':masterIds', implode(',', $categoriesIds), 'noquotestring');
		}
		
		$rows = 500;
		for ($i = 1; $i <= 1000; $i++) {
			$limit = ' LIMIT ' . (($i - 1) * $rows) . ', ' . $rows;
			$result = $db->Execute($sql . $limit);
				
			if ($result->RecordCount() < 1) {
				break;
			}
			
			while (!$result->EOF) {
				$updateData = array();
				if (is_numeric($post['price']) && $post['price'] > 0) {
					switch ($post['product_price']) {
						case '1':
							$updateData[] = array('fieldName'=>'price', 'value'=>($result->fields['price'] + $post['price']), 'type'=>'float');
							break;
						case '2':
							if ($result->fields['price'] <= $post['price']) {
								$message_stack->add('batch', '产品原价SKU: ' . $result->fields['sku'] . ' 输入的价格不能比当前价格高');
							}
							$updateData[] = array('fieldName'=>'price', 'value'=>($result->fields['price'] - $post['price']), 'type'=>'float');
							break;
						case '3':
							$updateData[] = array('fieldName'=>'price', 'value'=>($result->fields['price'] * $post['price']), 'type'=>'float');
							break;
					}
				}
				if (is_numeric($post['specials_price']) && $post['specials_price'] > 0) {
					switch ($post['product_specials']) {
						case '1':
							$updateData[] = array('fieldName'=>'specials_price', 'value'=>($result->fields['specials_price'] + $post['specials_price']), 'type'=>'float');
							break;
						case '2':
							if ($result->fields['price'] <= $post['price']) {
								$message_stack->add('batch', '产品特价SKU: ' . $result->fields['sku'] . ' 输入的价格不能比当前价格高');
							}
							$updateData[] = array('fieldName'=>'specials_price', 'value'=>($result->fields['specials_price'] - $post['specials_price']), 'type'=>'float');
							break;
						case '3':
							$updateData[] = array('fieldName'=>'specials_price', 'value'=>($result->fields['specials_price'] * $post['specials_price']), 'type'=>'float');
							break;
					}
				}
				if (!empty($updateData)) {
					$format = true;
					$updateData[] = array('fieldName'=>'last_modified', 'value'=> date('Y-m-d H:i:s'), 'type'=>'date');
					if (!$db->perform(TABLE_PRODUCT, $updateData, 'UPDATE', 'product_id=' . $result->fields['product_id'])) {
						$message_stack->add('batch', '价格产品SKU: ' . $result->fields['sku'] . ' 更新失败 - ' . $result->fields['product_id']);
					}
				}
				$result->MoveNext();
			}
		}
		$message_stack->add('batch', '价格更新完成', 'success');
		break;
	case 'description':
		// 获取POST的参数
		$descriptionCategory = isset($post['category']) ? $post['category'] : array();
		$width  = isset($post['description_width']) ? (int)$post['description_width'] : 0;
		
		if ($width < 1) {
			$message_stack->add('batch', '输入的宽度或者高度格式错误');
			break;
		}
		
		// 获取一行的字符串
		function getString($str = "", $len = 30)
		{
			$str = trim($str);
			if ($str == "") return $str;
			if (is_array($str)) return $str;
			if ($len==0 || !is_numeric($len)) return $str;
			if (strlen($str) <= $len) return $str;
		
			$str = substr($str, 0, $len);
			$pattern = "/^[a-zA-Z0-9\'\"(]$/i";
			if (!empty($str)) {
				while (strlen($str) && preg_match($pattern, $str[strlen($str)-1])) {
					$str = substr($str, 0, -1);
				}
			}
			return $str;
		}
		
		// 获取类目Ids
		$categoriesIds = array();
		$sql = "SELECT product_id, sku, description FROM " . TABLE_PRODUCT . " WHERE description IS NOT NULL";
		if (!empty($descriptionCategory) && is_array($descriptionCategory)) {
			foreach ($descriptionCategory as $val) {
				if (array_key_exists($val, $categoriesIds)) {
					continue;
				}
				// 根据类目ID获取其子孙级类目Ids
				$categoriesIds[$val] = $val;
				$categoriesIds = $category_tree->getSubcategories($categoriesIds, $val);
			}
				
			if (empty($categoriesIds)) {
				$message_stack->add('batch', '该类目下没有产品');
				break;
			}
			
			$categoriesIds = array_keys($categoriesIds);
			$sql .= " AND master_category_id in (:masterIds)";
			$sql = $db->bindVars($sql, ':masterIds', implode(',', $categoriesIds), 'noquotestring');
		}
		
		// 自定义
		$fontSize   = 10;
		$fontWidth  = 6.4; //imagefontwidth($fontSize);
		$fontHeight = imagefontheight($fontSize);
		$font       = "font/arial.ttf";
		
		$rows = 500;
		for ($i = 1; $i <= 1000; $i++) {
			$limit  = ' LIMIT ' . (($i - 1) * $rows) . ', ' . $rows;
			$result = $db->Execute($sql . $limit);
		
			if ($result->RecordCount() < 1) {
				break;
			}
			
			while (!$result->EOF) {
				$string = trim($result->fields['description']);
				$string = html_entity_decode($string);
				$string = str_replace(array("\r\n", "\r", "\n"), " ", $string);
				$string = trim(preg_replace('/\s(?=\s)/', '', $string));
				
				// img 开头的就是已经生成过的
				$validPattern = "/^<img/";
				if (strlen($string) < 1 || preg_match($validPattern, $string)) {
					$result->MoveNext();
					continue;
				}
				
				// 自定义
				$yHeight   = 8;
				$x         = 20;
				$y         = $yHeight + $fontHeight;
				$length    = floor( ($width - (2 * $x)) / $fontWidth );
				$textArray = array();
				$temArray  = explode('<br>', $string);
				
				// 一般不为空
				if (empty($temArray)) {
					$result->MoveNext();
					continue;
				}
				
				// 获取总共需要循环的行数的数组
				foreach ($temArray as $v) {
					$string = strip_tags($v);
					
					// 绘画开始1
					for ($j = 0; $j < 1000; $j++) {
						if ($j == 0) {
							if (strlen($string) <= ($length - 4)) {
								$textArray[] = '    ' . trim($string);
								break;
							}
							
							$temStr = getString($string, $length - 4);
							$string = substr($string, strlen($temStr));
							$textArray[] = '    ' . trim($temStr);
						} else {
							if (strlen($string) <= $length) {
								$textArray[] = trim($string);
								break;
							}
							
							$temStr = getString($string, $length);
							$string = substr($string, strlen($temStr));
							$textArray[] = trim($temStr);
						}
					}
				}
				
				// 创建画布
				$img   = imagecreate($width, (count($textArray) * ($fontHeight + $yHeight)) + ($yHeight * 2));
				$bg    = imagecolorallocate($img, 255, 255, 255);
				$black = imagecolorallocate($img, 0, 0, 0);
				
				// 绘画开始2
				foreach ($textArray as $key => $val) {
					if ($key != 0) {
						$y += $fontHeight + $yHeight;
					}
					imagettftext($img, $fontSize, 0, $x, $y, $black, $font, $val);
					//imagestring($img, $fontSize, $x, $y, $val, $black);
				}
				
				//header("Content-type: image/jpeg");
				imagejpeg($img, DIR_FS_CATALOG_IMAGES . 'description/'. $result->fields['sku'] .'.jpg');
				imagedestroy($img);
				//header("Content-Type: text/html; charset=utf-8");
				
				// 更新产品描述
				$updateData   = array();
				$updateData[] = array('fieldName'=>'description', 'value'=> '<img src="images/description/'. $result->fields['sku'] .'.jpg" alt="" />', 'type'=>'string');
				$updateData[] = array('fieldName'=>'last_modified', 'value'=> date('Y-m-d H:i:s'), 'type'=>'date');
				if (!$db->perform(TABLE_PRODUCT, $updateData, 'UPDATE', 'product_id=' . $result->fields['product_id'])) {
					$message_stack->add('batch', '价格产品SKU: ' . $result->fields['sku'] . ' 更新失败 - ' . $result->fields['product_id']);
				}
				
				$result->MoveNext();
			}
		}
		$message_stack->add('batch', '描述更新完成', 'success');
		break;
	case 'sql':
		$sqlList = isset($post['sql']) ? $post['sql'] : '';

		if (empty($sqlList)) {
			$message_stack->add('batch', '数据库命令不能为空');
			break;
		} else {
			$sqlList = explode("\r\n", $sqlList);
			foreach ($sqlList as $sql) {
				$urlResult = $db->Execute($sql);
			}
			$message_stack->add('batch', '数据库命令执行成功', 'success');
		}
		break;
	default:
		break;
}

//Update Db Cache
if (!empty($action)) {
	$cache->sql_cache_flush_cache();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>批量管理</title>
<meta name="robot" content="noindex, nofollow" />
<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_ADMIN; ?>" />
<link href="css/styles.css" type="text/css" rel="stylesheet" />
<link href="css/styles-ie.css" type="text/css" rel="stylesheet" />
<script src="js/jquery/jquery.js" type="text/javascript"></script>
<script src="js/jquery/base.js" type="text/javascript"></script>
<script src="js/jquery/tabs.js" type="text/javascript"></script>
</head>
<body>
<div class="wrapper">
	<?php require(DIR_FS_ADMIN_INCLUDES . 'noscript.php'); ?>
	<div class="page">
    	<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
    	<div class="main-container">
    		<div class="main">
    		<?php if ($message_stack->size('batch') > 0) echo $message_stack->output('batch'); ?>
	    		<div class="page-title">
		    		<h1>批量管理</h1>
		    	</div>
	    		<div class="columns">
	    			<div id="vtabs" class="vtabs">
	    				<a class="category" href="#tab-category">分类</a>
	    				<a class="product" href="#tab-product">产品</a>
	    				<a class="price" href="#tab-price">价格</a>
	    				<a class="description" href="#tab-description">描述转图片</a>
	    				<a class="description" href="#tab-sql">执行数据库命令</a>
	    			</div>
	    			<div class="main-col">
	    				<div id="tab-general" class="main-col-inner">
	    					<div id="tab-category">
	    						<form action="<?php echo href_link(FILENAME_BATCH); ?>" method="post">
	    						<table class="form-list">
	    						<tbody>
	    							<tr><td colspan="2"><input type="hidden" name="action" value="category" /></td></tr>
	    							<?php foreach ($categoryFilter as $key => $val) { ?>
	    							<tr>
										<td class="label"><label for="<?php echo $val ?>"><?php echo $key ?></label></td>
										<td class="value"><input type="text" class="input-text" id="<?php echo $val ?>" value="<?php echo (isset($_POST[$val])?$_POST[$val]:'') ?>" name="<?php echo $val ?>" /></td>
									</tr>
									<?php } ?>
									<tr>
										<td class="label"><label for="category-category">产品分类</label></td>
										<td class="value">
											<select class="multiselect" multiple="multiple" name="category[]" id="category-category">
												<?php foreach ($availabCategoryList as $key => $val) { ?>
												<option<?php if (isset($category)&&in_array($key, $category)) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr><td class="label"></td><td class="value"><button type="submit" class="button"><span><span>保存</span></span></button></td></tr>
	    						</tbody>
	    						</table>
	    						</form>
	    						<table class="data-table">
	    						<tbody>
	    							<tr>
	    								<td class="label" width="100">分类名称</td>
	    								<td class="value">{category_name}</td>
	    							</tr>
	    							<tr><td class="label red a-center" colspan="2">如果为空不填则不修改</td></tr>
	    						</tbody>
	    						</table>
	    					</div>
	    					<div id="tab-product">
	    						<form action="<?php echo href_link(FILENAME_BATCH); ?>" method="post">
	    						<table class="form-list">
	    						<tbody>
	    							<tr><td colspan="2"><input type="hidden" name="action" value="product" /></td></tr>
	    							<?php foreach ($productFilter as $key => $val) { ?>
	    							<tr>
										<td class="label"><label for="<?php echo $val ?>"><?php echo $key ?></label></td>
										<td class="value"><input type="text" class="input-text" id="<?php echo $val ?>" value="<?php echo (isset($_POST[$val])?$_POST[$val]:'') ?>" name="<?php echo $val ?>" /></td>
									</tr>
									<?php } ?> 
									<tr>
										<td class="label"><label for="product-category">产品分类</label></td>
										<td class="value">
											<select class="multiselect" multiple="multiple" name="category[]" id="product-category">
												<?php foreach ($availabCategoryList as $key => $val) { ?>
												<option<?php if (isset($productCategory)&&in_array($key, $productCategory)) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr><td class="label"></td><td class="value"><button type="submit" class="button"><span><span>保存</span></span></button></td></tr>
	    						</tbody>
	    						</table>
	    						</form>
	    						<table class="data-table">
	    						<tbody>
	    							<tr>
	    								<td class="label" width="100">产品名称</td>
	    								<td class="value">{product_name}</td>
	    								<?php $i = 1; ?>
	    								<?php foreach ($filterList as $key => $val) { ?>
	    								<?php if ($i % 4 == 0) { ?>
	    								</tr><tr>
	    								<?php } ?>
	    								<td class="label" width="100"><?php echo $key ?></td>
	    								<td class="value"><?php echo '{' . $val . '}' ?></td>
	    								<?php $i++; } ?>
	    							</tr>
	    							<tr><td class="label red a-center" colspan="10">如果为空不填则不修改(分类不填,视为全部类目修改)</td></tr>
	    						</tbody>
	    						</table>
	    					</div>
	    					<div id="tab-price">
	    						<form action="<?php echo href_link(FILENAME_BATCH); ?>" method="post">
	    						<table class="form-list">
	    						<tbody>
	    							<tr><td colspan="2"><input type="hidden" name="action" value="price" /></td></tr>
	    							<tr>
										<td class="label"><label for="product-price">原价</label></td>
										<td class="value" style="width:150px !important;">
											<select name="product_price">
												<?php foreach ($priceFilter as $key => $val) { ?>
												<option<?php if (isset($_POST['product_price'])&&$_POST['product_price']==$val) { ?> selected="selected"<?php } ?> value="<?php echo $val ?>"><?php echo $key ?></option>
												<?php } ?>
											</select>
										</td>
										<td class="value"><input type="text" class="input-text" id="product-price" value="<?php echo (isset($_POST['price'])?$_POST['price']:'') ?>" name="price" /></td>
									</tr>
									<tr>
										<td class="label"><label for="product-specials_price">特价</label></td>
										<td class="value" style="width:150px !important;">
											<select name="product_specials">
												<?php foreach ($priceFilter as $key => $val) { ?>
												<option<?php if (isset($_POST['product_specials'])&&$_POST['product_specials']==$val) { ?> selected="selected"<?php } ?> value="<?php echo $val ?>"><?php echo $key ?></option>
												<?php } ?>
											</select>
										</td>
										<td class="value"><input type="text" class="input-text" id="product-specials_price" value="<?php echo (isset($_POST['specials_price'])?$_POST['specials_price']:'') ?>" name="specials_price" /></td>
									</tr>
									<tr>
										<td class="label"><label for="product-category">产品分类</label></td>
										<td class="value" colspan="2">
											<select class="multiselect" multiple="multiple" name="category[]" id="product-category">
												<?php foreach ($availabCategoryList as $key => $val) { ?>
												<option<?php if (isset($priceCategory)&&in_array($key, $priceCategory)) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr><td class="label"></td><td class="value"><button type="submit" class="button"><span><span>保存</span></span></button></td></tr>
	    						</tbody>
	    						</table>
	    						</form>
	    						<table class="data-table">
	    						<tbody>
	    							<tr><td class="label red a-center" colspan="10">如果为空不填则不修改</td></tr>
	    						</tbody>
	    						</table>
	    					</div>
	    					<div id="tab-description">
	    						<form action="<?php echo href_link(FILENAME_BATCH); ?>" method="post">
	    						<table class="form-list">
	    						<tbody>
	    							<tr><td><input type="hidden" name="action" value="description" /></td></tr>
	    							<tr>
										<td class="label"><label for="description_width">宽度<span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text" id="description_width" value="<?php echo (isset($_POST['description_width'])?$_POST['description_width']:'') ?>" name="description_width" /></td>
									</tr>
									<tr>
										<td class="label"><label for="description-category">产品分类</label></td>
										<td class="value">
											<select class="multiselect" multiple="multiple" name="category[]" id="description-category">
												<?php foreach ($availabCategoryList as $key => $val) { ?>
												<option<?php if (isset($descriptionCategory)&&in_array($key, $descriptionCategory)) { ?> selected="selected"<?php } ?> value="<?php echo $key; ?>"><?php echo $val['name']; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr><td class="label"></td><td class="value"><button type="submit" class="button"><span><span>保存</span></span></button></td></tr>
	    						</tbody>
	    						</table>
	    						</form>
	    					</div>
							<div id="tab-sql">
								<form action="<?php echo href_link(FILENAME_BATCH); ?>" method="post">
									<table class="form-list">
										<tbody>
										<tr><td><input type="hidden" name="action" value="sql" /></td></tr>
										<tr>
											<td class="label"><label>数据库命令<span class="required">*</span></label></td>
											<td class="value"><textarea name="sql" cols="60" rows="5"></textarea></td>
										</tr>
										<tr><td class="label"></td><td class="value"><button type="submit" class="button"><span><span>执行</span></span></button></td></tr>
										</tbody>
									</table>
								</form>
								<table class="data-table">
									<tbody>
									<tr>
										<td class="label" width="100">产品名称</td>
										<td class="value">name</td>
										<td class="label" width="100">产品名称</td>
										<td class="value">sku</td>
										<td class="label" width="100">产品原价</td>
										<td class="value">price</td>
										<td class="label" width="100">产品特价</td>
										<td class="value">specials_price</td>
									</tr>
									<tr>
										<td class="label" width="100">产品销量</td>
										<td class="value">ordered</td>
										<td class="label" width="100">产品是否上架（是=>1 否=>0）</td>
										<td class="value">in_stock</td>
										<td class="label" width="100"></td>
										<td class="value"></td>
										<td class="label" width="100"></td>
										<td class="value"></td>
									</tr>
									<tr>
										<td class="label" width="100" rowspan="3">sql例子</td>
										<td class="value" colspan="10">
											<p>改热卖： UPDATE `product` SET `ordered` = 99999 WHERE `sku` IN ('SKU1','SKU2');</p>
											<p>改特价： UPDATE `product` SET `specials_price` = `specials_price` + 1 WHERE `name` LIKE '%12 OZ%';</p>
											<p>改原价： UPDATE `product` SET `price` = `price` + 1 WHERE `name` LIKE '%12 OZ%';</p>
										</td>
									</tr>
									</tbody>
								</table>
							</div>
	    				</div>
	    			</div>
					<script type="text/javascript"><!--
					$('#vtabs a').tabs();
					<?php if (isset($_POST['action'])) { ?>
					$('.<?php echo $_POST['action']?>').click();
					<?php } ?>
					//--></script>
	    		</div>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>