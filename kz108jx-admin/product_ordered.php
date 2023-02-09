<?php require('includes/application_top.php'); ?>
<?php
$productFilterFields = array();
$productFields = array_keys($db->metaColumns('product'));
foreach ($productFields as $field) {
	if (strstr($field, '_filter')) {
		$productFilterFields[] = $field;
	}
}
if (isset($_GET['filter'])
	&& in_array($_GET['filter'], $productFilterFields)) {
	$sql = "SELECT COUNT({$_GET['filter']}) AS total
			FROM   " . TABLE_PRODUCT . "
			WHERE  ordered > 0
			AND   {$_GET['filter']} <> ''
			AND   {$_GET['filter']} IS NOT NULL
			GROUP BY {$_GET['filter']}";
	$result = $db->Execute($sql);
	$pagerConfig['total'] = $result->fields['total'];
	require(DIR_FS_ADMIN_CLASSES . 'pager.php');
	$pager = new pager($pagerConfig);
	$sql = "SELECT {$_GET['filter']} filter, SUM(ordered) ordered
			FROM   " . TABLE_PRODUCT . "
			WHERE  ordered > 0
			AND   {$_GET['filter']} <> ''
			AND   {$_GET['filter']} IS NOT NULL
			GROUP BY {$_GET['filter']}
			ORDER BY ordered DESC";
	$result = $db->Execute($sql, $pager->getLimitSql());
	$productOrderedList = array();
	while (!$result->EOF) {
		$productOrderedList[] = array(
			'filter' => $result->fields['filter'],
			'ordered'    => $result->fields['ordered']
		);
		$result->MoveNext();
	}
} else {
	$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT . " WHERE ordered > 0";
	$result = $db->Execute($sql);
	$pagerConfig['total'] = $result->fields['total'];
	require(DIR_FS_ADMIN_CLASSES . 'pager.php');
	$pager = new pager($pagerConfig);
	$sql = "SELECT product_id, sku,
				   name, ordered
			FROM   " . TABLE_PRODUCT . "
			WHERE  ordered > 0
			ORDER BY ordered DESC";
	$result = $db->Execute($sql, $pager->getLimitSql());
	$productOrderedList = array();
	while (!$result->EOF) {
		$productOrderedList[] = array(
			'product_id' => $result->fields['product_id'],
			'sku'        => $result->fields['sku'],
			'name'       => $result->fields['name'],
			'ordered'    => $result->fields['ordered']
		);
		$result->MoveNext();
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>产品销售</title>
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
    		<?php if ($message_stack->size('product_ordered') > 0) echo $message_stack->output('product_ordered'); ?>
    		<div class="page-title title-buttons">
    			<h1>产品销售列表</h1>
    			类型:
    			<select onchange="setLocation(this.value);">
					<option value="<?php echo href_link(FILENAME_PRODUCT_ORDERED); ?>">默认</option>
					<?php foreach ($productFilterFields as $val) { ?>
					<option<?php if (isset($_GET['filter']) && $_GET['filter']==$val) { ?> selected="selected"<?php } ?> value="<?php echo href_link(FILENAME_PRODUCT_ORDERED, 'filter='.$val); ?>"><?php echo $val; ?></option>
					<?php } ?>
				</select>
    		</div>
    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
    		<?php if (isset($_GET['filter'])
				&& in_array($_GET['filter'], $productFilterFields)) { ?>
				<table class="data-table">
	    		<colgroup>
	    			<col />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th>过滤</th>
	    				<th>销量</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($productOrderedList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($productOrderedList as $val) { ?>
	    			<tr>
	    				<td><?php echo $val['filter']; ?></td>
	    				<td><?php echo $val['ordered']; ?></td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="2">没有结果！</td>
					</tr>
				</tbody>
	    		<?php } ?>
	    		</table>
			<?php } else { ?>
    		<table class="data-table">
    		<colgroup>
    			<col width="60" />
    			<col />
    			<col />
    			<col width="60" />
    		</colgroup>
    		<thead>
    			<tr>
    				<th>ID#</th>
    				<th>产品型号</th>
    				<th>产品名称</th>
    				<th>销量</th>
    			</tr>
    		</thead>
    		<?php if (count($productOrderedList)>0) { ?>
    		<tbody>
    			<?php foreach ($productOrderedList as $val) { ?>
    			<tr>
    				<td><?php echo $val['product_id']; ?></td>
    				<td><?php echo $val['sku']; ?></td>
    				<td><?php echo $val['name']; ?></td>
    				<td><?php echo $val['ordered']; ?></td>
    			</tr>
    			<?php } ?>
    		</tbody>
    		<?php } else { ?>
    		<tbody>
				<tr>
					<td class="a-center" colspan="4">没有结果！</td>
				</tr>
			</tbody>
    		<?php } ?>
    		</table>
    		<?php } ?>
    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>