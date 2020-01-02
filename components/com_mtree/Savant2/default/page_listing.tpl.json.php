<?php
defined('_JEXEC') or die;

$json_output = $this->link;

// URL to listing
$uri = JUri::getInstance();
$json_output->link = $uri->toString(array( 'scheme', 'host', 'port' )) . JRoute::_('index.php?option=com_mtree&task=viewlink&link_id='.$this->link->link_id, false);
$json_output->images = $this->images;
$json_output->total_reviews = $this->total_reviews;

$i = 0;
foreach( $json_output->images AS $image )
{
    $json_output->images[$i]->image_url = $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_medium_image'] . $image->filename;
    $i++;
}

$i = 0;
$tmp_fields = array();
$visible_field_attributes = array(
    'id',
    'fieldType',
    'caption',
    'value',
    'prefixTextDisplay',
    'prefixTextDisplay',
    'viewAccessLevel',
    'arrayFieldElements',
    'hideCaption',
    'isCore',
    'params',
    'attachment',
    'counter',
    'attachmentUrl'
);

foreach( $this->fields->fields AS $field )
{
    if( $field['attachment'] == 1 ) {
        $objField = $this->fields->getFieldById($field['id']);

        $this->fields->fields[$i]['attachmentUrl'] = str_replace('&amp;','&',$objField->getDataAttachmentURL());
    }

    foreach( $visible_field_attributes AS $attr )
    {
        if( isset($this->fields->fields[$i][$attr]) )
        {
            $tmp_fields[$i][$attr] = $this->fields->fields[$i][$attr];
        }
    }

    $i++;
}

$json_output->fields = $tmp_fields;

echo json_encode( $json_output, JSON_FORCE_OBJECT );
