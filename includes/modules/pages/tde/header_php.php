<?php
/**
 * tde header_php.php
 */
if (isset($_SESSION['order_id']) && ($orderInfo = get_order($_SESSION['order_id']))
	&& ($orderProductInfo = get_order_product($_SESSION['order_id']))) {
} else {
	redirect(href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="<?php echo STORE_LANGUAGE; ?>" lang="<?php echo STORE_LANGUAGE; ?>">
<head>
	<title><?php echo __('Online Payment'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE-Edge,chrome">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0, user-scalable=no, minimal-ui">
	<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $code_page_directory; ?>/css/styles.css?v2.0" />
</head>
<body>
<div class="page">
	<div class="header">
		<h1><?php echo __('Online Payment'); ?></h1>
	</div>
	<div class="main">
		<form id="tdeForm" method="post" action="<?php echo href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'); ?>" onSubmit="return checkForm();">
			<div class="title">
				<p><?php echo __('Order Number'); ?>: <span><?php echo put_orderNO($orderInfo['order_id']); ?></span></p>
				<p class="last"><?php echo __('Order Amount'); ?>: <span><?php echo $orderInfo['currency']['code'] . $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></span></p>
			</div>
			<div class="content">
                <div class="field">
                <label><em>*</em> <?php echo __('Type'); ?></label>
                <div class="box">
                    <select name="tde_card_type" id="selCardType" class="wide">
                        <option value="Giropay"><?php echo __('Giropay'); ?></option>
                        <option value="Directpay"><?php echo __('Sofort Banking'); ?></option>
                    </select>
                </div>
                </div>
				<div class="field">
					<label><em>*</em> <?php echo __('Account Number'); ?></label>
					<div class="box">
						<input type="text" name="tde_card_number" id="txtCardNumber" maxLength="12" onkeyup="this.value=this.value.replace(/\D/g, '')" oninput="this.value=this.value.replace(/\D/g, '')" />
					</div>
				</div>
                <div class="field">
                    <label><em>*</em> <?php echo __('Bank Code'); ?></label>
                    <div class="box">
                        <input type="text" name="tde_card_code" id="txtCardCode"  maxLength="8" onkeyup="this.value=this.value.replace(/\D/g, '')" oninput="this.value=this.value.replace(/\D/g, '')" />
                    </div>
                </div>
				<div class="field a-center last">
					<button type="submit" id="btnSubmit"><?php echo __('Submit'); ?></button>
				</div>
			</div>
			<script type="text/javascript">
				function checkForm() {
					var selCardType = document.getElementById('selCardType');
					var txtCardNumber = document.getElementById('txtCardNumber');
					var txtCardCode = document.getElementById('txtCardCode');
					var error = false;

                    if (selCardType.value.length <= 0 || (selCardType.value != 'Giropay' && selCardType.value != 'Directpay')) {
						error = true;
						selCardType.style.borderColor = '#FF0000';
						selCardType.focus();
                        return false;
                    } else { selCardType.style.borderColor = '#CCCCCC'; }

					txtCardNumber.value = txtCardNumber.value.replace(/\D/g, '');
					if (txtCardNumber.value.length < 4 || txtCardNumber.value.length > 12) {
						error = true;
						txtCardNumber.style.borderColor = '#FF0000';
						txtCardNumber.focus();
						return false;
					} else { txtCardNumber.style.borderColor = '#CCCCCC'; }

					txtCardCode.value = txtCardCode.value.replace(/\D/g, '');
                    if (txtCardCode.value.length != 8) {
						error = true;
						txtCardCode.style.borderColor = '#FF0000';
						txtCardCode.focus();
                        return false;
                    } else { txtCardCode.style.borderColor = '#CCCCCC'; }

					if (error) { return false; } else {
						var btnSubmit = document.getElementById('btnSubmit');
						if (btnSubmit.innerHTML == '<?php echo __('Submit'); ?>') {
							btnSubmit.innerHTML = '<?php echo __('Processing, please wait...'); ?>';
							return true;
						}
						alert('<?php echo __('Processing, please wait...'); ?>');
						return false;
					}
				}
			</script>
			<script type="text/javascript" src="https://risk.hdkhdkrisk.com/ssl.js"></script>
		</form>
	</div>
	<div class="footer"></div>
</div>
</body>
</html>
<?php die; ?>