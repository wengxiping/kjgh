<?php
namespace Mosets;

defined('_JEXEC') or die;

use \JFactory;
use \JRoute;
use \JFilterOutput;
use \JPagination;

class searchby
{
	protected $mtconf = null;

	protected $db = null;

	protected $nowDate = null;

	protected $nullDate = null;

	protected $limitstart = null;

	public $cf_id = null;

	protected $value = null;
	
	protected $customfield = null;

	protected $fieldValueSeparator = null;

	protected $search_cat = null;

	protected $tlcat_id = null;

	protected $mtCat = null;

	protected $totalTagsForField = null;

	function __construct()
	{
		global $mtconf;

		$this->mtconf = $mtconf;
		$this->db = JFactory::getDBO();
		$this->nowDate	= JFactory::getDate()->toSql();
		$this->nullDate	= $this->db->getNullDate();

		$this->limitstart	= JFactory::getApplication()->input->getInt('limitstart', 0);
		$this->search_cat	= JFactory::getApplication()->input->getInt('cat_id', 0);

		$this->value 		= JFactory::getApplication()->input->getString( 'value', '' );
		$this->cf_id 		= JFactory::getApplication()->input->getInt( 'cf_id', '' );

		if( $this->limitstart < 0 ) $this->limitstart = 0;

		if ( $this->search_cat > 0 ) {
			$this->mtCat = new \mtCats( $this->db );
			$this->mtCat->load($this->search_cat);
		}

		$this->tlcat_id = getTopLevelCatID($this->search_cat);

		if( $this->cf_id > 0 )
		{
			$this->customfield = $this->getCustomField($this->cf_id);

			if( is_null($this->customfield) )
			{
				return JError::raiseError(404,JText::_('JGLOBAL_RESOURCE_NOT_FOUND'));
			}

			// Only 'mtags' custom fields uses comma (,) as value separator. Other taggable fieldtype use the bar (|) or
			// contain single value only. The condition below uses the appropriate separate based on the fieldtype.
			$this->fieldValueSeparator = '|';
			if( $this->customfield->field_type == 'mtags' ) {
				$this->fieldValueSeparator = ', ';
			}


		}
	}

	public function index()
	{
		global $savantConf;

		# Load custom template
		loadCustomTemplate( $this->search_cat, $savantConf);

		if( !$this->cf_id )
		{

			$taggable_fields = $this->getTaggableFields($this->limitstart, $this->mtconf->get('fe_num_of_searchby'));
			$total_taggable_fields = $this->getTotalNumberOfTaggableFields();

			jimport('joomla.html.pagination');
			$pageNav = new JPagination($total_taggable_fields, $this->limitstart, $this->mtconf->get('fe_num_of_searchby'));

			# Set title
			setTitle(\MText::sprintf( 'PAGE_TITLE_SEARCH_BY', $this->tlcat_id));

			# Savant Template
			$savant = new \Savant2($savantConf);
			$savant->assignRef('pathway', $pathWay);
			$savant->assign('pageNav', $pageNav);
			$savant->assign('cf_id', $this->cf_id);
			$savant->assign('cat_id', $this->search_cat);
			$savant->assign('tlcat_id', $this->tlcat_id);
			$savant->assign('taggable_fields', $taggable_fields);
			$savant->assign('template', $this->mtconf->get('template'));
			$savant->display( 'page_searchBy.tpl.php' );
			return;
		}

		if( empty($this->value) )
		{
			// List values from custom field
			$tags = $this->getTaggableFieldValues($this->cf_id, $this->limitstart, $this->mtconf->get('fe_num_of_searchbytags'));

			$total = $this->getTotalTagsForField($this->cf_id);

			jimport('joomla.html.pagination');
			$pageNav = new JPagination($total, $this->limitstart, $this->mtconf->get('fe_num_of_searchbytags'));

			# Set title
			setTitle(\MText::sprintf( 'PAGE_TITLE_SEARCH_BY_TAGS', $this->tlcat_id, $this->customfield->caption));

			# Savant Template
			$savant = new \Savant2($savantConf);
			$savant->assignRef('pathway', $pathWay);
			$savant->assign('pageNav', $pageNav);
			$savant->assign('cf_id', $this->cf_id);
			$savant->assign('cat_id', $this->search_cat);
			$savant->assign('tlcat_id', $this->tlcat_id);
			$savant->assign('searchword', $this->value);
			$savant->assign('customfieldcaption', $this->customfield->caption);
			$savant->assign('page_id', JFilterOutput::stringURLUnicodeSlug($this->customfield->caption));
			$savant->assign('tags', $tags);
			$savant->assign('template', $this->mtconf->get('template'));
			$savant->display( 'page_searchByTags.tpl.php' );

		} else {
			// Show listings matching value from a custom field
			$links = $this->getListingsFromFieldWithValue($this->cf_id, $this->value, $this->limitstart, $this->mtconf->get('fe_num_of_all') );

			$total = $this->getTotalListingsFromFieldWithValue( $this->cf_id, $this->value);

			jimport('joomla.html.pagination');
			$pageNav = new JPagination($total, $this->limitstart, $this->mtconf->get('fe_num_of_all'));

			# Set title
			setTitle(\MText::sprintf( 'PAGE_TITLE_SEARCH_BY_RESULTS', $this->tlcat_id, $this->customfield->caption, $this->value ));

			# Pathway
			$pathWay = new \mtPathWay();

			# Savant Template
			$savant = new \Savant2($savantConf);
			assignCommonListlinksVar( $savant, $links, $pathWay, $pageNav );

			$savant->assign('searchword', $this->value);
			$savant->assign('customfieldcaption', $this->customfield->caption);
			$savant->assign('cf_id', $this->cf_id);
			$savant->assign('cat_id', $this->search_cat);
			$savant->assign('tlcat_id', $this->tlcat_id);
			$savant->assign('total_listing', $total);
			$savant->assign(
				'page_id',
				JFilterOutput::stringURLUnicodeSlug($this->customfield->caption)
				. '-'
				. JFilterOutput::stringURLUnicodeSlug($this->value)
			);
			$savant->display( 'page_searchByResults.tpl.php' );
		}
	}

	public function getTaggableFields($limitstart, $limit)
	{
		global $Itemid;

		$this->db->setQuery(
			'SELECT cf.* '
			. ' FROM #__mt_customfields AS cf '
			. ' RIGHT JOIN #__mt_fields_map AS field_map '
			. ' ON field_map.cf_id = cf.cf_id AND field_map.cat_id = ' . $this->db->Quote($this->tlcat_id) . ' '
			. ' WHERE published = 1 AND tag_search = 1 '
			. ' ORDER BY ordering ASC '
			. " LIMIT $limitstart, ".$limit
		);
		$raw_taggable_fields = $this->db->loadObjectList();

		// Loop through each taggable fields and build a meaningful object for use in template
		$i=0;
		$taggable_fields = array();
		foreach( $raw_taggable_fields AS $tag )
		{
			$taggable_fields[$i] = new \stdClass();
			$taggable_fields[$i]->value = $tag->caption;
			$taggable_fields[$i]->link  = JRoute::_('index.php?option=com_mtree&task=searchby&cf_id='.$tag->cf_id.'&cat_id='.$this->search_cat.'&Itemid='.$Itemid);
			$taggable_fields[$i]->elementId  = 'browsebytags-'.JFilterOutput::stringURLUnicodeSlug($tag->caption);
			$i++;
		}

		return $taggable_fields;
	}

	public function getTotalNumberOfTaggableFields()
	{
		$this->db->setQuery( 'SELECT COUNT(cf_id) FROM #__mt_customfields WHERE published = 1 AND tag_search = 1' );
		return $this->db->loadResult();

	}

	public function getTaggableFieldValues( $cf_id=null, $limitstart=null, $limit=null )
	{
		global $Itemid;

		if( is_null($cf_id) && is_null($this->cf_id) ) {
			return null;
		} elseif( is_null($cf_id) && $this->cf_id > 0 ) {
			$cf_id = $this->cf_id;
		}

		// Is the field a core field?
		$this->db->setQuery('SELECT iscore FROM #__mt_customfields WHERE cf_id = ' . $this->db->Quote($cf_id) . ' LIMIT 1');
		$iscore = $this->db->loadResult();

		// Retrieve the custom field values
		if ( $iscore )
		{
			$this->db->setQuery('SELECT field_type FROM #__mt_customfields WHERE cf_id = ' . $this->db->Quote($cf_id) . ' LIMIT 1');
			$field_type = $this->db->loadResult();
			$core_name = substr($field_type,4);
			$this->db->setQuery('SELECT ' . $core_name . ' FROM #__mt_links WHERE ' . $core_name . ' != \'\'');
		} else {
			$this->db->setQuery(
				'SELECT REPLACE(value,\'|\',\',\')'
				. ' FROM #__mt_cfvalues AS cfv '
				. ' LEFT JOIN #__mt_links AS l ON l.link_id = cfv.link_id '
				. ' LEFT JOIN #__mt_cl AS cl ON l.link_id = cl.link_id AND cl.main = 1 '
				. ' LEFT JOIN #__mt_cats AS cat ON cat.cat_id = cl.cat_id '
				. ' WHERE '
				. ' cfv.cf_id = ' . $this->db->Quote($cf_id)
				. 	"\n AND link_published='1' AND link_approved='1' AND ( publish_up = ".$this->db->Quote($this->nullDate)." OR publish_up <= '$this->nowDate'  ) AND ( publish_down = ".$this->db->Quote($this->nullDate)." OR publish_down >= '$this->nowDate' )"
				. ((isset($this->mtCat)) ? ' AND cat.lft >= ' . $this->mtCat->lft . ' AND cat.rgt <= ' . $this->mtCat->rgt : '')
			);
		}
		$arrTags = $this->db->loadColumn();

		// Read through array of strings and return an array mapping tag with number of occurances
		$rawTags = array();
		foreach( $arrTags AS $tag )
		{
			$results = explode(',',$tag);
			$count = count($results);

			for($i=0;$i<$count;$i++)
			{
				$results[$i] = trim($results[$i]);
			}

			$rawTags = array_merge($rawTags,array_unique($results));
		}

		$rawTags = array_count_values($rawTags);

		$this->totalTagsForField[$cf_id] = count($rawTags);

		ksort($rawTags, SORT_NATURAL);

		if( $limitstart >= 0 && $limit > 0 ) {
			$rawTags = array_slice($rawTags, $limitstart, $limit, true);
		}

		// Loop through each tag and build a meaningful object for use in template
		$i=0;
		$tags=array();

		foreach( $rawTags AS $tag => $items )
		{
			$tags[$i] = new \stdClass();
			$tags[$i]->value = $tag;
			$tags[$i]->items = $items;
			$tags[$i]->link  = JRoute::_('index.php?option=com_mtree&task=searchby&cf_id='.$cf_id.'&cat_id='.$this->search_cat.'&value='.$tag.'&Itemid='.$Itemid);
			$tags[$i]->elementId  = 'browsebytags-value-'.JFilterOutput::stringURLUnicodeSlug($tag);
			$i++;
		}

		return $tags;
	}

	public function getTotalTagsForField($cf_id)
	{
		if( isset($this->totalTagsForField[$cf_id]) ) {
			return $this->totalTagsForField[$cf_id];
		}

		return false;
	}
	
	public function getListingsFromFieldWithValue( $cf_id, $value, $limitstart, $limit )
	{
		# Retrieve links
		$sql = "SELECT l.*, "
			.   "tlcat.cat_id AS tlcat_id, tlcat.cat_name AS tlcat_name, cl.*, cat.*, u.username AS username, u.name AS owner, "
			.   "GROUP_CONCAT(img.filename ORDER BY img.ordering ASC SEPARATOR ',') AS images "
			.   "FROM #__mt_links AS l";
		if( !$this->customfield->iscore ) {
			$sql .= "\n LEFT JOIN #__mt_cfvalues AS cfv ON cfv.link_id = l.link_id AND cfv.cf_id = " . $this->db->Quote($cf_id);
		}
		$sql .=	"\n	LEFT JOIN #__mt_images AS img ON img.link_id = l.link_id "
			.	"\n LEFT JOIN #__mt_cl AS cl ON cl.link_id = l.link_id "
			.	"\n LEFT JOIN #__users AS u ON u.id = l.user_id "
			.	"\n LEFT JOIN #__mt_cats AS cat ON cat.cat_id = cl.cat_id "
			.	"\n LEFT JOIN #__mt_cats AS tlcat ON tlcat.lft <= cat.lft AND tlcat.rgt >= cat.rgt AND tlcat.cat_parent =0 "
			.	"\n WHERE "
			. 	"\n link_published='1' AND link_approved='1' AND ( publish_up = ".$this->db->Quote($this->nullDate)." OR publish_up <= '$this->nowDate'  ) AND ( publish_down = ".$this->db->Quote($this->nullDate)." OR publish_down >= '$this->nowDate' )"
			.	"\n AND cl.link_id = l.link_id ";

		if( !$this->customfield->iscore ) {
			$sql .= "\n AND ("
				. "cfv.value = " . $this->db->Quote(''.$value.'')
				. " OR "
				. "cfv.value LIKE " . $this->db->Quote(''.$value.$this->fieldValueSeparator.'%')
				. " OR "
				. "cfv.value LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.$this->fieldValueSeparator.'%')
				. " OR "
				. "cfv.value LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.'')
				. ")";
		} else {
			$sql .= "\n AND ("
				. "l.".substr($this->customfield->field_type,4)." = " . $this->db->Quote(''.$value.'')
				. " OR "
				. "l.".substr($this->customfield->field_type,4)." LIKE " . $this->db->Quote(''.$value.$this->fieldValueSeparator.'%')
				. " OR "
				. "l.".substr($this->customfield->field_type,4)." LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.$this->fieldValueSeparator.'%')
				. " OR "
				. "l.".substr($this->customfield->field_type,4)." LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.'')
				. ")";
		}

		if(isset($this->mtCat)) {
			$sql .=	' AND cat.lft >= ' . $this->mtCat->lft . ' AND cat.rgt <= ' . $this->mtCat->rgt;
		}

		$sql .= "\n GROUP BY l.link_id";

		$listings = new listings($this->mtconf);
		$sql .= $listings->getSQLOrderBy();
		$sql .=	"\n LIMIT $limitstart, " . $limit;
		$this->db->setQuery( $sql );
		return  $this->db->loadObjectList();
	}

	public function getTotalListingsFromFieldWithValue( $cf_id, $value )
	{
		$sql = "SELECT COUNT(DISTINCT l.link_id) FROM (#__mt_links AS l, #__mt_cl AS cl";
		$sql .= ")";
		if( !$this->customfield->iscore ) {
			$sql .= "\n LEFT JOIN #__mt_cfvalues AS cfv ON cfv.link_id = l.link_id AND cfv.cf_id = " . $this->db->Quote($cf_id);
		}
		$sql .=	"\n	LEFT JOIN #__mt_cats AS cat ON cat.cat_id = cl.cat_id "
			.	"\n	WHERE "
			.	"link_published='1' AND link_approved='1' AND ( publish_up = ".$this->db->Quote($this->nullDate)." OR publish_up <= '$this->nowDate'  ) AND ( publish_down = ".$this->db->Quote($this->nullDate)." OR publish_down >= '$this->nowDate' )"
			.	"\n AND cl.link_id = l.link_id ";
		if( !$this->customfield->iscore ) {
			$sql .= "\n AND ("
					. "cfv.value = " . $this->db->Quote(''.$value.'')
					. " OR "
					. "cfv.value LIKE " . $this->db->Quote(''.$value.$this->fieldValueSeparator.'%')
					. " OR "
					. "cfv.value LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.$this->fieldValueSeparator.'%')
					. " OR "
					. "cfv.value LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.'')
					. ")";

		} else {
			$sql .= "\n AND ("
					. "l.".substr($this->customfield->field_type,4)." = " . $this->db->Quote(''.$value.'')
					. " OR "
					. "l.".substr($this->customfield->field_type,4)." LIKE " . $this->db->Quote(''.$value.$this->fieldValueSeparator.'%')
					. " OR "
					. "l.".substr($this->customfield->field_type,4)." LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.$this->fieldValueSeparator.'%')
					. " OR "
					. "l.".substr($this->customfield->field_type,4)." LIKE " . $this->db->Quote('%'.$this->fieldValueSeparator.$value.'')
					. ")";

		}

		if(isset($this->mtCat))
		{
			$sql .= ' AND cat.lft >= ' . $this->mtCat->lft . ' AND cat.rgt <= ' . $this->mtCat->rgt;
		}

		$this->db->setQuery( $sql );

		return $this->db->loadResult();
	}

	public function getCustomField($cf_id=null)
	{
		if( is_null($cf_id) ) $cf_id = $this->cf_id;

		# Retrieve information about custom field
		$this->db->setQuery( 'SELECT * FROM #__mt_customfields AS cf'
			. ' WHERE cf.cf_id = ' . $this->db->Quote($cf_id) . ' AND published = 1 AND tag_search = 1 LIMIT 1');
		return $this->db->loadObject();

	}
}