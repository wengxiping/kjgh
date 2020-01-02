<?php
$selectedTemplatePath = realpath(JPATH_ROOT . $this->config->get('relative_path_to_templates') . $this->config->get('template'));
$selectedTemplateSetupFilePath = $selectedTemplatePath . '/' . basename(__FILE__) ;

// Checks if the current setup.php file that is being used is from the active
// template. If it's not, load the version from active template.
if(
		($selectedTemplatePath != dirname(__FILE__))
		&&
		file_exists($selectedTemplateSetupFilePath)
) {

	require $selectedTemplateSetupFilePath;
	return;
}


// Enter the custom fields ID to skip.
// These fields will not be shown in the list of custom fields that typically
// appears at the bottom of listing summary.
$skipped_field_ids = array(1,2);

$config = require 'config.php';

if( $this->task == 'viewlink' ) {

	// Details View Main Attribute Fields
	$strDetailsViewMainAttributeFields = $this->config->getTemParam('mainAttributeFieldsInDetailsView','');
	$config['details_view']['main_attr_fields'] = array();

	if( !empty($strDetailsViewMainAttributeFields) ) {
		$config['details_view']['main_attr_fields'] = explode(',',$strDetailsViewMainAttributeFields);
	}

	$skipped_field_ids = array_merge(
			$skipped_field_ids,
			$config['details_view']['main_attr_fields']
	);

} else {
	// Summary View Main Attribute Fields
	$strMainAttributeFields = $this->config->getTemParam('mainAttributeFields','');

	$config['summary_view']['main_attr_fields'] = array();
	if( !empty($strMainAttributeFields) ) {
		$config['summary_view']['main_attr_fields'] = explode(',',$strMainAttributeFields);
	}

	// Skip Website (12) field in summary view because all summary views have
	// a dedicated are that shows Website URL.
	$skipped_field_ids[] = 12;

	$skipped_field_ids[] = $config['summary_view']['focus_field_1'];
	$skipped_field_ids[] = $config['summary_view']['focus_field_2'];

	$skipped_field_ids = array_merge(
			$skipped_field_ids,
			$config['summary_view']['main_attr_fields']
	);

	// Should we show the Website field?
	$show_website = true;
	if(
		$config['summary_view']['focus_field_1'] == '12'
		||
		$config['summary_view']['focus_field_2'] == '12'
		||
		in_array(12, $config['summary_view']['main_attr_fields'])
	) {
		$show_website = !$show_website;
	}
}

$hidden_ls_fields = $config['summary_view']['hide_fields'];

if( isset($hidden_ls_fields) && !empty($hidden_ls_fields) ) {
	$skipped_field_ids = array_merge(
			$skipped_field_ids,
			$hidden_ls_fields
	);
}

if( $this->config->get('show_rating') ) {
	$skipped_field_ids[] = 16;
}