<div class="step-title">
	<span class="number">1</span>
	<h2><?php echo __('Billing Information'); ?></h2>
</div>
<div class="step" id="checkout-step-billing">
	<ul class="form-list">
		<?php if (isset($_SESSION['customer_id'])) { ?>
		<li class="wide">
            <label for="billing-address-select"><?php echo __('Select a billing address from your address book or enter a new address.'); ?></label>
            <div class="input-box">
				<select class="form-control" onchange="newAddress('billing');" id="billing-address-select" name="billing[address_id]">
					<?php foreach ($addressList as $_address) {?>
						<option value="<?php echo $_address['address_id']; ?>"<?php if ($_address['address_id']==$_SESSION['customer_billing_address_id']) { ?> selected="selected"<?php } ?>><?php echo address_format($_address, 'text') ; ?></option>
					<?php } ?>
					<option value=""><?php echo __('New Address'); ?></option>
				</select>
			</div>
        </li>
        <?php } ?>
        <li id="billing-new-address-form">
        <ul>
			<li class="fields">
				<div class="field">
					<label class="required" for="billing-firstname"><em>*</em><?php echo __('First Name'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($billing['firstname'])?$billing['firstname']:''; ?>" name="billing[firstname]" id="billing-firstname" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="billing-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($billing['lastname'])?$billing['lastname']:''; ?>" name="billing[lastname]" id="billing-lastname" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="billing-company"><?php echo __('Company'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control" value="<?php echo isset($billing['company'])?$billing['company']:''; ?>" name="billing[company]" id="billing-company" />
					</div>
				</div>
				<?php if (!isset($_SESSION['customer_id'])) { ?>
				<div class="field">
					<label class="required" for="billing-email_address"><em>*</em><?php echo __('Email Address'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry validate-email" minlength="10" value="<?php echo isset($billing['email_address'])?$billing['email_address']:''; ?>" name="billing[email_address]" id="billing-email_address" />
					</div>
				</div>
				<?php } ?>
			</li>
			<li class="wide">
				<label class="required" for="billing-street_address"><em>*</em><?php echo __('Street Address'); ?></label>
				<div class="input-box">
					<input type="text" class="form-control required-entry" minlength="10" value="<?php echo isset($billing['street_address'])?$billing['street_address']:''; ?>" name="billing[street_address]" id="billing-street_address" />
				</div>
			</li>
			<li class="wide">
				<div class="input-box">
					<input type="text" class="form-control" value="<?php echo isset($billing['suburb'])?$billing['suburb']:''; ?>" name="billing[suburb]" id="billing-suburb" />
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="billing-city"><em>*</em><?php echo __('City'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($billing['city'])?$billing['city']:''; ?>" name="billing[city]" id="billing-city" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="billing-region"><em id="billing-region-em"></em><?php echo __('State/Province'); ?></label>
					<div class="input-box">
						<select class="form-control required-entry" name="billing[region_id]" id="billing-region_id">
							<option value=""><?php echo __('Please select region, state or province'); ?></option>
						</select>
						<input type="text" class="form-control" value="<?php echo isset($billing['region'])?$billing['region']:''; ?>" name="billing[region]" id="billing-region" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="billing-postcode"><em>*</em><?php echo __('Zip/Postal Code'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($billing['postcode'])?$billing['postcode']:''; ?>" name="billing[postcode]" id="billing-postcode" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="billing-country_id"><em>*</em><?php echo __('Country'); ?></label>
					<div class="input-box">
						<?php $billing['country_id'] = isset($billing['country_id'])?$billing['country_id']:STORE_COUNTRY; ?>
						<?php $_availabCountry = get_countries(); ?>
						<select class="form-control required-entry" onchange="updateRegion('billing');" name="billing[country_id]" id="billing-country_id">
							<option value=""><?php echo __('Please select country'); ?></option>
							<?php foreach ($_availabCountry as $key => $val) { ?>
								<option value="<?php echo $key; ?>"<?php if ($key == $billing['country_id']) { ?> selected="selected"<?php } ?>><?php echo $val; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="billing-telephone"><em>*</em><?php echo __('Telephone'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control required-entry" minlength="2" value="<?php echo isset($billing['telephone'])?$billing['telephone']:''; ?>" name="billing[telephone]" id="billing-telephone" />
					</div>
				</div>
				<div class="field">
					<label for="billing-fax"><?php echo __('Fax'); ?></label>
					<div class="input-box">
						<input type="text" class="form-control" value="<?php echo isset($billing['fax'])?$billing['fax']:''; ?>" name="billing[fax]" id="billing-fax" />
					</div>
				</div>
			</li>
			<?php if (isset($_SESSION['customer_id'])) { ?>
			<li class="control">
				<input type="checkbox" class="checkbox" title="<?php echo __('Save in address book'); ?>" value="1" name="save_in_address_book" id="save_in_address_book" /><label for="save_in_address_book"><?php echo __('Save in address book'); ?></label>
			</li>
			<?php } ?>
		</ul>
		</li>
		<li class="control">
			<input type="radio" class="radio"<?php if ((isset($use_for_shipping)&&$use_for_shipping==1) || !isset($use_for_shipping)) { ?> checked="checked"<?php } ?> onclick="same_as_billing();" value="1" name="use_for_shipping" id="use_for_shipping_yes" />
            <label for="use_for_shipping_yes"><?php echo __('Ship to this address'); ?></label>
		</li>
		<li class="control">
			<input type="radio" class="radio"<?php if (isset($use_for_shipping)&&$use_for_shipping!=1) { ?> checked="checked"<?php } ?> onclick="same_as_billing();" value="0" name="use_for_shipping" id="use_for_shipping_no" />
			<label for="use_for_shipping_no"><?php echo __('Ship to different address'); ?></label>
        </li>
	</ul>
	<div class="buttons-set">
        <p class="required">* <?php echo __('Required Fields'); ?></p>
    </div>
</div>
<script type="text/javascript"><!--
updateRegion('billing', '<?php echo isset($billing['region_id'])?$billing['region_id']:''; ?>');
//--></script>
