<?php if (isset($productInfo['attribute']) && count($productInfo['attribute']) > 0) { ?>
<div id="product-options-wrapper" class="product-options">
<?php foreach ($productInfo['attribute'] as $_option) { ?>
<?php
	$optionValueHtml='';
	$optionvalueRequired = $_option['required'] ? 'required-entry' : '';
	switch ($_option['type']) {
		//'select','text','radio','checkbox','wholesale','list'
		case 'select' :
			$optionValueHtml .= '<select class="' . $optionvalueRequired . ' form-control" name="attribute[' . $_option['product_option_id'] . ']" id="attribute_' . $_option['product_option_id'] . '">';
			$optionValueHtml .= '<option value="">-- ' . __('Please Select') . ' --</option>';
			foreach ($_option['value'] as $_optionValue) {
				$optionValuePriceHtml = $_optionValue['price'] > 0 ? ' ( ' . $_optionValue['price_prefix'] . $currencies->display_price($_optionValue['price']) . ' )' : '';
				$optionValueHtml .= '<option value="' . $_optionValue['product_option_value_id'] . '">' . __($_optionValue['name']) . $optionValuePriceHtml . '</option>';
			}
			$optionValueHtml .= '</select>';
		break;
		case 'text' :
			$_optionValue = current($_option['value']);
			$optionValuePriceHtml = $_optionValue['price'] > 0 ? ' ( ' . $_optionValue['price_prefix'] . $currencies->display_price($_optionValue['price']) . ' )' : '';
			$optionValueHtml .= '<input type="text" class="input-text form-control ' . $optionvalueRequired . '" value="" name="attribute[' . $_option['product_option_id'] . ']" id="attribute_' . $_option['product_option_id'] . '" placeholder="' . '" /><br />' . $optionValuePriceHtml;
		break;
		case 'radio' :
			foreach ($_option['value'] as $_optionValue) {
				$optionValuePriceHtml = $_optionValue['price'] > 0 ? ' ( ' . $_optionValue['price_prefix'] . $currencies->display_price($_optionValue['price']) . ' )' : '';
				$optionValueHtml .= '<label class="label-radio" for="attribute_' . $_option['product_option_id'] . '_' . $_optionValue['product_option_value_id'] . '">';
				$optionValueHtml .= '<input type="radio" class="' . $optionvalueRequired . '" value="' . $_optionValue['product_option_value_id'] . '" name="attribute[' . $_option['product_option_id'] . ']" id="attribute_' . $_option['product_option_id'] . '_' . $_optionValue['product_option_value_id'] . '" />';
				$optionValueHtml .= '' . __($_optionValue['name']) . $optionValuePriceHtml . '</label><br />';
			}
		break;
		case 'checkbox' :
			foreach ($_option['value'] as $_optionValue) {
				$optionValuePriceHtml = $_optionValue['price'] > 0 ? ' ( ' . $_optionValue['price_prefix'] . $currencies->display_price($_optionValue['price']) . ' )' : '';
				$optionValueHtml .= '<label class="label-checkbox" for="attribute_' . $_option['product_option_id'] . '_' . $_optionValue['product_option_value_id'] . '">';
				$optionValueHtml .= '<input type="checkbox" class="' . $optionvalueRequired . '" value="' . $_optionValue['product_option_value_id'] . '" name="attribute[' . $_option['product_option_id'] . '][]" id="attribute_' . $_option['product_option_id'] . '_' . $_optionValue['product_option_value_id'] . '" />';
				$optionValueHtml .= '' . __($_optionValue['name']) . $optionValuePriceHtml . '</label><br />';
			}
		break;
		case 'list' :
			$optionValueHtml .= '<ul class="options-list">';
			foreach ($_option['value'] as $_optionValue) {
				$optionValuePriceHtml = $_optionValue['price'] > 0 ? ' ( ' . $_optionValue['price_prefix'] . $currencies->display_price($_optionValue['price']) . ' )' : '';
				$optionValueHtml .= '<li onclick="$(\'#attribute_' . $_option['product_option_id'] . '\').val('.$_optionValue['product_option_value_id'].');">' . __($_optionValue['name']) . $optionValuePriceHtml . '</li>';
			}
			$optionValueHtml .= '</ul>';
			$optionValueHtml .= '<input type="hidden" class="' . $optionvalueRequired . '" value="" name="attribute[' . $_option['product_option_id'] . ']" id="attribute_' . $_option['product_option_id'] . '" />';
		break;
		case 'wholesale' :
			$optionValueHtml .= '<table class="options-wholesale-list">';
			$optionValueHtml .= '<tr><th>'. __($_option['name']) .'</th><td>'. __('Qty') .'</td></tr>';
			foreach ($_option['value'] as $_optionValue) {
				$optionValuePriceHtml = $_optionValue['price'] > 0 ? ' ( ' . $_optionValue['price_prefix'] . $currencies->display_price($_optionValue['price']) . ' )' : '';
				$optionValueHtml .= '<tr>';
				$optionValueHtml .= '<th><label for="qty_' . $_option['product_option_id'] . '_' . $_optionValue['product_option_value_id'] .'">'. __($_optionValue['name']) .'</label></th>';
				$optionValueHtml .= '<td><input type="text" name="qty[' . $_option['product_option_id'] . '][' . $_optionValue['product_option_value_id'] . ']" id="qty_' . $_option['product_option_id'] . '_' . $_optionValue['product_option_value_id'] . '" maxlength="3" value="0" title="Qty" class="input-text ' . $optionvalueRequired . ' qty valid" onblur="if($(this).val()==\'\') $(this).val(0);" onfocus="if($(this).val()==\'0\') $(this).val(\'\');" />' . $optionValuePriceHtml;
				$optionValueHtml .= '</td></tr>';
			}
			$optionValueHtml .= '</table>';
			$productInfo['qty'] = false;
		break;
	}
?>
	<dl>
		<?php if ($productInfo['qty'] == true) { ?>
		<dt>
			<label class="required"><?php echo $_option['required'] ? '<em>*</em> ' : ''; ?><?php echo __($_option['name']); ?></label>
			<?php if ($productInfo['qty'] == true) { ?>
				<span class="required">* <?php echo __('Required Fields'); ?></span>
			<?php } ?>
		</dt>
		<?php } ?>
		<dd class="options-<?php echo $_option['type']; ?>">
			<div class="input-box"><?php echo $optionValueHtml; ?></div>
		</dd>
	</dl>
<?php } ?>

</div>
<script type="text/javascript"><!--
decorateDataList($("#product-options-wrapper dl"));
$("#product-options-wrapper li").click(function(){
	$(this).addClass("active").siblings().removeClass("active");
});
//--></script>
<?php } ?>
