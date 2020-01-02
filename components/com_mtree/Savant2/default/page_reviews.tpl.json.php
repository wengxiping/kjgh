<?php
defined('_JEXEC') or die;

$json_output = new stdClass();

if( isset($this->link) )
{
	$json_output = $this->link;
}

if( isset($this->reviewsNav) )
{
	$json_output->page_nav = $this->reviewsNav;
}

$json_output->reviews = $this->reviews;

// Output the JSON data
echo json_encode( $json_output, JSON_FORCE_OBJECT );

