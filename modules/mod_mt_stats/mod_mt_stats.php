<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2005-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

require( JPATH_ROOT.'/components/com_mtree/init.module.php');
require_once( dirname(__FILE__).'/helper.php' );

if( !$moduleHelper->isModuleShown() ) { return; }

# Get params
$moduleclass_sfx 	    = $params->get( 'moduleclass_sfx' );
$cache			        = $params->get( 'cache', 1 );

// Items to URL task map
$url_maps = array(
	'listings' => 'listall',
	'categories' => 'listallcats',
	'owners' => 'owner'
);

// What to show?
$show = array('listings', 'categories', 'owners');
$show_count = 0;
$arguments_for_jtext_sprintf = array();

$cache = JFactory::getCache('mod_mt_stats');

foreach($show AS $show_item)
{
	// Get the config to see if we should show the item.
	$show_num_of[$show_item] = $params->get( 'show_num_of_'.$show_item, 1 );

	// Keep track of the number of items to show
	if($show_num_of[$show_item] == 1) {
		// Let's get the total of the item
		$total[$show_item] = $cache->call(array('modMTStatsHelper','getTotal' . ucfirst($show_item)));

		// URL to view all items
		$urls[$show_item] = JRoute::_( 'index.php?option=com_mtree&task=' . $url_maps[$show_item] );
		$arguments_for_jtext_sprintf[] = JText::sprintf('MOD_MT_STATS_ITEM_'.strtoupper($show_item), $total[$show_item], $urls[$show_item]);

		$show_count++;
	}

}

// Stores the actual text to be displayed.
$text = '';

if( $show_count > 0 )
{
	array_unshift($arguments_for_jtext_sprintf, 'MOD_MT_STATS_TEXT_' . $show_count);

	$jtext = new \ReflectionMethod( "JText", "sprintf" );
	$text = $jtext->invokeArgs(new JText(), $arguments_for_jtext_sprintf);
}

require JModuleHelper::getLayoutPath('mod_mt_stats', $params->get('layout', 'default'));