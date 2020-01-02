<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_listings extends mFieldType
{
	var $numOfSearchFields = 0;

	var $acceptMultipleValues = true;

	protected $db = null;

	protected $nullDate = null;

	protected $now = null;

	/**
	 * Set whether to show unpublished listings when showing the select input field. Otherwise, only published listings
	 * will be made available for selection.
	 *
	 * @var bool
	 */
	protected $hide_unpublished_listings_on_input = true;

	/**
	 * Set whether to show unpublished listings during output. Otherwise, only published listings will be shown in
	 * output.
	 *
	 * @var bool
	 */
	protected $show_unpublished_listings_on_output = false;

	/**
	 * Set whether to show listing image on output.
	 *
	 * @var bool
	 */
	protected $show_image_on_output = true;

	/**
	 * Image's output width in px.
	 *
	 * @var bool
	 */
	protected $image_output_width= 80;

	/**
	 * Image's output height in px.
	 *
	 * @var bool
	 */
	protected $image_output_height = 80;

	/**
	 * The size of input element in px.
	 *
	 * @var int
	 */
	protected $input_html_size_in_px = 370;

	public function __construct( $data=array() )
	{
		parent::__construct($data);

		$this->db = JFactory::getDBO();
		$this->nullDate	= $this->db->getNullDate();
		$this->now = JFactory::getDate()->toSql();

		$this->show_image_on_output = $this->getParam( 'showImage', 1 );
		$this->image_output_width = $this->getParam( 'imageOutputWidth', 80 );
		$this->image_output_height = $this->getParam( 'imageOutputHeight', 80 );
		$this->input_html_size_in_px = $this->getParam( 'inputHtmlSizeInPx', 370 );

	}

	function getInputHTML()
	{
		JHtml::_('formbehavior.chosen', '#' . $this->getInputFieldID(1) );

		$cat_id = $this->getParam( 'category' );
		$placeholder = $this->getParam( 'placeholder', 'Select listings' );

		$links = $this->getAllListings($cat_id);

		$selected_listings = $this->getInputValue();

		$html = '';

		$html .= '<select'.($this->isRequired() ? ' required':'').' name="' . $this->getInputFieldName(1) . '[]" id="' . $this->getInputFieldID(1) . '"';
		$html .= ' multiple';
		$html .= ' style="width:' . $this->input_html_size_in_px . 'px"';
		$html .= ' data-placeholder="' . $placeholder . '"';
		$html .= '>';

		foreach($links AS $link) {
			$html .= '<option value="'.htmlspecialchars($link->link_id).'"';
			if(
			(
				!empty($selected_listings)
				&&
				in_array($link->link_id,$selected_listings)
			)
			) {
				$html .= ' selected';
			}
			$html .= '>' . $link->link_name . '</option>';
		}
		$html .= '</select>';

		return $html;
	}
	
	/**
	* Return the formatted output
	* @param integer $view Type of output to return. Especially useful when you need to display expanded
	*		 information in detailed view and use can use this display a summarized version
	*		 for summary view. $view = 1 for Normal/Details View. $view = 2 for Summary View.
	* @return string The formatted value of the field
	*/
	function getOutput($view=1) {
		global $mtconf, $Itemid;

		$arrayValue = $this->getListingIdsValueAsArray();

		if( empty($arrayValue) ) {
			return null;
		}

		$listings = $this->getSelectedListings($arrayValue);

		$fields = $this->getFields($listings);

		$displayfields = explode(',',$this->getParam( 'fields' ));

		$html = '';

		$html .= '<ul class="listings">';
		foreach( $listings AS $listing ) {
			$html .= '<li>';

			$url = JRoute::_('index.php?option=com_mtree&task=viewlink&link_id=' . $listing->link_id . '&Itemid=' . $Itemid);

			// Listing image
			if( $this->show_image_on_output )
			{
				$html .= '<a class="mtImage" href="' . $url . '">';
				if( $listing->image ) {
					$html .= '<img src="';
					$html .= $mtconf->getjconf('live_site').$mtconf->get('relative_path_to_listing_small_image') . $listing->image . '"';
					$html .= ' alt="' . $listing->link_name . '"';
					$html .= ' width="' . $this->image_output_width . '"';
					$html .= ' height="' . $this->image_output_width . '"';
					$html .= '/>';
				} else {
					$html .= '<img src="';
					$html .= $mtconf->getjconf('live_site').$mtconf->get('relative_path_to_images') . 'noimage_thb.png"';
					$html .= ' alt="' . $listing->link_name . '"';
					$html .= ' width="' . $this->image_output_width . '"';
					$html .= ' height="' . $this->image_output_width . '"';
					$html .= '/>';

				}
				$html .= '</a>';
			}

			$html .= '<ul class="fields">';

			// Listing name
			$html .= '<a class="mtListingName" href="' . $url . '">';
			$html .= $listing->link_name;
			$html .= '</a>';

			// Listing fields
		 	$fields[$listing->link_id]->resetPointer();
			if( !empty($displayfields) )
			{
				while( $fields[$listing->link_id]->hasNext() )
				{
					$field = $fields[$listing->link_id]->getField();

					if( in_array($field->getId(),$displayfields) && $field->hasValue() )
					{
						$html .=  '<li>';
						$html .=  '<span class="' . $this->getFieldTypeClassName() . '_caption">';
						$html .=  $field->getCaption();
						$html .=  '</span>';
						$html .=  ': ';

						$html .=  '<span class="' . $this->getFieldTypeClassName() . '_output">';
						$value = $field->getOutput(2);
						$html .=  $value;
						$html .=  '</span>';
						$html .=  '</li>';
					}
					$fields[$listing->link_id]->next();
				}
			}
			$html .= '</ul>';

			$html .= '</li>';

		}

		$html .= '</ul>';
		return $html;
	}

	/**
	 * Return a validated value in an array. It trims and remove empty values.
	 *
	 * @return array
	 */
	protected function getListingIdsValueAsArray()
	{
		$arr = explode('|',$this->value);

		// Remove empty values
		$arr = array_filter($arr);

		// Trim
		array_walk($arr, 'trim');

		return $arr;

	}

	function hasValue()
	{
		$arrValue = $this->getListingIdsValueAsArray();

		return (!empty($arrValue)) ? true: false;
	}

	protected function getAllListings($cat_id=0)
	{
		// If the root is where the query is showing from, skip this and let it query without category limitation
		$only_subcats_sql = '';

		if( $cat_id > 0 )
		{
			$mtCats = new mtCats( $this->db );
			$subcats = $mtCats->getSubCats_Recursive( $cat_id );

			if ( count($subcats) > 0 ) {
				$only_subcats_sql = "\n AND cl.cat_id IN (" . implode( ", ", $subcats ) . ")";
			}

		}

		$sql = "SELECT l.link_id, l.link_name FROM (#__mt_links AS l, #__mt_cl AS cl) ";

		if( $this->hide_unpublished_listings_on_input ) {
			$sql .= "\n WHERE link_published='1' && link_approved='1' "
				. "\n AND ( publish_up = ".$this->db->Quote($this->nullDate)." OR publish_up <= '$this->now'  ) "
				. "\n AND ( publish_down = ".$this->db->Quote($this->nullDate)." OR publish_down >= '$this->now' ) "
				. "\n AND ";
		} else {
			$sql .= "\n WHERE ";
		}
		$sql .= "\n cl.main = 1 ";
		$sql .= "\n AND l.link_id = cl.link_id ";
		$sql .= ( (!empty($only_subcats_sql)) ? $only_subcats_sql : '' );

		$sql .= "\n ORDER BY l.link_name ASC";

		$this->db->setQuery( $sql );

		return $this->db->loadObjectList();
	}

	protected function getSelectedListings($listing_ids)
	{
		if(empty($listing_ids)) {
			return array();
		}

		$sql = 	"SELECT l.*, tlcat.cat_id AS tlcat_id, tlcat.cat_name AS tlcat_name, cat.*, u.username AS username, u.name AS owner, u.email AS owner_email";
		$sql .= ( ($this->show_image_on_output) ? ', img.filename AS image' : '' );

		$sql .= "\n FROM (#__mt_links AS l, #__mt_cl AS cl, #__mt_cats AS cat)";
		$sql .= ( ($this->show_image_on_output) ? "\n LEFT JOIN #__mt_images AS img ON img.link_id = l.link_id AND img.ordering = 1" : '' );
		$sql .= "\n LEFT JOIN #__mt_cats AS tlcat ON tlcat.lft <= cat.lft AND tlcat.rgt >= cat.rgt AND tlcat.cat_parent =0 ";
		$sql .= "\n LEFT JOIN #__users AS u ON u.id = l.user_id";

		$sql .= "\n WHERE l.link_id IN (" . implode(",", $listing_ids). ")";

		if( !$this->show_unpublished_listings_on_output ) {
			$sql .= "\n AND link_published='1' && link_approved='1' "
				. "\n AND ( publish_up = ".$this->db->Quote($this->nullDate)." OR publish_up <= '$this->now'  ) "
				. "\n AND ( publish_down = ".$this->db->Quote($this->nullDate)." OR publish_down >= '$this->now' ) ";
		}
		$sql .= "\n AND l.link_id = cl.link_id "
			. "\n AND cat.cat_id = cl.cat_id "
			. "\n AND cl.main = 1 ";

		$sql .= "\n ORDER BY l.link_name ASC";

		$this->db->setQuery( $sql );

		$listings = $this->db->loadObjectList();

		return $listings;
	}

	public static function getFields( $listings )
	{
		if( !empty($listings) )
		{
			$mfields = array();
			foreach( $listings AS $l )
			{
				$mfields[$l->link_id] = loadFields( $l, 0 );

			}
			return $mfields;
		}
		return false;
	}

}
