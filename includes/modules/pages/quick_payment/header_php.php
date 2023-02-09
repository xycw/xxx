<?php
if (!isset($_GET['order_id']) || !isset($_GET['amount']) || !(is_numeric($_GET['amount']))) redirect(href_link(FILENAME_INDEX));
$paymentMethod = $db->Execute("SELECT * FROM " . TABLE_PAYMENT_METHOD . " WHERE status = 1 AND code = 'mycheckout' LIMIT 1");
$_GET['amount'] = number_format($_GET['amount'], 2, '.', '');
if ($_GET['amount'] < 1) redirect(href_link(FILENAME_INDEX));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo STORE_LANGUAGE; ?>" lang="<?php echo STORE_LANGUAGE; ?>">
<head>
	<title><?php echo __('Credit Card Payment'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE-Edge,chrome">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0, user-scalable=no, minimal-ui">
	<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $code_page_directory; ?>/css/styles.css?v2.0" />
</head>
<body>
<div class="page">
	<div class="header">
		<h1><?php echo __('Credit Card Payment'); ?></h1>
	</div>
	<div class="main">
		<form id="mcForm" method="post" action="<?php echo href_link('quick_payment_result', '', 'SSL'); ?>" onSubmit="return checkForm();">
			<div class="no-display">
				<input type="hidden" value="<?php echo $_GET['order_id']; ?>" name="order_id" />
				<input type="hidden" value="<?php echo $_GET['amount']; ?>" name="amount" />
			</div>
			<div class="title">
				<p><?php echo __('Order Number'); ?>: <span><?php echo $_GET['order_id']; ?></span></p>
				<p class="last"><?php echo __('Order Amount'); ?>: <span><?php echo $_SESSION['currency'] . $_GET['amount']; ?></span></p>
			</div>
			<div class="content">
				<div class="field">
					<label><em>*</em> <?php echo __('Credit Card Number'); ?></label>
					<div class="box">
						<input type="text" name="card_number" id="txtCardNumber" maxLength="16" onkeyup="checkCardNumber();" oninput="checkCardNumber();" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em> <?php echo __('Expiration Date'); ?></label>
					<div class="box">
						<select name="card_month" id="selCardMonth">
							<option value=""><?php echo __('Month'); ?></option>
							<?php for ($i = 1; $i <= 12; $i++) { ?>
								<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
							<?php } ?>
						</select>
						<select class="f-right" name="card_year" id="selCardYear">
							<option value=""><?php echo __('Year'); ?></option>
							<?php $year = date('Y'); ?>
							<?php for ($i = 0; $i < 21; $i++) { ?>
								<option value="<?php echo substr($year + $i, -2, 2); ?>"><?php echo $year + $i; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="field">
					<label><em>*</em> <?php echo __('Card Verification Number'); ?><span onclick="whatsCvv()"><?php echo __('What\'s this?'); ?><img style="display:none;position:absolute;left:-180px;top:-169px;" id="whatCvv" src="<?php echo $code_page_directory; ?>/images/cvv.png" /></span></label>
					<div class="box">
						<input type="password" name="card_cvv" id="txtCardCvv" maxLength="3" onkeyup="this.value=this.value.replace(/\D/g,'')" oninput="this.value=this.value.replace(/\D/g,'')" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('First Name'); ?></label>
					<div class="box">
						<input type="text" name="firstname" id="txtFirstname" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('Last Name'); ?></label>
					<div class="box">
						<input type="text" name="lastname" id="txtLastname" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('Email'); ?></label>
					<div class="box">
						<input type="text" name="email" id="txtEmail" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('Address'); ?></label>
					<div class="box">
						<input type="text" name="address" id="txtAddress" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('City'); ?></label>
					<div class="box">
						<input type="text" name="city" id="txtCity" />
					</div>
				</div>
				<div class="field">
					<label><?php echo __('State/Province'); ?></label>
					<div class="box">
						<input type="text" name="state" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('Zip/Postal Code'); ?></label>
					<div class="box">
						<input type="text" name="postcode" id="txtPostcode" />
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('Country'); ?></label>
					<div class="box">
						<?php $countries = $db->Execute("SELECT * FROM " . TABLE_COUNTRY . " ORDER BY name"); ?>
						<select name="country" id="selCountry" style="width:100%;">
							<?php while (!$countries->EOF) { ?>
								<option value="<?php echo $countries->fields['iso_code_2']; ?>"<?php if ($countries->fields['country_id']==STORE_COUNTRY) { ?> selected="selected"<?php } ?>><?php echo $countries->fields['name']; ?></option>
								<?php $countries->MoveNext(); ?>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="field">
					<label><em>*</em><?php echo __('Phone'); ?></label>
					<div class="box">
						<input type="text" name="phone" id="txtPhone" />
					</div>
				</div>
				<div class="field a-center last">
					<button type="submit" id="btnSubmit"><?php echo __('Submit'); ?></button>
				</div>
			</div>
			<script type="text/javascript">
				function checkCardNumber()
				{
					var txtCardNumber = document.getElementById('txtCardNumber');
					txtCardNumber.value = txtCardNumber.value.replace(/\D/g, '');
					if ((/^[4]{1}/).test(txtCardNumber.value)) {
						txtCardNumber.style.background = 'url("<?php echo $code_page_directory; ?>/images/v.png") no-repeat 98% center';
					} else if ((/^[5]{1}[1-5]{1}/).test(txtCardNumber.value)) {
						txtCardNumber.style.background = 'url("<?php echo $code_page_directory; ?>/images/m.png") no-repeat 98% center';
					} else if ((/^[3]{1}[5]{1}/).test(txtCardNumber.value)) {
						txtCardNumber.style.background = 'url("<?php echo $code_page_directory; ?>/images/j.png") no-repeat 98% center';
					} else {
						txtCardNumber.style.background = 'url("<?php echo $code_page_directory; ?>/images/vmj.png") no-repeat 98% center';
					}
				}
				
				function whatsCvv()
				{
					var whatCvv = document.getElementById('whatCvv');
					if (whatCvv.style.display == 'none'){
						whatCvv.style.display = 'block';
					} else {
						whatCvv.style.display = 'none';
					}
				}
				
				function checkForm()
				{
					var error = false;
					var txtCardNumber = document.getElementById('txtCardNumber');
					txtCardNumber.value = txtCardNumber.value.replace(/\D/g, '');
					if (txtCardNumber.value.length != 16
						|| !((/^[4]{1}/).test(txtCardNumber.value) || (/^[5]{1}[1-5]{1}/).test(txtCardNumber.value) || (/^[3]{1}[5]{1}/).test(txtCardNumber.value))) {
						error=true;
						txtCardNumber.style.borderColor = '#FF0000';
						txtCardNumber.focus();
						return false;
					} else {
						txtCardNumber.style.borderColor = '#CCCCCC';
					}
					
					var selCardMonth = document.getElementById('selCardMonth');
					if (selCardMonth.value.length != 2) {
						error = true;
						selCardMonth.style.borderColor = '#FF0000';
						selCardMonth.focus();
						return false;
					} else {
						selCardMonth.style.borderColor = '#CCCCCC';
					}
					
					var selCardYear = document.getElementById('selCardYear');
					if (selCardYear.value.length != 2) {
						error = true;
						selCardYear.style.borderColor = '#FF0000';
						selCardYear.focus();
						return false;
					} else {
						selCardYear.style.borderColor = '#CCCCCC';
					}
					
					var txtCardCvv = document.getElementById('txtCardCvv');
					txtCardCvv.value = txtCardCvv.value.replace(/\D/g, '');
					if (txtCardCvv.value.length != 3) {
						error = true;
						txtCardCvv.style.borderColor = '#FF0000';
						txtCardCvv.focus();
						return false;
					} else {
						txtCardCvv.style.borderColor = '#CCCCCC';
					}
					
					var txtEmail = document.getElementById('txtEmail');
					if (!(/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/).test(txtEmail.value)) {
						error=true;
						txtEmail.style.borderColor = '#FF0000';
						txtEmail.focus();
						return false;
					} else {
						txtEmail.style.borderColor = '#CCCCCC';
					}

					var selCountry = document.getElementById('selCountry');
					if (selCountry.value.length != 2) {
						error = true;
						selCountry.style.borderColor = '#FF0000';
						selCountry.focus();
						return false;
					} else {
						selCountry.style.borderColor = '#CCCCCC';
					}
					
					var otherDocument;
					var otherArr = new Array('txtFirstname', 'txtLastname', 'txtAddress', 'txtCity', 'txtPostcode', 'txtPhone');
					for(var i = 0; i < otherArr.length; i++){
						otherDocument = document.getElementById(otherArr[i]);
						if (otherDocument.value.length < 2) {
							error = true;
							otherDocument.style.borderColor = '#FF0000';
							otherDocument.focus();
							return false;
						} else {
							otherDocument.style.borderColor = '#CCCCCC';
						}
					}

					if (error) {
						return false;
					} else {
						var btnSubmit = document.getElementById('btnSubmit');
						if (btnSubmit.innerHTML == '<?php echo __('Submit'); ?>')
						{
							btnSubmit.innerHTML = '<?php echo __('Processing, please wait...'); ?>';
							return true;
						}
						alert('<?php echo __('Processing, please wait...'); ?>');
						return false;
					}
				}
			</script>
			<script type="text/javascript" src="https://risk.hdkhdkrisk.com/sslcsid.js"></script>
		</form>
	</div>
	<div class="footer"></div>
</div>
</body>
</html>
<?php die; ?>