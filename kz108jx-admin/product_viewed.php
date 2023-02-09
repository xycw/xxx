<?php require('includes/application_top.php'); ?>
<?php
$sql = "SELECT COUNT(*) AS total FROM " . TABLE_PRODUCT . " WHERE viewed > 0";
$result = $db->Execute($sql);
$pagerConfig['total'] = $result->fields['total'];
require(DIR_FS_ADMIN_CLASSES . 'pager.php');
$pager = new pager($pagerConfig);
$sql = "SELECT product_id, sku,
			   name, viewed
		FROM   " . TABLE_PRODUCT . "
		WHERE  viewed > 0
		ORDER BY viewed DESC";
$result = $db->Execute($sql, $pager->getLimitSql());
$productViewedList = array();
while (!$result->EOF) {
	$productViewedList[] = array(
		'product_id' => $result->fields['product_id'],
		'sku'        => $result->fields['sku'],
		'name'       => $result->fields['name'],
		'viewed'     => $result->fields['viewed']
	);
	$result->MoveNext();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>产品浏览</title>
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
    		<?php if ($message_stack->size('product_viewed') > 0) echo $message_stack->output('product_viewed'); ?>
    		<div class="page-title title-buttons">
    			<h1>产品浏览列表</h1>
    		</div>
    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
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
    				<th>浏览量</th>
    			</tr>
    		</thead>
    		<?php if (count($productViewedList)>0) { ?>
    		<tbody>
    			<?php foreach ($productViewedList as $val) { ?>
    			<tr>
    				<td><?php echo $val['product_id']; ?></td>
    				<td><?php echo $val['sku']; ?></td>
    				<td><?php echo $val['name']; ?></td>
    				<td><?php echo $val['viewed']; ?></td>
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
    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>