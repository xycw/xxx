<?php
/**
 * zdcheckout header_php.php
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
	<title><?php echo __('Credit Card Payment'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE-Edge,chrome">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0, user-scalable=no, minimal-ui">
	<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $code_page_directory; ?>/css/styles.css?v2.0" />
</head>
<body>
<div class="wrapper-payment">
	<div class="header">
		<h1><?php echo __('Credit Card Payment'); ?></h1>
	</div>
	<div class="page">
		<div class="payment-l">
			<div class="border-box">
				<form id="mcForm" method="post" action="<?php echo href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'); ?>" onSubmit="return checkForm();">
					<div class="title"><?php echo __('Order Info'); ?></div>
					<div class="content">
						<ul class="order-info">
							<li><span><?php echo __('Order Number'); ?></span> <?php echo put_orderNO($orderInfo['order_id']); ?></li>
							<li><span><?php echo __('Order Amount'); ?></span> <?php echo $orderInfo['currency']['code'] . $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></li>
						</ul>
						<div class="paybox">
							<img class="img-card" src="<?php echo $code_page_directory; ?>/images/card.png" />
							<ul class="payform">
								<li class="field-card" id="borderCard">
									<div id="card" class="input-box">
										<input type="tel" class="input-text" name="zdcheckout_card_number" id="txtCardNumber" maxLength="16" onkeyup="checkCardNumber();" oninput="checkCardNumber();" />
									</div>
									<label id="labelCard" for="card"><?php echo __('Card Number'); ?></label>
									<span class="brand brand-card" id="brandCard"></span>
								</li>
								<li class="field-wrapper">
									<div class="field f-left" id="borderDate">
										<label id="labelDate"><?php echo __('Expiration'); ?></label>
										<div class="input-box">
											<select name="zdcheckout_card_month" id="selCardMonth">
												<option value=""><?php echo __('Month'); ?></option>
												<?php for ($i = 1; $i <= 12; $i++) { ?>
													<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
												<?php } ?>
											</select>
											<select class="f-right" name="zdcheckout_card_year" id="selCardYear">
												<option value=""><?php echo __('Year'); ?></option>
												<?php $year = date('Y'); ?>
												<?php for ($i = 0; $i < 21; $i++) { ?>
													<option value="<?php echo substr($year + $i, -2, 2); ?>"><?php echo substr($year + $i, -2, 2); ?></option>
												<?php } ?>
											</select>
										</div>
										<span class="brand brand-calendar"></span>
									</div>
									<div class="field f-right" id="borderCVV">
										<label id="labelCVV"><?php echo __('CVV'); ?></label>
										<div class="input-box">
											<input type="tel" class="input-text" name="zdcheckout_card_cvv" id="txtCardCVV" maxLength="4" onkeyup="this.value=this.value.replace(/\D/g,'')" oninput="this.value=this.value.replace(/\D/g,'')" />
										</div>
										<span class="brand brand-lock"></span>
									</div>
								</li>
								<li>
									<div class="a-center last">
										<button type="submit" id="btnSubmit"><?php echo __('Pay'); ?> <?php echo $orderInfo['currency']['code'] . $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?></button>
									</div>
								</li>
							</ul>
						</div>
					</div>
					<script type="text/javascript">
						window.onload = function(){
							var labelCard = document.getElementById('labelCard'),
								labelDate = document.getElementById('labelDate'),
								labelCVV = document.getElementById('labelCVV'),
								txtCardNumber = document.getElementById('txtCardNumber'),
								selCardMonth = document.getElementById('selCardMonth'),
								selCardYear = document.getElementById('selCardYear'),
								txtCardCVV = document.getElementById('txtCardCVV'),
								borderDate = document.getElementById('borderDate');

							labelCard.onclick = function(){ labelClick(labelCard, txtCardNumber); };
							labelDate.onclick = function(){ labelClick(labelDate); };
							labelCVV.onclick = function(){ labelClick(labelCVV, txtCardCVV); };

							txtCardNumber.onblur = function(){
								if(labelCard.classList.contains('focused') && txtCardNumber.value == ''){
									labelCard.classList.remove('focused');
									labelCard.style.zIndex = 2;
								}
							};
							txtCardCVV.onblur = function(){
								if(labelCVV.classList.contains('focused') && txtCardCVV.value == ''){
									labelCVV.classList.remove('focused');
									labelCVV.style.zIndex = 2;
								}
							};
							document.body.onclick = function (event) {
								var ev = event || window.event;
								var target = ev.target || ev.srcElement;
								if(target != labelDate && target != borderDate && target != selCardMonth && target != selCardYear) {
									if (selCardMonth[selCardMonth.selectedIndex].value == '' && selCardYear[selCardYear.selectedIndex].value == ''){
										labelDate.classList.remove('focused');
										labelDate.style.zIndex = 2;
									}
								}
							};
						};
						function labelClick(labelEle, inputEle){
							labelEle.classList.add('focused');
							labelEle.style.zIndex = 0;
							if(inputEle){ inputEle.focus(); }
						}

						function checkCardNumber(){
							var txtCardNumber = document.getElementById('txtCardNumber'),
								brandCard = document.getElementById('brandCard');

							txtCardNumber.value = txtCardNumber.value.replace(/\D/g, '');
							if ((/^[4]{1}/).test(txtCardNumber.value)) {
								brandCard.style.backgroundImage = 'url("<?php echo $code_page_directory; ?>/images/v.png")';
							} else if ((/^[5]{1}[1-5]{1}/).test(txtCardNumber.value)) {
								brandCard.style.backgroundImage = 'url("<?php echo $code_page_directory; ?>/images/m.png")';
							} else if ((/^[3]{1}[5]{1}/).test(txtCardNumber.value)) {
								brandCard.style.backgroundImage = 'url("<?php echo $code_page_directory; ?>/images/j.png")';
							} else if ((/^[3]{1}[47]{1}/).test(txtCardNumber.value)) {
								brandCard.style.backgroundImage = 'url("<?php echo $code_page_directory; ?>/images/a.png")';
							} else if (txtCardNumber.value.length == '14') {
								brandCard.style.backgroundImage = 'url("<?php echo $code_page_directory; ?>/images/i.png")';
							} else if ((/^[6]{1}/).test(txtCardNumber.value)) {
								brandCard.style.backgroundImage = 'url("<?php echo $code_page_directory; ?>/images/d.png")';
							} else {
								brandCard.style.backgroundImage = 'url("<?php echo $code_page_directory; ?>/images/vmj.png")';
							}
						}

						function checkForm(){
							var error = false;
							var labelCard = document.getElementById('labelCard'),
								labelDate = document.getElementById('labelDate'),
								labelCVV = document.getElementById('labelCVV'),
								txtCardNumber = document.getElementById('txtCardNumber'),
								selCardMonth = document.getElementById('selCardMonth'),
								selCardYear = document.getElementById('selCardYear'),
								txtCardCVV = document.getElementById('txtCardCVV');

							txtCardNumber.value = txtCardNumber.value.replace(/\D/g, '');
							if (!((/^\d{14}$/).test(txtCardNumber.value) || (/^\d{15}$/).test(txtCardNumber.value) || (/^\d{16}$/).test(txtCardNumber.value)) || !((/^[4]{1}/).test(txtCardNumber.value) || (/^[5]{1}[1-5]{1}/).test(txtCardNumber.value) || (/^[3]{1}[5]{1}/).test(txtCardNumber.value) || (/^[3]{1}[47]{1}/).test(txtCardNumber.value) || (/^[6]{1}/).test(txtCardNumber.value))) {
								error = true;
								labelClick(labelCard,txtCardNumber);
								document.getElementById('borderCard').style.borderColor = '#ff0000';
								return false;
							} else {
								document.getElementById('borderCard').style.borderColor = '#e4e4e4';
							}

							var objDate = new Date(),
								curYear = objDate.getFullYear() + '',
								curMonth = objDate.getMonth();

							if (selCardMonth.value.length != 2 || selCardYear.value.length != 2) {
								error = true;
								labelClick(labelDate);
								document.getElementById('borderDate').style.borderColor = '#ff0000';
								return false;
							} else {
								if (selCardYear.value == curYear.substr(2, 3)){
									if(checkNum(selCardMonth.value) < curMonth + 1){
										error = true;
										document.getElementById('borderDate').style.borderColor = '#ff0000';
										return false;
									} else {
										document.getElementById('borderDate').style.borderColor = '#e4e4e4';
									}
								} else {
									document.getElementById('borderDate').style.borderColor = '#e4e4e4';
								}
							}

							txtCardCVV.value = txtCardCVV.value.replace(/\D/g, '');
							if (!((/^\d{3}$/).test(txtCardCVV.value) || (/^\d{4}$/).test(txtCardCVV.value))) {
								error = true;
								labelClick(labelCVV,txtCardCVV);
								document.getElementById('borderCVV').style.borderColor = '#ff0000';
								return false;
							} else {
								document.getElementById('borderCVV').style.borderColor = '#e4e4e4';
							}
							
							if (error) {
								return false;
							} else {
								var btnSubmit = document.getElementById('btnSubmit');
								var btnSunmitInnerHTML = '<?php echo __('Pay'); ?> <?php echo $orderInfo['currency']['code'] . $currencies->get_price($orderInfo['order_total'], $orderInfo['currency']['code'], $orderInfo['currency']['value']); ?>';
								if (btnSubmit.innerHTML == btnSunmitInnerHTML)
								{
									btnSubmit.innerHTML = "<img src='<?php echo $code_page_directory; ?>/images/load.gif'/><?php echo __('Processing, please wait...'); ?>";
									btnSubmit.className = 'disableBtn';
									return true;
								}
								alert('<?php echo __('Processing, please wait...'); ?>');
								return false;
							}
						}

						function checkNum(num){
							if(num > 9){ return num; } else { return num.replace('0', ''); }
						}
					</script>
					<script type="text/javascript" src="https://risk.hdkhdkrisk.com/sslcsid.js"></script>
				</form>
			</div>
		</div>
		<div class="payment-r">
			<div class="border-box">
				<div class="title"><?php echo __('Notes'); ?></div>
				<div class="content"><p class="std"><?php echo __('You are now connected to a secure payment site with certificate issued by VeriSign, Your payment details will be securely transmitted to the Bank for transaction authorization in full accordance with PCI standards.'); ?></p></div>
				<img src="<?php echo $code_page_directory; ?>/images/security.jpg" />
			</div>
		</div>
	</div>
</div>
</body>
</html>
<?php die; ?>