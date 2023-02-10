<div class="step-title">
	<span class="number">2</span>
	<h2><?php echo __('Shipping Information'); ?></h2>
</div>
<div class="step" id="checkout-step-shipping">
	<ul class="form-list">
		<?php if (isset($addressList)) { ?>
		<li class="wide">
            <label for="shipping-address-select"><?php echo __('Select a shipping address from your address book or enter a new address.'); ?></label>
            <div class="input-box">
				<select class="form-control" onchange="newAddress('shipping');" id="shipping-address-select" name="shipping[address_id]">
					<?php foreach ($addressList as $_address) {?>
						<option value="<?php echo $_address['address_id']; ?>"<?php if ($_address['address_id']==$_SESSION['customer_shipping_address_id']) { ?> selected="selected"<?php } ?>><?php echo address_format($_address, 'text') ; ?></option>
					<?php } ?>
					<option value=""><?php echo __('New Address'); ?></option>
				</select>
			</div>
        </li>
        <?php } ?>
        <li id="shipping-new-address-form">
        <ul>
			<li class="fields">
				<div class="field">
					<label class="required" for="shipping-firstname"><em>*</em><?php echo __('First Name'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($shipping['firstname'])?$shipping['firstname']:''; ?>" name="shipping[firstname]" id="shipping-firstname" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="shipping-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($shipping['lastname'])?$shipping['lastname']:''; ?>" name="shipping[lastname]" id="shipping-lastname" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="shipping-company"><?php echo __('Company'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control" value="<?php echo isset($shipping['company'])?$shipping['company']:''; ?>" name="shipping[company]" id="shipping-company" />
					</div>
				</div>
			</li>
			<li class="wide">
				<label class="required" for="shipping-street_address"><em>*</em><?php echo __('Street Address'); ?></label>
				<div class="input-box">
					<input type="text" class="form-control required-entry" minlength="10" value="<?php echo isset($shipping['street_address'])?$shipping['street_address']:''; ?>" name="shipping[street_address]" id="shipping-street_address" />
				</div>
			</li>
			<li class="wide">
				<div class="input-box">
					<input type="text" class="form-control" value="<?php echo isset($shipping['suburb'])?$shipping['suburb']:''; ?>" name="shipping[suburb]" id="shipping-suburb" />
					</div>
				</li>
				<li class="fields">
					<div class="field">
						<label class="required" for="shipping-city"><em>*</em><?php echo __('City'); ?></label>
	 					<div class="input-box">
							<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($shipping['city'])?$shipping['city']:''; ?>" name="shipping[city]" id="shipping-city" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="shipping-region"><em id="shipping-region-em"></em><?php echo __('State/Province'); ?></label>
					<div class="input-box">
						<select class="form-control required-entry" name="shipping[region_id]" id="shipping-region_id">
							<option value=""><?php echo __('Please select region, state or province'); ?></option>
						</select>
						<input type="text" class="form-control" value="<?php echo isset($shipping['region'])?$shipping['region']:''; ?>" name="shipping[region]" id="shipping-region" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="shipping-postcode"><em>*</em><?php echo __('Zip/Postal Code'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($shipping['postcode'])?$shipping['postcode']:''; ?>" name="shipping[postcode]" id="shipping-postcode" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="shipping-country_id"><em>*</em><?php echo __('Country'); ?></label>
					<div class="input-box">
						<?php $shipping['country_id'] = isset($shipping['country_id'])?$shipping['country_id']:STORE_COUNTRY; ?>
						<?php $_availabCountry = get_countries(); ?>
						<select class="form-control required-entry" onchange="updateRegion('shipping');" name="shipping[country_id]" id="shipping-country_id">
							<option value=""><?php echo __('Please select country'); ?></option>
							<?php foreach ($_availabCountry as $key => $val) { ?>
								<option value="<?php echo $key; ?>"<?php if ($key == $shipping['country_id']) { ?> selected="selected"<?php } ?>><?php echo $val; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="shipping-telephone"><em>*</em><?php echo __('Telephone'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($shipping['telephone'])?$shipping['telephone']:''; ?>" name="shipping[telephone]" id="shipping-telephone" />
					</div>
				</div>
				<div class="field">
					<label for="shipping-fax"><?php echo __('Fax'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control" value="<?php echo isset($shipping['fax'])?$shipping['fax']:''; ?>" name="shipping[fax]" id="shipping-fax" />
					</div>
				</div>
			</li>
		</ul>
		</li>
	</ul>
	<div class="buttons-set">
        <p class="required">* <?php echo __('Required Fields'); ?></p>
    </div>
</div>
<script type="text/javascript"><!--
updateRegion('shipping', '<?php echo isset($shipping['region_id'])?$shipping['region_id']:''; ?>');
//--></script>
