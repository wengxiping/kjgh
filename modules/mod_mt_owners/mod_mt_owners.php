<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

require( JPATH_ROOT.'/components/com_mtree/init.module.php');

if( !$moduleHelper->isModuleShown() ) { return; }

require_once( JPATH_ROOT.'/components/com_mtree/Savant2.php');
require_once JPATH_ROOT . '/components/com_mtree/controllers/owner.php';

// Get params
$moduleclass_sfx	    = $params->get( 'moduleclass_sfx' );
$type			        = $params->get( 'type'                      , 1 ); // Default is: Most Listings
$count			        = $params->get( 'count'                     , 5 );
$show_from_cat_id	    = $params->get( 'show_from_cat_id'          , 0 );
$show_name		        = $params->get( 'show_name'                 , 1 );
$show_rel_data		    = $params->get( 'show_rel_data'             , 1 );
$show_images		    = $params->get( 'show_images'               , 1 );
$show_more		        = $params->get( 'show_more'                 , 1 );
$caption_showmore	    = $params->get( 'caption_showmore'          , 'Show more...' );
$dropdown_select_text	= $params->get( 'dropdown_select_text'      , JText::_( 'MOD_MT_OWNERS_FIELD_DROPDOWN_SELECT_TEXT_DEFAULT_VALUE' ) );
$dropdown_width		    = $params->get( 'dropdown_width'            , 200 );
$tiles_flow		        = $params->get( 'tiles_flow'                , 'horizontal' );
$name_and_data_alignment= $params->get( 'name_and_data_alignment'   , 'left' );
$image_size		        = $params->get( 'image_size'                , '50px' );
$tile_width		        = $params->get( 'tile_width'                , '' );

// Determine tile's width if not explicitly given
if( empty($tile_width) )
{
	switch($tiles_flow)
	{
		default:
		case 'horizontal':
			$tile_width = '50%';
			break;
		case 'vertical':
			$tile_width = '100%';
			break;
	}
}

$owner = new Mosets\owner();
$owner->limitToCategory($show_from_cat_id);
$owner->setLimit($count);

if( $type == '2' )
{
	$owner->setOrderByMostReviews();
}
else
{
	$owner->setOrderByMostListings();
}

$owners = $owner->getListingOwners();

$show_reviews = false;
$show_listings = false;

if( $show_rel_data )
{
	if( $show_from_cat_id == 0 ) {
		$show_reviews = true;
		$show_listings = true;
	} else {
		if( $owner->isOrderedByListings() )
		{
			$show_reviews = false;
			$show_listings = true;
		} else {
			$show_reviews = true;
			$show_listings = false;
		}
	}
}

$show_more_link = '';
if( $show_more )
{
	$show_more_link = JRoute::_( 'index.php?option=com_mtree&task=owner&' . (($show_from_cat_id) ? 'cat_id='.$show_from_cat_id : '' ));
}

// Provides unique ID to module for custom styling.
$uniqid = uniqid('mod_mt_owners');

require JModuleHelper::getLayoutPath('mod_mt_owners', $params->get('layout', 'default'));