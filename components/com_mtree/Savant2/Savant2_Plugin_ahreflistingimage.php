<?php
defined('_JEXEC') or die('Restricted access');

/**
* Base plugin class.
*/
require_once JPATH_ROOT.'/components/com_mtree/Savant2/Plugin.php';

/**
* Mosets Tree 
*
* @package Mosets Tree 0.8
* @copyright (C) 2004 Lee Cher Yeong
* @url http://www.mosets.com/
* @author Lee Cher Yeong <mtree@mosets.com>
**/

class Savant2_Plugin_ahreflistingimage extends Savant2_Plugin {

	/**
	* 
	* Output an HTML <a href="">...</a> with optional 'Popular', 'Featured', 'New' text.
	* 
	* @access public
	* 
	* @param int $link_id Listing's ID. To be used in the URL
	*
	* @param object $link Reference to link object.
	* 
	* @return string The <a href="">...</a> tag.
	* 
	*/
	
	function plugin()
	{
		global $Itemid, $mtconf;

		list($link, $attr, $size, $images, $use_slider) = array_merge(func_get_args(), array(null));

		if( !isset($size) ) {
			$size = 'small';
		}
		$path_to_image = 'relative_path_to_listing_' . $size . '_image';

		$html = '';

		// Does this listing have image? If it has image, show it, either by showing the first image or all of them as
		// slider.
		if( isset($link->link_image) && !empty($link->link_image) ) {
			if( !$use_slider || count($images) == 1 ) {

				$html .= '<a href="';
				$html .= JRoute::_('index.php?option=com_mtree&task=viewlink&link_id='.$link->link_id.'&Itemid='.$Itemid);
				$html .= '"';

				# set the listing text, close the tag
				$html .= '><img border="0" src="' . $mtconf->getjconf('live_site').$mtconf->get($path_to_image).$link->link_image . '"';

				if (substr(PHP_OS, 0, 3) != 'WIN') {
					$listingimage_info = @getimagesize($mtconf->getjconf('absolute_path').$mtconf->get($path_to_image).$link->link_image);
					if($listingimage_info !== false && !empty($listingimage_info[0]) && $listingimage_info[0] > 0 && !empty($listingimage_info[1]) && $listingimage_info[1] >0) {
						$html .= ' width="'.$listingimage_info[0].'" height="'.$listingimage_info[1].'"';
					}
				}

				# Insert attributes
				if (is_array($attr)) {
					// from array
					foreach ($attr as $key => $val) {
						$key = htmlspecialchars($key);
						$val = htmlspecialchars($val);
						$html .= " $key=\"$val\"";
					}
				} elseif (! is_null($attr)) {
					// from scalar
					$html .= " $attr";
				}

				$html .= ' /></a> ';

			} else {

				$html .= '<div class="flexslider">';
				$html .= '<ul class="slides">';

				foreach($images AS $image) {
					$html .= '<li>';
					$html .= '<a href="' . JRoute::_('index.php?option=com_mtree&task=viewlink&link_id=' . $link->link_id . '&Itemid=' . $Itemid) . '">';
					$html .= '<img ';
					$html .= 'src="' . $mtconf->getjconf('live_site').$mtconf->get('relative_path_to_listing_medium_image') . $image . '" ';
					$html .= 'class="image' . (($mtconf->getTemParam('imageDirectionListingSummary', 'left') == 'right') ? '' : '-left') . '" ';
					$html .= '>';
					$html .= '</a>';
					$html .= '</li>';
				}

				$html .= '</ul>';
				$html .= '</div>';
			}
		} else if( $mtconf->getTemParam('showFillerImage',1) ) {

			// If listing has no image, check if the template is configured to show filler image. If it is, return the
			// filler image here.
			if( $link->link_approved )
			{
				$html .= '<a href="' . JRoute::_('index.php?option=com_mtree&task=viewlink&link_id=' . $link->link_id . '&Itemid=' . $Itemid) . '">';
			}

			$html .= '<img ';
			$html .= 'src="' . $mtconf->getjconf('live_site') . $mtconf->get('relative_path_to_images') . 'noimage_thb.png" ';
			$html .= 'width="' . $mtconf->get('resize_small_listing_size') . '" ';
			$html .= 'height="' . $mtconf->get('resize_small_listing_size') . '" ';
			$html .= 'class="image' . (($mtconf->getTemParam('imageDirectionListingSummary', 'left') == 'right') ? '' : '-left') . '" ';
			$html .= 'alt="" ';
			$html .= '>';

			if( $link->link_approved )
			{
				$html .= '</a>';
			}
		}

		# Return the listing link
		return $html;
	}
}