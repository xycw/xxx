<div class="step-title">
	<span class="icon"><i class="iconfont">&#xe699;</i></span>
	<h2><?php echo __('Shipping Information'); ?></h2>
</div>
<div class="step" id="checkout-step-shipping">
	<?php if (isset($addressList)) { ?>
		<div class="form-group">
			<label for="shipping-address-select"><?php echo __('Select a shipping address from your address book or enter a new address.'); ?></label>
			<select class="form-control" onchange="newAddress('shipping');" id="shipping-address-select" name="shipping[address_id]">
				<?php foreach ($addressList as $_address) {?>
					<option value="<?php echo $_address['address_id']; ?>"<?php if ($_address['address_id']==$_SESSION['customer_shipping_address_id']) { ?> selected="selected"<?php } ?>><?php echo address_format($_address, 'text') ; ?></option>
				<?php } ?>
				<option value=""><?php echo __('New Address'); ?></option>
			</select>
		</div>
	<?php } ?>
	<div id="shipping-new-address-form">
		<div class="form-group">
			<label class="required" for="shipping-firstname"><em>*</em><?php echo __('First Name'); ?></label>
			<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($shipping['firstname'])?$shipping['firstname']:''; ?>" name="shipping[firstname]" id="shipping-firstname" />
		</div>
		<div class="form-group">
			<label class="required" for="shipping-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
			<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($shipping['lastname'])?$shipping['lastname']:''; ?>" name="shipping[lastname]" id="shipping-lastname" />
		</div>
		<div class="form-group">
			<label for="shipping-company"><?php echo __('Company'); ?></label>
			<input type="text" class="form-control input-text" value="<?php echo isset($shipping['company'])?$shipping['company']:''; ?>" name="shipping[company]" id="shipping-company" />
		</div>
		<div class="form-group">
			<label class="required" for="shipping-street_address"><em>*</em><?php echo __('Street Address'); ?></label>
			<input type="text" class="form-control input-text required-entry" minlength="10" value="<?php echo isset($shipping['street_address'])?$shipping['street_address']:''; ?>" name="shipping[street_address]" id="shipping-street_address" />
		</div>
		<div class="form-group">
			<input type="text" class="form-control input-text" value="<?php echo isset($shipping['suburb'])?$shipping['suburb']:''; ?>" name="shipping[suburb]" id="shipping-suburb" />
		</div>
		<div class="form-group">
			<label class="required" for="shipping-city"><em>*</em><?php echo __('City'); ?></label>
			<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($shipping['city'])?$shipping['city']:''; ?>" name="shipping[city]" id="shipping-city" />
		</div>
		<div class="form-group">
			<label class="required" for="shipping-region"><em id="shipping-region-em"></em><?php echo __('State/Province'); ?></label>
			<select class="form-control required-entry" name="shipping[region_id]" id="shipping-region_id">
				<option value=""><?php echo __('Please select region, state or province'); ?></option>
			</select>
			<input type="text" class="form-control input-text" value="<?php echo isset($shipping['region'])?$shipping['region']:''; ?>" name="shipping[region]" id="shipping-region" />
		</div>
		<div class="form-group">
			<label class="required" for="shipping-postcode"><em>*</em><?php echo __('Zip/Postal Code'); ?></label>
			<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($shipping['postcode'])?$shipping['postcode']:''; ?>" name="shipping[postcode]" id="shipping-postcode" />
		</div>
		<div class="form-group">
			<label class="required" for="shipping-country_id"><em>*</em><?php echo __('Country'); ?></label>
			<?php $shipping['country_id'] = isset($shipping['country_id'])?$shipping['country_id']:STORE_COUNTRY; ?>
			<?php $_availabCountry = get_countries(); ?>
			<select class="form-control required-entry" onchange="updateRegion('shipping');" name="shipping[country_id]" id="shipping-country_id">
				<option value=""><?php echo __('Please select country'); ?></option>
				<?php foreach ($_availabCountry as $key => $val) { ?>
					<option value="<?php echo $key; ?>"<?php if ($key == $shipping['country_id']) { ?> selected="selected"<?php } ?>><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="form-group">
			<label class="required" for="shipping-telephone"><em>*</em><?php echo __('Telephone'); ?></label>
			<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($shipping['telephone'])?$shipping['telephone']:''; ?>" name="shipping[telephone]" id="shipping-telephone" />
		</div>
		<div class="form-group">
			<label for="shipping-fax"><?php echo __('Fax'); ?></label>
			<input type="text" class="form-control input-text" value="<?php echo isset($shipping['fax'])?$shipping['fax']:''; ?>" name="shipping[fax]" id="shipping-fax" />
		</div>
	</div>
	<div class="buttons-set">
        <p class="required">* <?php echo __('Required Fields'); ?></p>
    </div>
</div>
<script type="text/javascript"><!--
updateRegion('shipping', '<?php echo isset($shipping['region_id'])?$shipping['region_id']:''; ?>');
//--></script>
