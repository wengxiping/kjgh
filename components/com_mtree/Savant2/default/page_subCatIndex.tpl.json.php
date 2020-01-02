<?php
defined('_JEXEC') or die;

$uri = JUri::getInstance();

$json_output = new stdClass();
$json_output->task = $this->task;

if( isset($this->cat) )
{
	$json_output = $this->cat;
} elseif( isset($this->cat_id) )
{
    $json_output->cat_id = $this->cat_id;
}

if( isset($this->header) )
{
    $json_output->header= $this->header;
}

if( isset($this->pageNav) )
{
	$json_output->page_nav = $this->pageNav;
}

// List Listing.
// We assume that this is a Top Listing page if $this->lists['sort'] is set.
if( isset($this->lists['sort']) )
{
	$json_output->page = new stdClass();

	$sort_options = $this->mtconf['all_listings_sort_by_options'];
	if( !is_array($sort_options) ) {
		$sort_options = explode('|',$sort_options);
	}

	if( $this->task == 'listall' )
	{
		$json_output->page->active_sort = $this->sort;
		if( empty($this->sort) )
		{
			$json_output->page->active_sort = $this->mtconf['all_listings_sort_by'];
		}

		$json_output->page->sort_options = $sort_options;
	}

	$json_output->link = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=listall&cat_id='.$this->cat->cat_id.'&sort='.$this->sort, false);
}

// Search Results
if( isset($this->searchword) )
{
	$json_output->searchword = $this->searchword;
	$json_output->link = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=search&cat_id='.$this->cat_id.'&searchword='.$this->searchword, false);
}

// Custom Fields caption, used in 'searchby' page
if( isset($this->customfieldcaption) )
{
    $json_output->customfieldcaption = $this->customfieldcaption;
}

// Web link to category
if( $this->task == 'listcats' )
{
	$json_output->link = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$this->cat->cat_id, false);
}

if( isset($this->categories) )
{
	$tmp_categories = $this->categories;
	$i = 0;
	foreach ($this->categories as $cat)
	{
		if (!in_array($cat->cat_id, $this->authorised_cat_ids) )
		{
			continue;
		}

		$tmp_categories[$i] = $cat;
		$i++;
	}

	$json_output->categories = $tmp_categories;
}

// Create a custom response for listings
$tmp_listings = $this->links;
$i = 0;
foreach( $this->links AS $link )
{
	$uri = JUri::getInstance();

	$tmp_listings[$i]->link = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=viewlink&link_id='.$link->link_id, false);
	$tmp_listings[$i]->total_reviews = $this->reviews_count[$link->link_id]->total;
    if( !isset($tmp_listings[$i]->favourites) ) {
        $tmp_listings[$i]->favourites = 0;
    }

	$tmp_listings[$i]->link_image_url = '';
	if( !empty($tmp_listings[$i]->link_image) ) {
		$tmp_listings[$i]->link_image_url = $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_medium_image'] . $tmp_listings[$i]->link_image;
        $tmp_listings[$i]->link_image_url_small = $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_small_image'] . $tmp_listings[$i]->link_image;
	}


	foreach( $this->links_fields[$link->link_id]->fields AS $field)
	{
		$tmp_listings[$i]->link_fields[] = array(
			'id'                => $field['id'],
			'fieldType'         => $field['fieldType'],
			'caption'           => $field['caption'],
			'value'             => $field['value'],
			'prefixTextDisplay' => $field['prefixTextDisplay'],
			'suffixTextDisplay' => $field['suffixTextDisplay'],
			'detailsView'       => $field['detailsView'],
			'summaryView'       => $field['summaryView']
		);

	}
	$i++;
}

$json_output->listings = $tmp_listings;

// Output the JSON data
echo json_encode( $json_output, JSON_FORCE_OBJECT );