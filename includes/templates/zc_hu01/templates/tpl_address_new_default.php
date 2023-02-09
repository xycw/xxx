<div class="my-account">
	<div class="account-top">
		<div class="page-title">
			<h1><?php echo __('Add New Address') ?><a class="f-right account-more visible-xs"><i class="iconfont">&#xe609;</i></a></h1>
		</div>
		<div class="account-menu">
			<ul>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT, '', 'SSL') ?>"><?php echo __('Account') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') ?>"><?php echo __('Account Information') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL') ?>"><?php echo __('Address Book') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') ?>"><?php echo __('My Orders') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_REVIEW, '', 'SSL') ?>"><?php echo __('My Product Reviews') ?></a></li>
				<li><a href="<?php echo href_link(FILENAME_ACCOUNT_NEWSLETTER, '', 'SSL') ?>"><?php echo __('Newsletter Subscriptions') ?></a></li>
			</ul>
		</div>
	</div>
	<div class="my-account-content">
		<?php if ($message_stack->size('address_new') > 0) echo $message_stack->output('address_new'); ?>
		<form id="form-validate" method="post" action="<?php echo href_link(FILENAME_ADDRESS_NEW, '', 'SSL'); ?>">
			<div class="no-display">
				<input type="hidden" value="<?php echo $_SESSION['securityToken']; ?>" name="securityToken" />
				<input type="hidden" value="process" name="action" />
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label class="required" for="address-firstname"><em>*</em><?php echo __('First Name'); ?></label>
					<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($address['firstname'])?$address['firstname']:''; ?>" name="address[firstname]" id="address-firstname" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="address-lastname"><em>*</em><?php echo __('Last Name'); ?></label>
					<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($address['lastname'])?$address['lastname']:''; ?>" name="address[lastname]" id="address-lastname" />
				</div>
				<div class="form-group col-md-6">
					<label for="address-company"><?php echo __('Company'); ?></label>
					<input type="text" class="form-control input-text" value="<?php echo isset($address['company'])?$address['company']:''; ?>" name="address[company]" id="address-company" />
				</div>
				<div class="form-group col-md-12">
					<label class="required" for="address-street_address"><em>*</em><?php echo __('Street Address'); ?></label>
					<input type="text" class="form-control input-text required-entry" minlength="10" value="<?php echo isset($address['street_address'])?$address['street_address']:''; ?>" name="address[street_address]" id="address-street_address" />
				</div>
				<div class="form-group col-md-12">
					<input type="text" class="form-control input-text" value="<?php echo isset($address['suburb'])?$address['suburb']:''; ?>" name="address[suburb]" id="address-suburb" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="address-city"><em>*</em><?php echo __('City'); ?></label>
					<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($address['city'])?$address['city']:''; ?>" name="address[city]" id="address-city" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="address-region"><em id="address-region-em">*</em><?php echo __('State/Province'); ?></label>
					<select class="form-control required-entry" name="address[region_id]" id="address-region_id">
						<option value=""><?php echo __('Please select region, state or province'); ?></option>
					</select>
					<input type="text" class="form-control input-text" value="<?php echo isset($address['region'])?$address['region']:''; ?>" name="address[region]" id="address-region" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="address-postcode"><em>*</em><?php echo __('Zip/Postal Code'); ?></label>
					<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($address['postcode'])?$address['postcode']:''; ?>" name="address[postcode]" id="address-postcode" />
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="address-country_id"><em>*</em><?php echo __('Country'); ?></label>
					<?php $address['country_id'] = isset($address['country_id'])?$address['country_id']:STORE_COUNTRY; ?>
					<?php $_availabCountry = get_countries(); ?>
					<select class="form-control required-entry" onchange="updateRegion('address');" name="address[country_id]" name="address[country_id]" id="address-country_id">
						<option value=""><?php echo __('Please select country'); ?></option>
						<?php foreach ($_availabCountry as $key => $val) { ?>
							<option value="<?php echo $key; ?>"<?php if ($key==$address['country_id']) { ?> selected="selected"<?php } ?>><?php echo $val; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group col-md-6">
					<label class="required" for="address-telephone"><em>*</em><?php echo __('Telephone'); ?></label>
					<input type="text" class="form-control input-text required-entry" minlength="2" value="<?php echo isset($address['telephone'])?$address['telephone']:''; ?>" name="address[telephone]" id="address-telephone" />
				</div>
				<div class="form-group col-md-6">
					<label for="address-fax"><?php echo __('Fax'); ?></label>
					<input type="text" class="form-control input-text" value="<?php echo isset($address['fax'])?$address['fax']:''; ?>" name="address[fax]" id="address-fax" />
				</div>
			</div>
			<div class="checkbox">
				<label for="default_billing">
					<input type="checkbox" class="checkbox" title="<?php echo __('Use as My Default Billing Address'); ?>" value="1" name="default_billing" id="default_billing" />
					<?php echo __('Use as My Default Billing Address'); ?>
				</label>
			</div>
			<div class="checkbox">
				<label for="default_shipping">
					<input type="checkbox" class="checkbox" title="<?php echo __('Use as My Default Shipping Address'); ?>" value="1" name="default_shipping" id="default_shipping" />
					<?php echo __('Use as My Default Shipping Address'); ?>
				</label>
			</div>
			<div class="buttons-set">
				<p class="required">* <?php echo __('Required Fields'); ?></p>
				<p class="back-link"><a href="<?php echo href_link(FILENAME_ADDRESS, '', 'SSL'); ?>"><small>« </small><?php echo __('Back'); ?></a></p>
				<button class="button" title="<?php echo __('Save'); ?>" type="submit"><span><span><?php echo __('Save'); ?></span></span></button>
			</div>
		</form>
	</div>
<script type="text/javascript">
$(function () {
	updateRegion('address', '<?php echo isset($address['region_id'])?$address['region_id']:''; ?>');
	$('#form-validate').validate();
	$('.account-more').click(function(){
		if ($('.account-menu').is(':hidden')) {
			$('.account-menu').slideDown();
			$(this).children("i").html('&#xe643;');
		} else {
			$('.account-menu').slideUp();
			$(this).children("i").html('&#xe609;');
		}
	});
})
</script>
</div>
