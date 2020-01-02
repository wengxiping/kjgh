<?php
defined('_JEXEC') or die;

$json_output = new stdClass();
$json_output->cat_id = $this->cat_id;
$json_output->task = $this->task;

if( isset($this->pageNav) )
{
	$json_output->page_nav = $this->pageNav;
}

// Create a custom response for owners
$tmp_owners = $this->owners;
$i = 0;
foreach( $this->owners AS $owner )
{
	$uri = JUri::getInstance();

	$tmp_owners[$i]->link = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=viewowner&user_id='.$owner->id, false);
	$tmp_owners[$i]->listingsUrl = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=viewuserslisting&user_id='.$owner->id, false);
	$tmp_owners[$i]->reviewsUrl = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=viewusersreview&user_id='.$owner->id, false);

	unset($tmp_owners[$i]->url);

	$i++;

}

$json_output->owners = $tmp_owners;

// Output the JSON data
echo json_encode( $json_output, JSON_FORCE_OBJECT );
