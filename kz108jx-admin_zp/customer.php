<?php require('includes/application_top.php'); ?>
<?php
$customer_id = isset($_GET['customer_id'])?$_GET['customer_id']:0;
$action = isset($_GET['action'])?$_GET['action']:'';
$availabNewsletter = array('0'=>'禁用', '1'=>'启用');
$availabStatus = array('0'=>'禁用', '1'=>'启用');
switch ($action) {
	case 'save':
		$error = false;
		$updatePassword = false;
		$customer = db_prepare_input($_POST['customer']);
		$addressList = isset($_POST['address'])?db_prepare_input($_POST['address']):array();
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add('customer', '客户设置保存时出现安全错误。');
		}
		if (strlen($customer['firstname']) < 1) {
			$error = true;
			$message_stack->add('customer', '名字不能为空。');
		}
		if (strlen($customer['lastname']) < 1) {
			$error = true;
			$message_stack->add('customer', '姓氏不能为空。');
		}
		if (strlen($customer['email_address']) < 1) {
			$error = true;
			$message_stack->add('customer', '邮箱不能为空。');
		} elseif (!validate_email($customer['email_address']) || disable_email($customer['email_address'])) {
			$error = true;
			$message_stack->add('customer', '邮箱格式错误。');
		} else {
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_CUSTOMER . " WHERE email_address = :email_address AND customer_id <> :customerID";
			$sql = $db->bindVars($sql, ':email_address', $customer['email_address'], 'string');
			$sql = $db->bindVars($sql, ':customerID', isset($customer['customer_id'])?$customer['customer_id']:0, 'integer');
		    $check_email_address = $db->Execute($sql);
			if ($check_email_address->fields['total'] > 0) {
				$error = true;
				$message_stack->add('customer', '邮箱存在相同。');
			}
		}
		if (strlen($customer['password']) < 1) {
			$updatePassword = false;
		} elseif ($customer['password'] < 6) {
			$error = true;
			$message_stack->add('customer', '密码长度必须大于6个字符。');
		} elseif ($customer['password'] != $customer['confirm']) {
			$error = true;
			$message_stack->add('customer', '密码和确认密码不一致！');
		} else {
			$updatePassword = true;
		}
		if (!array_key_exists($customer['newsletter'], $availabNewsletter)) $customer['status'] = 0;
		if (!array_key_exists($customer['status'], $availabStatus)) $customer['status'] = 0;
		if ($error==true) {
			//nothing
		} else {
			$sql_data_array = array(
				array('fieldName'=>'firstname', 'value'=>$customer['firstname'], 'type'=>'string'),
				array('fieldName'=>'lastname', 'value'=>$customer['lastname'], 'type'=>'string'),
				array('fieldName'=>'email_address', 'value'=>$customer['email_address'], 'type'=>'string'),
				array('fieldName'=>'newsletter', 'value'=>$customer['newsletter'], 'type'=>'integer'),
				array('fieldName'=>'billing_address_id', 'value'=>$customer['billing_address_id'], 'type'=>'integer'),
				array('fieldName'=>'shipping_address_id', 'value'=>$customer['shipping_address_id'], 'type'=>'integer'),
				array('fieldName'=>'status', 'value'=>$customer['status'], 'type'=>'integer')
			);
			//password
			if ($updatePassword) {
				$sql_data_array[] = array('fieldName'=>'password', 'value'=>encrypt_password($customer['password']), 'type'=>'string');
			}
			if ($customer['customer_id'] > 0) {
				$sql_data_array[] = array('fieldName'=>'last_modified', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_CUSTOMER, $sql_data_array, 'UPDATE', 'customer_id = ' . $customer['customer_id']);
			} else {
				$sql_data_array[] = array('fieldName'=>'date_added', 'value'=>'NOW()', 'type'=>'noquotestring');
				$db->perform(TABLE_CUSTOMER, $sql_data_array);
				$customer['customer_id'] = $db->Insert_ID();
			}
			if ($customer['customer_id'] > 0) {
				$notDeleltIds = array();
				foreach ($addressList as $address) {
					$error = false;
					if (strlen($address['firstname']) < 1) {
						$error = true;
					}
					if (strlen($address['lastname']) < 1) {
						$error = true;
					}
					if (strlen($address['street_address']) < 1) {
						$error = true;
					}
					if (strlen($address['city']) < 1) {
						$error = true;
					}
					if (has_region_country($address['country_id'])) {
						if ($region_name = get_region_name($address['region_id'], $address['country_id'])) {
							$address['region'] = $region_name;
						} else {
							$error = true;
						}
					}
					if (strlen($address['postcode']) < 1) {
						$error = true;
					}
					if (!not_null($address['country_id'])
						|| !($address['country'] = get_country_name($address['country_id']))) {
						$error = true;
					}
					if (strlen($address['telephone']) < 1) {
						$error = true;
					}
					if ($error==true) {
					//nothing
					} else {
						$sql_data_array = array(
							array('fieldName'=>'customer_id', 'value'=>$customer['customer_id'], 'type'=>'integer'),
							array('fieldName'=>'firstname', 'value'=>$address['firstname'], 'type'=>'string'),
							array('fieldName'=>'lastname', 'value'=>$address['lastname'], 'type'=>'string'),
							array('fieldName'=>'company', 'value'=>$address['company'], 'type'=>'string'),
							array('fieldName'=>'street_address', 'value'=>$address['street_address'], 'type'=>'string'),
							array('fieldName'=>'suburb', 'value'=>$address['suburb'], 'type'=>'string'),
							array('fieldName'=>'city', 'value'=>$address['city'], 'type'=>'string'),
							array('fieldName'=>'region_id', 'value'=>$address['region_id'], 'type'=>'integer'),
							array('fieldName'=>'region', 'value'=>$address['region'], 'type'=>'string'),
							array('fieldName'=>'postcode', 'value'=>$address['postcode'], 'type'=>'string'),
							array('fieldName'=>'country_id', 'value'=>$address['country_id'], 'type'=>'integer'),
							array('fieldName'=>'country', 'value'=>$address['country'], 'type'=>'string'),
							array('fieldName'=>'telephone', 'value'=>$address['telephone'], 'type'=>'string'),
							array('fieldName'=>'fax', 'value'=>$address['fax'], 'type'=>'string')
						);
						if ($address['address_id'] > 0) {
							$db->perform(TABLE_ADDRESS, $sql_data_array, 'UPDATE', 'address_id = ' . (int)$address['address_id']);
						} else {
							$db->perform(TABLE_ADDRESS, $sql_data_array);
							$address['address_id'] = $db->Insert_ID();
						}
						$notDeleltIds[] = $address['address_id'];
					}
				}
				if (not_null($notDeleltIds)) {
					$db->Execute("DELETE FROM " . TABLE_ADDRESS . " WHERE customer_id = " . (int)$customer['customer_id'] . " AND address_id NOT IN (" . implode(', ', $notDeleltIds) . ")");
				}
			}
			$message_stack->add_session('customer', '客户设置已保存。', 'success');
			redirect(href_link(FILENAME_CUSTOMER, 'customer_id=' . $customer['customer_id']));
		}
	break;
	case 'delete':
		$error = false;
		$selected = $_POST['selected'];
		$securityToken = isset($_POST['securityToken'])?db_prepare_input($_POST['securityToken']):'';
		if ($securityToken != $_SESSION['securityToken']) {
			$error = true;
			$message_stack->add_session('customer', '删除客户时出现安全错误。');
		}
		if ($error==true) {
			//nothing
		} else {
			foreach ($selected as $val) {
				$db->Execute("DELETE FROM " . TABLE_CUSTOMER . " WHERE customer_id = " . (int)$val);
				$db->Execute("DELETE FROM " . TABLE_ADDRESS . " WHERE customer_id = " . (int)$val);
			}
			$message_stack->add_session('customer', '客户已删除。', 'success');
		}
		redirect(href_link(FILENAME_CUSTOMER));
	break;
	case 'set_status':
		$db->Execute("UPDATE " . TABLE_CUSTOMER . " SET status = IF(status = 1, 0, 1) WHERE customer_id = " . (int)$customer_id);
		redirect(href_link(FILENAME_CUSTOMER));
	break;
	default:
		if ($customer_id > 0) {
			$sql = "SELECT customer_id, firstname, lastname, email_address,
						   newsletter, billing_address_id, shipping_address_id,
						   status
					FROM   " . TABLE_CUSTOMER . "
					WHERE  customer_id = :customerID";
			$sql = $db->bindVars($sql, ':customerID', $customer_id, 'integer');
			$result = $db->Execute($sql);
			if ($result->RecordCount() > 0) {
				$customer = array(
					'customer_id'   => $result->fields['customer_id'],
					'firstname'     => $result->fields['firstname'],
					'lastname'      => $result->fields['lastname'],
					'email_address' => $result->fields['email_address'],
					'newsletter'    => $result->fields['newsletter'],
					'billing_address_id'  => $result->fields['billing_address_id'],
					'shipping_address_id' => $result->fields['shipping_address_id'],
					'status'        => $result->fields['status']
				);
				$sql = "SELECT address_id, firstname, lastname, company,
							   street_address, suburb, city, region_id, region,
							   postcode, country_id, country, telephone, fax
						FROM   " . TABLE_ADDRESS . "
						WHERE  customer_id = :customerID
						ORDER BY address_id";
				$sql = $db->bindVars($sql, ':customerID', $customer_id, 'integer');
				$addressResult = $db->Execute($sql);
				$addressList = array();
				while (!$addressResult->EOF) {
					$addressList[] = array(
						'address_id' => $addressResult->fields['address_id'],
						'firstname'  => $addressResult->fields['firstname'],
						'lastname'   => $addressResult->fields['lastname'],
						'company'    => $addressResult->fields['company'],
						'street_address' => $addressResult->fields['street_address'],
						'suburb'     => $addressResult->fields['suburb'],
						'city'       => $addressResult->fields['city'],
						'region_id'  => $addressResult->fields['region_id'],
						'region'     => $addressResult->fields['region'],
						'postcode'   => $addressResult->fields['postcode'],
						'country_id' => $addressResult->fields['country_id'],
						'country'    => $addressResult->fields['country'],
						'telephone'  => $addressResult->fields['telephone'],
						'fax'        => $addressResult->fields['fax']
					);
					$addressResult->MoveNext();
				}
			}
		} else {
			$sqlFilter = '';
			if (isset($_GET['newsletter'])) {
				$sqlFilter .= " AND newsletter = :newsletter";
				$sqlFilter = $db->bindVars($sqlFilter, ':newsletter', trim($_GET['newsletter']), 'integer');
			}
			$sql = "SELECT COUNT(*) AS total FROM " . TABLE_CUSTOMER . "
					WHERE 1=1" . $sqlFilter;
			$result = $db->Execute($sql);
			$pagerConfig['total'] = $result->fields['total'];
			require(DIR_FS_ADMIN_CLASSES . 'pager.php');
			$pager = new pager($pagerConfig);
			$sql = "SELECT customer_id, firstname, lastname,
						   email_address, date_added, newsletter, status
					FROM   " . TABLE_CUSTOMER . "
					WHERE 1=1" . $sqlFilter . "
					ORDER BY customer_id DESC";
			$result = $db->Execute($sql, $pager->getLimitSql());
			$customerList = array();
			while (!$result->EOF) {
				$customerList[] = array(
					'customer_id' => $result->fields['customer_id'],
					'firstname'   => $result->fields['firstname'],
					'lastname'    => $result->fields['lastname'],
					'email_address' => $result->fields['email_address'],
					'date_added'  => $result->fields['date_added'],
					'newsletter'  => $result->fields['newsletter'],
					'status'      => $result->fields['status']
				);
				$result->MoveNext();
			}
		}
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>客户管理</title>
<meta name="robot" content="noindex, nofollow" />
<base href="<?php echo (($request_type=='SSL')?HTTPS_SERVER:HTTP_SERVER) . DIR_WS_ADMIN; ?>" />
<link href="css/styles.css" type="text/css" rel="stylesheet" />
<link href="css/styles-ie.css" type="text/css" rel="stylesheet" />
<script src="js/jquery/jquery.js" type="text/javascript"></script>
<script src="js/jquery/base.js" type="text/javascript"></script>
<script src="js/jquery/tabs.js" type="text/javascript"></script>
<?php require(DIR_FS_ADMIN_INCLUDES . 'jscript_update_region.php'); ?>
</head>
<body>
<div class="wrapper">
	<?php require(DIR_FS_ADMIN_INCLUDES . 'noscript.php'); ?>
	<div class="page">
    	<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
    	<div class="main-container">
    		<div class="main">
    		<?php if ($message_stack->size('customer') > 0) echo $message_stack->output('customer'); ?>
    		<?php if ($action == 'save' || $customer_id > 0) { ?>
    			<form action="<?php echo href_link(FILENAME_CUSTOMER, 'action=save'); ?>" method="post">
    			<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    				<input type="hidden" value="<?php echo isset($customer['customer_id'])?$customer['customer_id']:''; ?>" name="customer[customer_id]" />
    			</div>
    			<div class="page-title title-buttons">
	    			<h1>客户设置</h1>
	    			<button type="submit" class="button"><span><span>保存</span></span></button>
    				<button type="button" class="button btn-cancel" onclick="setLocation('<?php echo href_link(FILENAME_CUSTOMER); ?>');"><span><span>取消</span></span></button>
	    		</div>
	    		<div class="columns">
	    			<div id="vtabs" class="vtabs">
	    				<a href="#tab-customer">客户信息</a>
	    				<?php if (isset($addressList)&&count($addressList)>0) { ?>
			    		<?php $i=1; ?>
			    		<?php foreach ($addressList as $address) { ?>
			    		<a href="#tab-address<?php echo $i; ?>" id="address<?php echo $i; ?>">地址 <?php echo $i; ?><?php if ($customer['billing_address_id']!=$address['address_id']&&$customer['shipping_address_id']!=$address['address_id']) { ?>&nbsp;<img onclick="$('#vtabs a:first').trigger('click'); $('#address<?php echo $i; ?>').remove(); $('#tab-address<?php echo $i; ?>').remove(); return false;" title="移除地址 <?php echo $i; ?>" src="images/delete.png" /><?php } ?></a>
			    		<?php $i++; ?>
			    		<?php } ?>
				    	<?php } ?>
	    				<span id="address-add">添加地址&nbsp;<img onclick="addAddress();" title="添加地址" src="images/add.png" /></span>
	    			</div>
	    			<div class="main-col">
	    				<div id="tab-general" class="main-col-inner">
		    				<div id="tab-customer">
					    		<table class="form-list">
								<tbody>
									<tr>
										<td class="label"><label for="customer-firstname">名字  <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($customer['firstname'])?$customer['firstname']:''; ?>" name="customer[firstname]" id="customer-firstname" /></td>
									</tr>
									<tr>
										<td class="label"><label for="customer-lastname">姓氏  <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($customer['lastname'])?$customer['lastname']:''; ?>" name="customer[lastname]" id="customer-lastname" /></td>
									</tr>
									<tr>
										<td class="label"><label for="customer-email_address">邮箱 <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($customer['email_address'])?$customer['email_address']:''; ?>" name="customer[email_address]" id="customer-email_address" /></td>
									</tr>
									<tr>
										<td class="label"><label for="customer-password">密码</label></td>
										<td class="value"><input type="password" class="input-text" value="" name="customer[password]" id="customer-password" /></td>
									</tr>
									<tr>
										<td class="label"><label for="customer-confirm">确认密码</label></td>
										<td class="value"><input type="password" class="input-text" value="" name="customer[confirm]" id="customer-confirm" /></td>
									</tr>
									<tr>
										<td class="label"><label for="customer-newsletter">电子商情</label></td>
										<td class="value">
											<select name="customer[newsletter]" id="customer-newsletter">
												<?php foreach ($availabNewsletter as $_key=>$_val) { ?>
												<option<?php if (isset($customer['newsletter'])&&$customer['newsletter']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="label"><label for="customer-status">状态</label></td>
										<td class="value">
											<select name="customer[status]" id="customer-status">
												<?php foreach ($availabStatus as $_key=>$_val) { ?>
												<option<?php if (isset($customer['status'])&&$customer['status']==$_key) { ?> selected="selected"<?php } ?> value="<?php echo $_key; ?>"><?php echo $_val; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
								</tbody>
				    			</table>
				    		</div>
				    		<?php if (isset($addressList)&&count($addressList)>0) { ?>
				    		<?php $i=1; ?>
				    		<?php foreach ($addressList as $address) { ?>
				    		<div id="tab-address<?php echo $i; ?>">
				    			<div class="no-display">
				    				<input type="hidden" value="<?php echo isset($address['address_id'])?$address['address_id']:''; ?>" name="address[<?php echo $i; ?>][address_id]" />
				    			</div>
				    			<table class="form-list">
				    			<tbody>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-firstname">名字  <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($address['firstname'])?$address['firstname']:''; ?>" name="address[<?php echo $i; ?>][firstname]" id="address<?php echo $i; ?>-firstname" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-lastname">姓氏  <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($address['lastname'])?$address['lastname']:''; ?>" name="address[<?php echo $i; ?>][lastname]" id="address<?php echo $i; ?>-lastname" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-company">公司</label></td>
										<td class="value"><input type="text" class="input-text" value="<?php echo isset($address['company'])?$address['company']:''; ?>" name="address[<?php echo $i; ?>][company]" id="address<?php echo $i; ?>-company" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-street_address">地址 1 <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($address['street_address'])?$address['street_address']:''; ?>" name="address[<?php echo $i; ?>][street_address]" id="address<?php echo $i; ?>-street_address" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-suburb">地址 2</label></td>
										<td class="value"><input type="text" class="input-text" value="<?php echo isset($address['suburb'])?$address['suburb']:''; ?>" name="address[<?php echo $i; ?>][suburb]" id="address<?php echo $i; ?>-suburb" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-city">城市 <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($address['city'])?$address['city']:''; ?>" name="address[<?php echo $i; ?>][city]" id="address<?php echo $i; ?>-city" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-region">省份/地区</label></td>
										<td class="value">
											<select class="required-entry" name="address[<?php echo $i; ?>][region_id]" id="address<?php echo $i; ?>-region_id">
												<option value="">请选择省份/地区</option>
											</select>
											<input type="text" class="input-text" value="<?php echo isset($address['region'])?$address['region']:''; ?>" name="address[<?php echo $i; ?>][region]" id="address<?php echo $i; ?>-region" />
										</td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-postcode">邮编 <span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($address['postcode'])?$address['postcode']:''; ?>" name="address[<?php echo $i; ?>][postcode]" id="address<?php echo $i; ?>-postcode" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-country_id">国家 <span class="required">*</span></label></td>
										<td class="value">
											<?php $address['country_id'] = isset($address['country_id'])?$address['country_id']:STORE_COUNTRY; ?>
											<?php $_availabCountry = get_countries(); ?>
											<select class="required-entry" onchange="updateRegion('address<?php echo $i; ?>');" name="address[<?php echo $i; ?>][country_id]" id="address<?php echo $i; ?>-country_id">
												<option value="">请选择国家</option>
					    						<?php foreach ($_availabCountry as $key => $val) { ?>
					    						<option value="<?php echo $key; ?>"<?php if ($key==$address['country_id']) { ?> selected="selected"<?php } ?>><?php echo $val; ?></option>
					    						<?php } ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-telephone">电话<span class="required">*</span></label></td>
										<td class="value"><input type="text" class="input-text required-entry" value="<?php echo isset($address['telephone'])?$address['telephone']:''; ?>" name="address[<?php echo $i; ?>][telephone]" id="address<?php echo $i; ?>-telephone" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-fax">传真</label></td>
										<td class="value"><input type="text" class="input-text" value="<?php echo isset($address['fax'])?$address['fax']:''; ?>" name="address[<?php echo $i; ?>][fax]" id="address<?php echo $i; ?>-fax" /></td>
									</tr>
									<?php if (isset($address['address_id']) && $address['address_id']>0) { ?>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-default_billing">默认的帐单地址</label></td>
										<td class="value"><input type="radio" class="radio"<?php if ($address['address_id']==$customer['billing_address_id']) { ?> checked="checked"<?php } ?> title="默认的帐单地址" value="<?php echo $address['address_id']; ?>" name="customer[billing_address_id]" id="address<?php echo $i; ?>-default_billing" /></td>
									</tr>
									<tr>
										<td class="label"><label for="address<?php echo $i; ?>-default_shipping">默认的运送地址</label></td>
										<td class="value"><input type="radio" class="radio"<?php if ($address['address_id']==$customer['shipping_address_id']) { ?> checked="checked"<?php } ?> title="默认的运送地址" value="<?php echo $address['address_id']; ?>" name="customer[shipping_address_id]" id="address<?php echo $i; ?>-default_shipping" /></td>
									</tr>
									<?php } ?>
				    			</tbody>
				    			</table>
<script type="text/javascript"><!--
updateRegion('address<?php echo $i; ?>', '<?php echo isset($address['region_id'])?$address['region_id']:''; ?>');
//-->;</script>
				    		</div>
				    		<?php $i++; ?>
				    		<?php }?>
				    		<?php }?>
			    		</div>
			    	</div>
    			</div>
    			</form>
<script type="text/javascript"><!--
var address_row = <?php echo $i; ?>;
function addAddress() {
html = '<div id="tab-address' + address_row + '" style="display: none;">';
html += '<div class="no-display">';
html += '<input type="hidden" value="" name="address[' + address_row + '][address_id]" />';
html += '</div>';
html += '<table class="form-list">';
html += '<tbody>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-firstname">名字  <span class="required">*</span></label></td>';
html += '<td class="value"><input type="text" class="input-text required-entry" value="" name="address[' + address_row + '][firstname]" id="address' + address_row + '-firstname" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-lastname">姓氏  <span class="required">*</span></label></td>';
html += '<td class="value"><input type="text" class="input-text required-entry" value="" name="address[' + address_row + '][lastname]" id="address' + address_row + '-lastname" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-company">公司</label></td>';
html += '<td class="value"><input type="text" class="input-text" value="" name="address[' + address_row + '][company]" id="address' + address_row + '-company" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-street_address">地址 1 <span class="required">*</span></label></td>';
html += '<td class="value"><input type="text" class="input-text required-entry" value="" name="address[' + address_row + '][street_address]" id="address' + address_row + '-street_address" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-suburb">地址 2</label></td>';
html += '<td class="value"><input type="text" class="input-text" value="" name="address[' + address_row + '][suburb]" id="address' + address_row + '-suburb" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-city">城市 <span class="required">*</span></label></td>';
html += '<td class="value"><input type="text" class="input-text required-entry" value="" name="address[' + address_row + '][city]" id="address' + address_row + '-city" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-region">省份/地区</label></td>';
html += '<td class="value">';
html += '<select class="required-entry" name="address[' + address_row + '][region_id]" id="address' + address_row + '-region_id">';
html += '<option value="">请选择省份/地区</option>';
html += '</select>';
html += '<input type="text" class="input-text" value="" name="address[' + address_row + '][region]" id="address' + address_row + '-region" />';
html += '</td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-postcode">邮编 <span class="required">*</span></label></td>';
html += '<td class="value"><input type="text" class="input-text required-entry" value="" name="address[' + address_row + '][postcode]" id="address' + address_row + '-postcode" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-country_id">国家 <span class="required">*</span></label></td>';
html += '<td class="value">';
html += '<select class="required-entry" onchange="updateRegion(\'address' + address_row + '\');" name="address[' + address_row + '][country_id]" id="address' + address_row + '-country_id">';
html += '<option value="">请选择国家</option>';
<?php $_availabCountry = get_countries(); ?>
<?php foreach ($_availabCountry as $key => $val) { ?>
html += '<option value="<?php echo $key; ?>"<?php if ($key==STORE_COUNTRY) { ?> selected="selected"<?php } ?>><?php echo addslashes($val); ?></option>';
<?php } ?>
html += '</select>';
html += '</td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-telephone">电话<span class="required">*</span></label></td>';
html += '<td class="value"><input type="text" class="input-text required-entry" value="" name="address[' + address_row + '][telephone]" id="address' + address_row + '-telephone" /></td>';
html += '</tr>';
html += '<tr>';
html += '<td class="label"><label for="address' + address_row + '-fax">传真</label></td>';
html += '<td class="value"><input type="text" class="input-text" value="" name="address[' + address_row + '][fax]" id="address' + address_row + '-fax" /></td>';
html += '</tr>';
html += '<tr>';
html += '</tbody>';
html += '</table>';
html += '</div>';
$('#tab-general').append(html);
updateRegion('address' + address_row + '', '');
$('#address-add').before('<a href="#tab-address' + address_row + '" id="address' + address_row + '">地址 ' + address_row + '&nbsp;<img onclick="$(\'#vtabs a:first\').trigger(\'click\'); $(\'#address' + address_row + '\').remove(); $(\'#tab-address' + address_row + '\').remove(); return false;" title="移除地址 ' + address_row + '" src="images/delete.png" /></a>');
$('#vtabs a').tabs();
$('#address' + address_row).trigger('click');
address_row++;
}
//--></script>
<script type="text/javascript"><!--
$('#vtabs a').tabs();
//--></script>
    		<?php } else { ?>
				<form action="<?php echo href_link(FILENAME_CUSTOMER, 'action=delete'); ?>" method="post">
	    		<div class="no-display">
    				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
    			</div>
	    		<div class="page-title title-buttons">
	    			<h1>客户</h1>
	    			电子商情:
    				<select onchange="setLocation(this.value);">
					<option value="<?php echo href_link(FILENAME_CUSTOMER); ?>">全部</option>
					<?php foreach ($availabNewsletter as $key => $val) { ?>
					<option<?php if (isset($_GET['newsletter']) && $_GET['newsletter']==$key) { ?> selected="selected"<?php } ?> value="<?php echo href_link(FILENAME_CUSTOMER, 'newsletter='.$key); ?>"><?php echo $val; ?></option>
					<?php } ?>
				</select>
	    			<button type="submit" class="button" onclick="return confirm('删除或卸载后您将不能恢复，请确定要这么做吗？');"><span><span>删除</span></span></button>
	    		</div>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		<table class="data-table">
	    		<colgroup>
	    			<col width="10" />
	    			<col />
	    			<col />
	    			<col width="60" />
	    			<col width="140" />
	    			<col width="60" />
	    		</colgroup>
	    		<thead>
	    			<tr>
	    				<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></th>
	    				<th>客户名</th>
	    				<th>邮箱</th>
	    				<th class="a-center">电子商情</th>
	    				<th class="a-center">状态</th>
	    				<th>注册时间</th>
	    				<th class="a-center">管理</th>
	    			</tr>
	    		</thead>
	    		<?php if (count($customerList)>0) { ?>
	    		<tbody>
	    			<?php foreach ($customerList as $val) { ?>
	    			<tr>
	    				<td><input type="checkbox" value="<?php echo $val['customer_id']; ?>" name="selected[]" /></td>
	    				<td><?php echo $val['firstname'] . ' ' . $val['lastname']; ?></td>
	    				<td><?php echo $val['email_address']; ?></td>
	    				<td class="a-center"><?php echo $availabNewsletter[$val['newsletter']]; ?></td>
	    				<td class="a-center">[ <a title="点击 状态:<?php echo $val['status']==1?$availabStatus[0]:$availabStatus[1]; ?>" href="<?php echo href_link(FILENAME_CUSTOMER, 'action=set_status&customer_id=' . $val['customer_id']); ?>"><?php echo $availabStatus[$val['status']]; ?></a> ]</td>
	    				<td><?php echo datetime_short($val['date_added']); ?></td>
	    				<td class="a-center">[ <a href="<?php echo href_link(FILENAME_CUSTOMER, 'customer_id=' . $val['customer_id']); ?>">编辑</a> ]</td>
	    			</tr>
	    			<?php } ?>
	    		</tbody>
	    		<?php } else { ?>
	    		<tbody>
					<tr>
						<td class="a-center" colspan="6">没有结果！</td>
					</tr>
				</tbody>
	    		<?php } ?>
	    		</table>
	    		<?php require(DIR_FS_ADMIN_INCLUDES . 'pager.php'); ?>
	    		</form>
    		<?php } ?>
    		</div>
        </div>
        <?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>