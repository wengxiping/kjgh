<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2011-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

require( JPATH_ROOT.'/components/com_mtree/init.module.php');
require_once( JPATH_ADMINISTRATOR.'/components/com_mtree/mfields.class.php' );
require_once( dirname(__FILE__).'/helper.php' );

if( !$moduleHelper->isModuleShown() ) { return; }

$moduleclass_sfx= $params->get( 'moduleclass_sfx' );
$filter_button	= intval( $params->get( 'filter_button', 1 ) );
$reset_button	= intval( $params->get( 'reset_button', 1 ) );
$cat_id		= intval( $params->get( 'cat_id', 0 ) );
$show_keyword_search    = $params->get( 'show_keyword_search', 1 );
$auto_search    = $params->get( 'auto_search', 1 );
$cf_ids		= $params->get( 'fields' );
$itemid		= MTModuleHelper::getItemid();
$intItemid	= str_replace('&Itemid=','',$itemid);

$db 		= JFactory::getDBO();
$document	= JFactory::getDocument();
$post 		= $_REQUEST;
$search_params	= $post;

$keyword_search = '';
if( isset($_REQUEST['keyword']) ) {
	$keyword_search = JFilterInput::getInstance()->clean($_REQUEST['keyword']);
}

$show_avl_search = false;
if( $mtconf->get('show_avl_search') ) {
	$show_avl_search = true;
	includeJavascriptCSSFileDatepick();
}

$avl_date_from = '';
if( isset($_REQUEST['avl_date_from']) ) {
	$avl_date_from = $_REQUEST['avl_date_from'];
}

$avl_date_to = '';
if( isset($_REQUEST['avl_date_to']) ) {
	$avl_date_to = $_REQUEST['avl_date_to'];
}

JHtml::_('jquery.framework');
$document->addScript( ltrim($mtconf->get('relative_path_to_js'),'/') . 'jquery-ui.custom.min.js');
$document->addStylesheet( ltrim($mtconf->get('relative_path_to_js'),'/') . 'jquery-ui.css');

# Load all CORE and custom fields
$db->setQuery( "SELECT cf.*, '0' AS link_id, '' AS value, '0' AS attachment, '".$cat_id."' AS cat_id FROM #__mt_customfields AS cf "
	.	"\nWHERE cf.hidden ='0' AND cf.published='1' && filter_search = '1'"
	.	((!empty($cf_ids))?"\nAND cf.cf_id IN (" . implode(',',$cf_ids). ") ":'')
	.	" ORDER BY ordering ASC" 
	);
$filter_fields = new mFields($db->loadObjectList());
$searchParams = $filter_fields->loadSearchParams($search_params);
$hasSearchParams = true;

require JModuleHelper::getLayoutPath('mod_mt_filter', $params->get('layout', 'default'));
