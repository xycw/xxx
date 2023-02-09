<script language="javascript" type="text/javascript"><!--
function updateRegion(prefix, current_region_id) {
var country_id = $("#" + prefix + "-country_id");
var region_id = $("#" + prefix + "-region_id");
var region = $("#" + prefix + "-region");
var em = $("#" + prefix + "-region-em");
<?php $js_region_countries = get_region_countries(); ?>
<?php $i = 1; ?>
<?php foreach ($js_region_countries as $js_country_id => $js_region_country) { ?>
<?php if ($i==1) { ?>
	if (country_id.val() == <?php echo $js_country_id; ?>) {
<?php } else { ?>
	} else if (country_id.val() == <?php echo $js_country_id; ?>) {
<?php } ?>
		region_id.empty();
		region_id.append('<option value=""><?php echo addslashes(__('Please select region, state or province')); ?></option>');
<?php foreach ($js_region_country as $js_region) { ?>
		region_id.append('<option value="<?php echo $js_region['region_id']; ?>"><?php echo $js_region['name']; ?></option>');
<?php } ?>
<?php $i++; ?>
		em.html('*');
		region.val('');
		region_id.show();
		region_id.val(current_region_id);
		region.hide();
<?php } ?>
	} else {
		em.html('');
		region_id.empty();
		region_id.hide();
		region_id.append('<option value=""><?php echo addslashes(__('Please select region, state or province')); ?></option>');
		region.show();
	}
}
--></script>
