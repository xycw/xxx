<?php if ($currencyList = $currencies->getData()) { ?>
<div class="currency">
	<div class="block-title"><?php echo __('Currency'); ?></div>
    <div class="block-content">
		<select class="form-control" onchange="setLocation(this.value);" title="<?php echo __('Select Your Currency'); ?>" name="currency">
			<?php foreach ($currencyList as $key => $val) { ?>
				<option<?php if ($_SESSION['currency']==$key) { ?> selected="selected"<?php } ?> value="<?php echo href_link($current_page, get_all_get_params(array('currency')) . 'currency=' . $key); ?>"><?php echo $val['name']; ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="clearer"></div>
</div>
<?php } ?>
