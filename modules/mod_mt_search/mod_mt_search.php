<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2005-2017 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

require( JPATH_ROOT.'/components/com_mtree/init.module.php');
require_once( dirname(__FILE__).'/helper.php' );

global $mtconf;

if( !$moduleHelper->isModuleShown() ) { return; }

$moduleclass_sfx= $params->get( 'moduleclass_sfx' );
$placeholder_text	= $params->get( 'placeholder_text', JText::_('MOD_MT_SEARCH_SEARCH_ELLIPSIS') );
$advsearch 	= intval( $params->get( 'advsearch', 1 ) );
$search_button	= intval( $params->get( 'search_button', 1 ) );
$dropdownWidth	= intval( $params->get( 'dropdownWidth', 0 ) );
$parent_cat_id	= intval( $params->get( 'parent_cat', 0 ) );
$searchCategory	= intval( $params->get( 'searchCategory', 0 ) );

$itemid		= MTModuleHelper::getItemid();
$categories	= modMTSearchHelper::getCategories( $params );

$searchword	= urldecode(JFactory::getApplication()->input->getString('searchword'));
$cat_id		= JFactory::getApplication()->input->getInt('cat_id');

$useSearchCompletion = $params->get( 'useSearchCompletion', '1' );
$searchCompletionSearchCategory = $params->get( 'searchCompletionSearchCategory', '1' );
$searchCompletionShowImage = $params->get( 'searchCompletionShowImage', '1' );
$maxListings = $mtconf->get('search_completion_max_listings');

$selected_cat_id = $parent_cat_id;
if( $cat_id > 0 ) {
	$selected_cat_id = $cat_id;
}

$lists = array();
if( $categories ) {
	$all_category = new stdClass();
	$all_category->cat_id = $parent_cat_id;
	$all_category->cat_name = JText::_( 'MOD_MT_SEARCH_ALL_CATEGORIES' );
	array_unshift( $categories, $all_category);
	$lists['categories'] = JHtml::_('select.genericlist', $categories, 'cat_id', '' . (($dropdownWidth>0) ? ' style="width:'.$dropdownWidth.'px;"' : ''), 'cat_id', 'cat_name', $selected_cat_id );
} else {
	$lists['categories'] = null;
}

if( $advsearch ) {
	$cat_link = '';
	if( $parent_cat_id >= 0 ) {
		$cat_link = '&cat_id=' . $parent_cat_id;
	}
	$advsearch_link = JRoute::_( 'index.php?option=com_mtree&task=advsearch' . $itemid . $cat_link);
}

if( $useSearchCompletion )
{
	$sourceUrlListing = JURI::root() . '?option=com_mtree&task=search.completion&format=json&cat_id=' . $parent_cat_id . $itemid . '&type=listing';
	$sourceUrlCategory = JURI::root() . '?option=com_mtree&task=search.completion&format=json&cat_id=' . $parent_cat_id . $itemid . '&type=category';
	JFactory::getDocument()->addStyleSheet( ltrim($mtconf->get('relative_path_to_js'),'/') . 'jquery.typeahead.css');
	JFactory::getDocument()->addScript( ltrim($mtconf->get('relative_path_to_js'),'/') . 'jquery.typeahead.min.js');
}

$searchFormId = 'mod_mt_search' . $module->id;
$searchInputId = 'mod_mt_search_searchword' . $module->id;

require JModuleHelper::getLayoutPath('mod_mt_search', $params->get('layout', 'default'));
