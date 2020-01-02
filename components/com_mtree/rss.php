<?php
/**
 * @package		Mosets Tree
 * @copyright	(C) 2005-present Mosets Consulting. All rights reserved.
 * @license		GNU General Public License
 * @author		Lee Cher Yeong <mtree@mosets.com>
 * @url			http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

// load feed creator class
require_once( JPATH_COMPONENT.'/includes/feedcreator.php');

function rss( $option, $type, $cat_id=0 ) {
	global $mtconf;

	$database	= JFactory::getDBO();
	
	$info	=	null;
	$rss	=	null;
	$jdate	= JFactory::getDate();
	$now	= $jdate->toSql();
	$nullDate	= $database->getNullDate();

	$link_ids = array();

	$rss = new MTRSSCreator20();
	
	if ($type == 'new') {
		$filename = $mtconf->getjconf('cachepath') . '/mtreeNew' . ($cat_id?'-'.$cat_id:'') . '.xml';
	} else {
		$filename = $mtconf->getjconf('cachepath') . '/mtreeUpdated' . ($cat_id?'-'.$cat_id:'') . '.xml';
	}
	$rss->useCached($filename);
	
	switch($type) {
		case 'updated':
			$rss->title = $mtconf->getjconf('sitename') . $mtconf->get('rss_title_separator') . JText::_( 'COM_MTREE_RECENTLY_UPDATED_LISTINGS' );
			break;
		case 'new':
		default:
			$rss->title = $mtconf->getjconf('sitename') . $mtconf->get('rss_title_separator') . JText::_( 'COM_MTREE_NEW_LISTINGS' );
			break;
	}
	if($cat_id>0) {
		$mtCats = new mtCats($database);
		$cat_name = $mtCats->getName($cat_id);
		$rss->title .= $mtconf->get('rss_title_separator') . $cat_name;
	}
	
	$rss->link = JUri::root();
	$rss->cssStyleSheet	= NULL;
	$rss->feedURL = $mtconf->getjconf('live_site').$_SERVER['PHP_SELF'];

	$database->setQuery("SELECT id FROM #__menu WHERE link='index.php?option=com_mtree&view=home' AND published='1' LIMIT 1");
	$Itemid = $database->loadResult();

	$sql = "SELECT l.*, u.username, u.name AS owner, c.cat_id, c.cat_name FROM (#__mt_links AS l, #__mt_cl AS cl, #__users AS u, #__mt_cats AS c) "
		. "WHERE link_published='1' && link_approved='1' "
		. "\n AND ( publish_up = ".$database->Quote($nullDate)." OR publish_up <= '$now'  ) "
		. "\n AND ( publish_down = ".$database->Quote($nullDate)." OR publish_down >= '$now' ) "
		. "\n AND l.link_id = cl.link_id "
		. "\n AND cl.main = 1 "
		. "\n AND cl.cat_id = c.cat_id "
		. "\n AND l.user_id = u.id ";
	if($cat_id > 0) {
		$subcats = getSubCats_Recursive($cat_id);
		if(count($subcats)>1) {
			$sql .= ' AND cl.cat_id IN (' . implode(',',$subcats) . ')';
		}
	}
	switch($type) {
		case 'updated':
			$sql .= "ORDER BY l.link_modified DESC ";
			break;
		case 'new':
		default:
			$sql .= "ORDER BY l.link_created DESC ";
			break;
	}
	
	if( $mtconf->get('rss_'.$type.'_limit') > 0 )
	{
		$sql .= "LIMIT " . intval($mtconf->get('rss_'.$type.'_limit'));
	}
	
	$database->setQuery( $sql );
	$links = $database->loadObjectList();

	# Get first image of each listings
	if( $mtconf->get('show_image_rss') && !empty($links) )
	{
		foreach( $links AS $link ) {
			$link_ids[] = $link->link_id;
		}
		$database->setQuery( 'SELECT link_id, filename FROM #__mt_images WHERE link_id IN ('.implode(', ',$link_ids).') AND ordering = 1 LIMIT ' . count($link_ids) );
		$link_images = $database->loadObjectList('link_id');
	}

	# Get arrays if link_ids
	foreach( $links AS $link ) {
		$link_ids[] = $link->link_id;
	}

	# Additional elements from custom fields
	$custom_fields = trim($mtconf->get( 'rss_custom_fields' ));
	$array_custom_fields = explode(',',$custom_fields);
	list($custom_fields_values, $additional_elements) = getCustomFieldsValues( $array_custom_fields, $link_ids );

	# Custom field captions
	$custom_fields_captions = getCustomFieldsCaptions($array_custom_fields);

	# Core field captions
	$core_fields_captions = getCoreFieldsCaptions();

	$uri = JUri::getInstance(JUri::base());
	$host = $uri->toString(array('scheme', 'host', 'port'));

	$thumbnail_path = $mtconf->get('relative_path_to_listing_small_image');

	foreach( $links AS $link ) {
		$item = new FeedItem();
		$item->title = $link->link_name;
		$item->link = $host . JRoute::_("index.php?option=com_mtree&task=viewlink&link_id=".$link->link_id."&Itemid=".$Itemid);
		$item->guid = $host . JRoute::_("index.php?option=com_mtree&task=viewlink&link_id=".$link->link_id."&Itemid=".$Itemid);

		$item->description = '';
		if( $mtconf->get('show_image_rss') && isset($link_images[$link->link_id]) && !empty($link_images[$link->link_id]->filename) )
		{
			$item->description .= '<img align="right" src="'.$mtconf->getjconf('live_site').$thumbnail_path.$link_images[$link->link_id]->filename.'" alt="'.$link->link_name.'" />';
		}
		$item->description .= $link->link_desc;

		//optional
		$item->descriptionHtmlSyndicated = true;

		switch($type) {
			case 'updated':
				$item->date = ($link->link_modified ? date('r', strtotime($link->link_modified)) : '');
				break;
			case 'new':
			default:
				$item->date = ($link->link_created ? date('r', strtotime($link->link_created)) : '');
				break;
		}
		$item->source = $mtconf->getjconf('live_site');
		$item->author = $link->username;
		if(count($additional_elements)>0) {
			$ae = array();
			foreach($additional_elements AS $additional_element) {
				$cf_id = str_replace('cust_', '', $additional_element);

				if( in_array($additional_element,$mtconf->core_fields) ) {
					if ($additional_element == 'cat_url') {
						$ae['mtree:'.$additional_element] = htmlspecialchars(JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$link->cat_id.'&Itemid='.$Itemid, true, -1));
						$item->description .= '<br />' . JText::_( 'COM_MTREE_CATEGORY_URL' ) . ': ' . htmlspecialchars(JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$link->cat_id.'&Itemid='.$Itemid, true, -1));
					} else {
						if (!empty($link->$additional_element)) {
							$ae['mtree:'.$additional_element] = '<![CDATA[' . $link->$additional_element . ']]>';
							$item->description .= '<br />';

							$field_name = 'core'.str_replace('link_','',$cf_id);
							if(isset($core_fields_captions[$field_name])) {
								$item->description .= $core_fields_captions[$field_name]->caption;
							}
							$item->description .= ': ';
							$item->description .= $link->$additional_element;
						}
					}
				} else {
					$cf_id = substr( $additional_element, 5 );
					if( array_key_exists($link->link_id,$custom_fields_values) && array_key_exists($cf_id,$custom_fields_values[$link->link_id]) ) {
						$ae['mtree:'.$additional_element] = '<![CDATA[' . str_replace('|',',',$custom_fields_values[$link->link_id][$cf_id]) . ']]>';
						$item->description .= '<br />' . $custom_fields_captions[$cf_id]->caption . ': ' . str_replace('|',',',$custom_fields_values[$link->link_id][$cf_id]);
					}
				}
			}
			$item->additionalElements = $ae;
		}
		$rss->addItem($item);
	}
	echo $rss->saveFeed($filename);
}

function getCustomFieldsValues( $array_custom_fields, $link_ids ) {
	global $mtconf;

	$database	= JFactory::getDBO();

	$custom_fields_values = array();
	$additional_elements = array();

	if( !empty($array_custom_fields) && count($link_ids) > 0 ) {
		foreach( $array_custom_fields AS $key => $value ) {
			if( intval($value) > 0 ) {
				$array_custom_fields[$key] = intval($value);
				$additional_elements[] = 'cust_' . $array_custom_fields[$key];
			} else {
				unset($array_custom_fields[$key]);
			}
		}
		if( count($array_custom_fields) > 0 ) {
			$database->setQuery( 'SELECT cf_id, link_id, value FROM #__mt_cfvalues WHERE cf_id IN (' . implode(',',$array_custom_fields) . ') AND link_id IN (' . implode(',',$link_ids) . ') LIMIT ' . (count($array_custom_fields) * count($link_ids)) );
			$array_custom_fields_values = $database->loadObjectList();
			foreach( $array_custom_fields_values AS $array_custom_fields_value ) {
				$custom_fields_values[$array_custom_fields_value->link_id][$array_custom_fields_value->cf_id] = $array_custom_fields_value->value;
			}
		}
	}

	# Additional elements from core fields
	foreach( $mtconf->core_fields AS $core_field ) {
		if($mtconf->get('rss_'.$core_field)) { $additional_elements[] = $core_field; }
	}

	return array($custom_fields_values, $additional_elements);
}

function getCustomFieldsCaptions($array_custom_fields) {
	$database	= JFactory::getDBO();

	$custom_fields_captions = array();

	$array_custom_fields = array_filter($array_custom_fields);

	if( !empty($array_custom_fields) )
	{
		$database->setQuery( 'SELECT cf_id, caption FROM #__mt_customfields WHERE cf_id IN (' . implode(',',$array_custom_fields) . ') ');
		$custom_fields_captions = $database->loadObjectList('cf_id');
	}

	$custom_fields_captions['cat_name'] = new stdClass();
	$custom_fields_captions['cat_name']->caption = JText::_('COM_MTREE_CATEGORY');
	return $custom_fields_captions;
}

function getCoreFieldsCaptions() {
	$database	= JFactory::getDBO();

	$database->setQuery( 'SELECT cf_id, caption, field_type FROM #__mt_customfields WHERE iscore = 1');
	$core_fields_captions = $database->loadObjectList('field_type');

	return $core_fields_captions;
}

function rssreviews( $option, $type, $link_id=0 ) {
	global $mtconf;

	$database	= JFactory::getDBO();

	$info	=	null;
	$rss	=	null;

	$rss = new MTRSSCreator20();

	$filename = $mtconf->getjconf('cachepath') . '/mtreeListingReviews' . ($link_id?'-'.$link_id:'') . '.xml';
	$rss->useCached($filename);

	$rss->title = $mtconf->getjconf('sitename') . $mtconf->get('rss_title_separator') . JText::_( 'COM_MTREE_REVIEWS' );

	if($link_id>0) {
		$link = new mtLinks( $database );
		$link->load( $link_id );
		$rss->title .= $mtconf->get('rss_title_separator') . $link->link_name;
	}

	$rss->link = JUri::root();
	$rss->cssStyleSheet	= NULL;
	$rss->feedURL = $mtconf->getjconf('live_site').$_SERVER['PHP_SELF'];

	$database->setQuery("SELECT id FROM #__menu WHERE link='index.php?option=com_mtree&view=home' AND published='1' LIMIT 1");
	$Itemid = $database->loadResult();

	# Get reviews
	$sql = "SELECT r.*, u.username, u.name AS owner, log.value AS rating FROM #__mt_reviews AS r"
			.	"\n LEFT JOIN #__users AS u ON u.id = r.user_id"
			.	"\n LEFT JOIN #__mt_log AS log ON log.user_id = r.user_id AND log.link_id = r.link_id AND log_type = 'vote' AND log.rev_id = r.rev_id"
			.	"\n WHERE r.link_id = '".$link_id."' AND r.rev_approved = 1 ";
	if( $mtconf->get('first_review_order1') != '' )
	{
		$sql .= "\n ORDER BY " . $mtconf->get('first_review_order1') . ' ' . $mtconf->get('first_review_order2') ;
		if( $mtconf->get('second_review_order1') != '' )
		{
			$sql .= ', ' . $mtconf->get('second_review_order1') . ' ' . $mtconf->get('second_review_order2');
			if( $mtconf->get('third_review_order1') != '' )
			{
				$sql .= ', ' . $mtconf->get('third_review_order1') . ' ' . $mtconf->get('third_review_order2');
			}
		}
	}
	$sql .= "\n LIMIT 0, " . $mtconf->get('fe_num_of_reviews');
	$database->setQuery( $sql );
	$reviews = $database->loadObjectList();

	# Add <br /> to all new lines
	for( $i=0; $i<count($reviews); $i++ ) {
		$reviews[$i]->rev_text = nl2br(htmlspecialchars(trim($reviews[$i]->rev_text)));
	}

	$uri = JUri::getInstance(JUri::base());
	$host = $uri->toString(array('scheme', 'host', 'port'));

	foreach( $reviews AS $review ) {
		$item = new FeedItem();
		$item->title = $review->rev_title;
		$item->link = $host . JRoute::_("index.php?option=com_mtree&task=viewreview&rev_id=".$review->rev_id."&Itemid=".$Itemid);
		$item->guid = $host . JRoute::_("index.php?option=com_mtree&task=viewreview&rev_id=".$review->rev_id."&Itemid=".$Itemid);

		$item->description = $review->rev_text;

		//optional
		$item->descriptionHtmlSyndicated = true;

		$item->date = ($review->rev_date? date('r', strtotime($review->rev_date)) : '');

		$item->source = $mtconf->getjconf('live_site');
		if( $review->owner ) {
			$item->author = $review->owner;
		} else {
			$item->author = $review->guest_name;
		}

		$rss->addItem($item);
	}
	echo $rss->saveFeed($filename);
}

function rssuserfavourites( $option, $type, $user_id ) {
	global $mtconf;

	if( !is_numeric($user_id) || $user_id <= 0 ) return false;

	$database	= JFactory::getDBO();
	$nullDate	= $database->getNullDate();
	$now	= JFactory::getDate()->toSql();

	$info	=	null;
	$rss	=	null;

	$rss = new MTRSSCreator20();

	$filename = $mtconf->getjconf('cachepath') . '/mtreeUserFavourites' . '-' . $user_id . '.xml';
	$rss->useCached($filename);

	# Get owner's info
	$owner = getOwnerObject($user_id);

	$rss->title = $mtconf->getjconf('sitename') . $mtconf->get('rss_title_separator') . JText::sprintf( 'COM_MTREE_PAGE_TITLE_FAVOURITES_BY', $owner->name );

	$rss->link = JUri::root();
	$rss->cssStyleSheet	= NULL;
	$rss->feedURL = $mtconf->getjconf('live_site').$_SERVER['PHP_SELF'];

	$database->setQuery("SELECT id FROM #__menu WHERE link='index.php?option=com_mtree&view=home' AND published='1' LIMIT 1");
	$Itemid = $database->loadResult();

	# Retrieve Links
	$sql = "SELECT DISTINCT l.*, tlcat.cat_id AS tlcat_id, tlcat.cat_name AS tlcat_name, u.username, cat.*, img.filename AS link_image "
			. "FROM (#__mt_links AS l, #__mt_cl AS cl, #__mt_cats AS cat, #__mt_favourites AS f)"
			. "\n LEFT JOIN #__mt_images AS img ON img.link_id = l.link_id AND img.ordering = 1 "
			. "\n LEFT JOIN #__users AS u ON u.id = l.user_id "
			. "\n LEFT JOIN #__mt_cats AS tlcat ON tlcat.lft <= cat.lft AND tlcat.rgt >= cat.rgt AND tlcat.cat_parent =0 "
			. "\n WHERE link_published='1' AND link_approved='1' AND f.user_id='".$user_id."' AND f.link_id = l.link_id "
			. "\n AND l.link_id = cl.link_id AND cl.main = '1'"
			. "\n AND ( publish_up = ".$database->Quote($nullDate)." OR publish_up <= '$now'  ) "
			. "\n AND ( publish_down = ".$database->Quote($nullDate)." OR publish_down >= '$now' ) "
			. "\n AND cl.cat_id = cat.cat_id ";

	$listings = new Mosets\listings($mtconf);
	$sql .= $listings->getSQLOrderBy();

	$sql .= "\n LIMIT 0, " . $mtconf->get('fe_num_of_rss_favourite') ;
	$database->setQuery( $sql );
	$links = $database->loadObjectList();

	# Get first image of each listings
	if( $mtconf->get('show_image_rss') && !empty($links) )
	{
		foreach( $links AS $link ) {
			$link_ids[] = $link->link_id;
		}
		$database->setQuery( 'SELECT link_id, filename FROM #__mt_images WHERE link_id IN ('.implode(', ',$link_ids).') AND ordering = 1 LIMIT ' . count($link_ids) );
		$link_images = $database->loadObjectList('link_id');
	}

	# Get arrays if link_ids
	foreach( $links AS $link ) {
		$link_ids[] = $link->link_id;
	}

	# Additional elements from custom fields
	$custom_fields = trim($mtconf->get( 'rss_custom_fields' ));
	$array_custom_fields = explode(',',$custom_fields);
	list($custom_fields_values, $additional_elements) = getCustomFieldsValues( $array_custom_fields, $link_ids );

	# Custom field captions
	$custom_fields_captions = getCustomFieldsCaptions($array_custom_fields);

	# Add <br /> to all new lines
	for( $i=0; $i<count($links); $i++ ) {
		$links[$i]->link_desc = nl2br(htmlspecialchars(trim($links[$i]->link_desc)));
	}

	$uri = JUri::getInstance(JUri::base());
	$host = $uri->toString(array('scheme', 'host', 'port'));

	$thumbnail_path = $mtconf->get('relative_path_to_listing_small_image');

	foreach( $links AS $link ) {
		$item = new FeedItem();
		$item->title = $link->link_name;
		$item->link = $host . JRoute::_("index.php?option=com_mtree&task=viewlink&link_id=".$link->link_id."&Itemid=".$Itemid);
		$item->guid = $host . JRoute::_("index.php?option=com_mtree&task=viewlink&link_id=".$link->link_id."&Itemid=".$Itemid);

		$item->description = '';

		if( $mtconf->get('show_image_rss') && isset($link_images[$link->link_id]) && !empty($link_images[$link->link_id]->filename) )
		{
			$item->description .= '<img align="right" src="'.$mtconf->getjconf('live_site').$thumbnail_path.$link_images[$link->link_id]->filename.'" alt="'.$link->link_name.'" />';
		}

		$item->description .= $link->link_desc;

		//optional
		$item->descriptionHtmlSyndicated = true;

		$item->date = ($link->link_created? date('r', strtotime($link->link_created)) : '');

		$item->source = $mtconf->getjconf('live_site');
		$item->author = $link->username;

		if(count($additional_elements)>0) {
			$ae = array();
			foreach($additional_elements AS $additional_element) {
				$cf_id = str_replace('cust_', '', $additional_element);

				if( in_array($additional_element,$mtconf->core_fields) ) {
					if ($additional_element == 'cat_url') {
						$ae['mtree:'.$additional_element] = htmlspecialchars(JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$link->cat_id.'&Itemid='.$Itemid, true, -1));
						$item->description .= '<br />' . JText::_( 'COM_MTREE_CATEGORY_URL' ) . ': ' . htmlspecialchars(JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$link->cat_id.'&Itemid='.$Itemid, true, -1));
					} else {
						$ae['mtree:'.$additional_element] = '<![CDATA[' . $link->$additional_element . ']]>';
						$item->description .= '<br />' . $custom_fields_captions[$cf_id]->caption . ': ' . $link->$additional_element;
					}
				} else {
					$cf_id = substr( $additional_element, 5 );
					if( array_key_exists($link->link_id,$custom_fields_values) && array_key_exists($cf_id,$custom_fields_values[$link->link_id]) ) {
						$ae['mtree:'.$additional_element] = '<![CDATA[' . str_replace('|',',',$custom_fields_values[$link->link_id][$cf_id]) . ']]>';
						$item->description .= '<br />' . $custom_fields_captions[$cf_id]->caption . ': ' . str_replace('|',',',$custom_fields_values[$link->link_id][$cf_id]);
					}
				}
			}
			$item->additionalElements = $ae;
		}

		$rss->addItem($item);
	}
	echo $rss->saveFeed($filename);
}

class MTRSSCreator20 extends RSSCreator091 {

	function __construct() {
		$this->_setRSSVersion("2.0");
		$this->contentType = "application/rss+xml";
	}

	function createFeed() {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<rss version=\"".$this->RSSVersion."\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:mtree=\"http://www.mosets.com/tree/rss/\">\n";		$feed.= "<channel>\n";
		$feed.= "<title>".FeedCreator::iTrunc(htmlspecialchars($this->title),100)."</title>\n";
		$this->descriptionTruncSize = 500;
		$feed.= "<description>".$this->getDescription()."</description>\n";
		$feed.= "<link>".$this->link."</link>\n";
		$now = new FeedDate();
		$feed.= "<lastBuildDate>".htmlspecialchars($now->rfc822())."</lastBuildDate>\n";
		$feed.= "<generator>".FEEDCREATOR_VERSION."</generator>\n";

		if ($this->image!=null) {
			$feed.= "<image>\n";
			$feed.= "	<url>".$this->image->url."</url>\n";
			$feed.= "	<title>".FeedCreator::iTrunc(htmlspecialchars($this->image->title),100)."</title>\n";
			$feed.= "	<link>".$this->image->link."</link>\n";
			if ($this->image->width!="") {
				$feed.= "	<width>".$this->image->width."</width>\n";
			}
			if ($this->image->height!="") {
				$feed.= "	<height>".$this->image->height."</height>\n";
			}
			if ($this->image->description!="") {
				$feed.= "	<description>".$this->image->getDescription()."</description>\n";
			}
			$feed.= "</image>\n";
		}
		if ($this->language!="") {
			$feed.= "<language>".$this->language."</language>\n";
		}
		if ($this->copyright!="") {
			$feed.= "<copyright>".FeedCreator::iTrunc(htmlspecialchars($this->copyright),100)."</copyright>\n";
		}
		if ($this->editor!="") {
			$feed.= "<managingEditor>".FeedCreator::iTrunc(htmlspecialchars($this->editor),100)."</managingEditor>\n";
		}
		if ($this->webmaster!="") {
			$feed.= "<webMaster>".FeedCreator::iTrunc(htmlspecialchars($this->webmaster),100)."</webMaster>\n";
		}
		if ($this->pubDate!="") {
			$pubDate = new FeedDate($this->pubDate);
			$feed.= "<pubDate>".htmlspecialchars($pubDate->rfc822())."</pubDate>\n";
		}
		if ($this->category!="") {
			$feed.= "<category>".htmlspecialchars($this->category)."</category>\n";
		}
		if ($this->docs!="") {
			$feed.= "<docs>".FeedCreator::iTrunc(htmlspecialchars($this->docs),500)."</docs>\n";
		}
		if ($this->ttl!="") {
			$feed.= "<ttl>".htmlspecialchars($this->ttl)."</ttl>\n";
		}
		if (isset( $this->rating_count ) && $this->rating_count > 0) {
			$rating = round( $this->rating_sum / $this->rating_count );
			$feed.= "<rating>".FeedCreator::iTrunc(htmlspecialchars($rating),500)."</rating>\n";
		}
		if ($this->skipHours!="") {
			$feed.= "<skipHours>".htmlspecialchars($this->skipHours)."</skipHours>\n";
		}
		if ($this->skipDays!="") {
			$feed.= "<skipDays>".htmlspecialchars($this->skipDays)."</skipDays>\n";
		}
		$feed.= $this->_createAdditionalElements($this->additionalElements, "	");

		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "<item>\n";
			$feed.= "	<title>".FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title)),100)."</title>\n";
			$feed.= "	<link>".htmlspecialchars($this->items[$i]->link)."</link>\n";
			$feed.= "	<description>".$this->items[$i]->getDescription()."</description>\n";

			if ($this->items[$i]->author!="") {
				$feed.= "	<dc:creator>".htmlspecialchars($this->items[$i]->author)."</dc:creator>\n";
			}
			if ($this->items[$i]->category!="") {
				$feed.= "	<category>".htmlspecialchars($this->items[$i]->category)."</category>\n";
			}
			if ($this->items[$i]->comments!="") {
				$feed.= "	<comments>".htmlspecialchars($this->items[$i]->comments)."</comments>\n";
			}
			if ($this->items[$i]->date!="") {
				$itemDate = new FeedDate($this->items[$i]->date);
				$feed.= "	<pubDate>".htmlspecialchars($itemDate->rfc822())."</pubDate>\n";
			}
			if ($this->items[$i]->guid!="") {
				$feed.= "	<guid>".htmlspecialchars($this->items[$i]->guid)."</guid>\n";
			}
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "	");
			$feed.= "</item>\n";
		}
		$feed.= "</channel>\n";
		$feed.= "</rss>\n";
		return $feed;
	}
}
