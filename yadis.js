jQuery(document).ready(function() {

	jQuery('#new_provider').change(function() {
		var value = jQuery(this).val();
		if (value == '') {
			jQuery('#xrds_predefined_service').hide();
			jQuery('#xrds_custom_service').hide();
		}
		else if (value == 'other') {
			jQuery('#xrds_predefined_service').hide();
			jQuery('#xrds_custom_service').show();
		}
		else {
			jQuery('#xrds_predefined_service').show();
			jQuery('#xrds_custom_service').hide();
		}

	});

	jQuery('#yadis_services').Sortable({
		accept : 'service',
		helperclass : 'sortHelper',
		opacity : 0.8,
		fx : 200,
		axis : 'vertically',
		opacity : 0.6,
		revert : true,
	});


	jQuery('#yadis_form').submit(function() {
		var services = jQuery.SortSerialize().hash;
		jQuery('#services_order').val(services);
		return true;
	});

	jQuery('#xrds_predefined_service').hide();
	jQuery('#xrds_custom_service').hide();
});
