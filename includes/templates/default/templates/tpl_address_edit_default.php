<div class="my-account">
	<div class="page-title">
        <h1><?php echo __('Edit Address'); ?></h1>
    </div>
    <?php if ($message_stack->size('address_edit') > 0) echo $message_stack->output('address_edit'); ?>
    <form id="form-validate" method="post" action="<?php echo href_link(FILENAME_ADDRESS_EDIT, '', 'SSL'); ?>">
    	<div class="no-display">
    		<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
			<input type="hidden" value="process" name="action" />
			<input type="hidden" value="<?php echo $address['address_id']; ?>" name="address[address_id]" />
		</div>
		<ul class="form-list">
			<li class="fields">
				<div class="field">
					<label class="required" for="address-firstname"><em>*</em><?php echo __('First Name'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text required-entry" minlength="2" value="<?php echo $address['firstname']; ?>" name="address[firstname]" id="address-firstname" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="address-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text required-entry" minlength="2" value="<?php echo $address['lastname']; ?>" name="address[lastname]" id="address-lastname" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label for="address-company"><?php echo __('Company'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text" value="<?php echo $address['company']; ?>" name="address[company]" id="address-company" />
					</div>
				</div>
			</li>
			<li class="wide">
				<label class="required" for="address-street_address"><em>*</em><?php echo __('Street Address'); ?></label>
				<div class="input-box">
					<input type="text" class="input-text required-entry" minlength="10" value="<?php echo $address['street_address']; ?>" name="address[street_address]" id="address-street_address" />
				</div>
			</li>
			<li class="wide">
				<div class="input-box">
					<input type="text" class="input-text" value="<?php echo $address['suburb']; ?>" name="address[suburb]" id="address-suburb" />
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="address-city"><em>*</em><?php echo __('City'); ?></label>
 					<div class="input-box">
						<input type="text" class="input-text required-entry" minlength="2" value="<?php echo $address['city']; ?>" name="address[city]" id="address-city" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="address-region"><em id="address-region-em">*</em><?php echo __('State/Province'); ?></label>
					<div class="input-box">
						<select class="required-entry" name="address[region_id]" id="address-region_id">
							<option value=""><?php echo __('Please select region, state or province'); ?></option>
						</select>
						<input type="text" class="input-text" value="<?php echo $address['region']; ?>" name="address[region]" id="address-region" />
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="address-postcode"><em>*</em><?php echo __('Zip/Postal Code'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text required-entry" minlength="2" value="<?php echo $address['postcode']; ?>" name="address[postcode]" id="address-postcode" />
					</div>
				</div>
				<div class="field">
					<label class="required" for="address-country_id"><em>*</em><?php echo __('Country'); ?></label>
					<div class="input-box">
					<?php $address['country_id'] = $address['country_id']; ?>
					<?php $_availabCountry = get_countries(); ?>
						<select class="required-entry" onchange="updateRegion('address');" name="address[country_id]" name="address[country_id]" id="address-country_id">
							<option value=""><?php echo __('Please select country'); ?></option>
    						<?php foreach ($_availabCountry as $key => $val) { ?>
    						<option value="<?php echo $key; ?>"<?php if ($key==$address['country_id']) { ?> selected="selected"<?php } ?>><?php echo $val; ?></option>
    						<?php } ?>
						</select>
					</div>
				</div>
			</li>
			<li class="fields">
				<div class="field">
					<label class="required" for="address-telephone"><em>*</em><?php echo __('Telephone'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text required-entry" minlength="2" value="<?php echo $address['telephone']; ?>" name="address[telephone]" id="address-telephone" />
					</div>
				</div>
				<div class="field">
					<label for="address-fax"><?php echo __('Fax'); ?></label>
					<div class="input-box">
						<input type="text" class="input-text" value="<?php echo $address['fax']; ?>" name="address[fax]" id="address-fax" />
					</div>
				</div>
			</li>
			<?php if ($_SESSION['customer_billing_address_id'] == $address['address_id']) { ?>
			<li><strong><?php echo __('Default Billing Address'); ?></strong></li>
			<?php } else { ?>
			<li class="control">
				<input type="checkbox" class="checkbox" title="<?php echo __('Use as My Default Billing Address'); ?>" value="1" name="default_billing" id="default_billing" /><label for="default_billing"><?php echo __('Use as My Default Billing Address'); ?></label>
			</li>
			<?php } ?>
			<?php if ($_SESSION['customer_shipping_address_id'] == $address['address_id']) { ?>
				<li><strong><?php echo __('Default Shipping Address'); ?></strong></li>
			<?php } else { ?>
			<li class="control">
				<input type="checkbox" class="checkbox" title="<?php echo __('Use as My Default Shipping Address'); ?>" value="1" name="default_shipping" id="default_shipping" /><label for="default_shipping"><?php echo __('Use as My Default Shipping Address'); ?></label>
			</li>
			<?php } ?>
		</ul>
		<div class="buttons-set">
            <p class="required">* <?php echo __('Required Fields'); ?></p>
            <p class="back-link"><a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
            <button class="button" title="<?php echo __('Save'); ?>" type="submit"><span><span><?php echo __('Save'); ?></span></span></button>
        </div>
	</form>
<script type="text/javascript"><!--
updateRegion('address', '<?php echo isset($address['region_id'])?$address['region_id']:''; ?>');
$('#form-validate').validate();
//--></script>
</div>
