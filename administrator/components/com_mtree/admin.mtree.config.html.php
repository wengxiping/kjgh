<?php
/**
 * @package      Mosets Tree
 * @copyright    (C) 2015-present Mosets Consulting. All rights reserved.
 * @license      GNU General Public License
 * @author       Lee Cher Yeong <mtree@mosets.com>
 * @url          http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class MTConfigHtml
{

	public static function _($function, $items = array(), $config = null)
	{
		$args = func_get_args();
		array_shift($args);
		$i = 0;

		foreach ($items AS $item)
		{
			if (!isset($item['override']))
			{
				$item['override'] = null;
			}

			if (!isset($items[$i]['override']))
			{
				$items[$i]['override']   = null;
				$args[0][$i]['override'] = null;
			}

			if (!empty($config['namespace']))
			{
				$args[0][$i]['varname'] = $config['namespace'] . '[' . $args[0][$i]['varname'] . ']';
			}
			$i++;
		}

		if (empty($function))
		{
			return call_user_func_array(array('MTConfigHtml', 'self::text'), $args);
		}
		else
		{
			return call_user_func_array(array('MTConfigHtml', 'self::' . $function), $args);
		}
	}

	public static function overrideCheckbox($items = array(), $config = null)
	{
		$checked = ($items[0]['override'] != '' ? true : false);
		$class   = (!empty($config['class']) ? 'class="' . $config['class'] . '" ' : '');

		return '<input type="checkbox" name="override[' . $items[0]['varname'] . ']" value="1" ' . ($checked ? 'checked ' : '') . $class . 'onclick="" />';
	}

	public static function text($items, $config = null)
	{
		return '<input name="' . $items[0]['varname'] . '" value="' . htmlspecialchars(self::getValue($items[0])) . '" size="30" />';
	}

	public static function textarea($items, $config = null)
	{
		return '<textarea name="' . $items[0]['varname'] . '"  cols="80" rows="8" >'
		. htmlspecialchars(self::getValue($items[0]))
		. '</textarea>';
	}

	public static function label($items, $config = null)
	{
		return JText::_('COM_MTREE_CONFIGLABEL_' . strtoupper($items[0]['varname']));
	}

	public static function type_of_listings_in_index($items, $config = null)
	{
		# Listings type in index
		$type_of_listings_in_index = array();
		$arr_tmp                   = array('listcurrent', 'listpopular', 'listmostrated', 'listtoprated', 'listmostreview', 'listnew', 'listupdated', 'listfavourite', 'listfeatured', 'listrandom');

		foreach ($arr_tmp AS $tmp)
		{
			$type_of_listings_in_index[] = JHtml::_('select.option', $tmp, JText::_('COM_MTREE_TYPES_OF_LISTINGS_IN_INDEX_OPTION_' . strtoupper($tmp)));
		}

		return JHtml::_('select.genericlist', $type_of_listings_in_index, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function show_requirements($items, $config = null)
	{
		$options   = array();
		$options[] = JHtml::_('select.option', "0", JText::_('JNEVER'));
		$options[] = JHtml::_('select.option', "1", JText::_('COM_MTREE_ALL_THE_TIME'));
		$options[] = JHtml::_('select.option', "2", JText::_('COM_MTREE_ONLY_WHEN_USER_HAS_PERMISSION'));

		return JHtml::_('select.genericlist', $options, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function owner_default_page($items, $config = null)
	{
		$default_owner_listing_page   = array();
		$default_owner_listing_page[] = JHtml::_('select.option', "viewuserslisting", JText::_('COM_MTREE_DEFAULT_OWNER_LISTING_PAGE_OPTION_VIEWUSERSLISTING'));
		$default_owner_listing_page[] = JHtml::_('select.option', "viewusersfav", JText::_('COM_MTREE_DEFAULT_OWNER_LISTING_PAGE_OPTION_VIEWUSERSFAV'));
		$default_owner_listing_page[] = JHtml::_('select.option', "viewusersreview", JText::_('COM_MTREE_DEFAULT_OWNER_LISTING_PAGE_OPTION_VIEWUSERSREVIEW'));

		return JHtml::_('select.genericlist', $default_owner_listing_page, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function feature_locations($items, $config = null)
	{
		$feature_locations   = array();
		$feature_locations[] = JHtml::_('select.option', "1", JText::_('COM_MTREE_STANDALONE_PAGE'));
		$feature_locations[] = JHtml::_('select.option', "2", JText::_('COM_MTREE_LISTING_DETAILS_PAGE'));

		return JHtml::_('select.genericlist', $feature_locations, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function sef_link_slug_type($items, $config = null)
	{
		$sef_link_slug_type   = array();
		$sef_link_slug_type[] = JHtml::_('select.option', "1", JText::_('COM_MTREE_ALIAS'));
		$sef_link_slug_type[] = JHtml::_('select.option', "2", JText::_('COM_MTREE_LINK_ID'));
		$sef_link_slug_type[] = JHtml::_('select.option', "3", JText::_('COM_MTREE_LINK_ID_AND_ALIAS_HYBRID'));

		return JHtml::_('select.genericlist', $sef_link_slug_type, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function sef_owner_slug_type($items, $config = null)
	{
		$sef_owner_slug_type   = array();
		$sef_owner_slug_type[] = JHtml::_('select.option', "1", JText::_('COM_MTREE_OWNER_SLUG_TYPE_1'));
		$sef_owner_slug_type[] = JHtml::_('select.option', "2", JText::_('COM_MTREE_OWNER_SLUG_TYPE_2'));

		return JHtml::_('select.genericlist', $sef_owner_slug_type, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function resize_method($items, $config = null)
	{
		$imageLibs = detect_ImageLibs();

		return $imageLibs['gd2'];
	}

	public static function yesno($items, $config = null)
	{
		return self::_radio(array(
				JHtml::_('select.option', '0', JText::_('JNO')),
				JHtml::_('select.option', '1', JText::_('JYES'))
		), $items, $config);
	}

	public static function yesno_default_shown_or_hidden($items, $config = null)
	{
		return self::_radio(array(
				JHtml::_('select.option', '0', JText::_('JNO')),
				JHtml::_('select.option', '1', JText::_('COM_MTREE_YES_SHOWN_BY_DEFAULT')),
				JHtml::_('select.option', '2', JText::_('COM_MTREE_YES_HIDDEN_BY_DEFAULT'))
		), $items, $config);
	}

	private static function _radio($options, $items, $config = null)
	{
		$html = '<fieldset class="radio btn-group" id="' . str_replace(array('[', ']'), array('_', ''), $items[0]['varname']) . '_fieldset">';

		$value        = (int) self::getValue($items[0]);

		foreach ($options AS $option)
		{
			$html .= '<input type="radio" ';
			if ($value == $option->value)
			{
				$html .= 'checked="checked" ';
			}
			$html .= 'value="' . $option->value . '" name="' . $items[0]['varname'] . '" id="' . str_replace(array('[', ']'), array('_', ''), $items[0]['varname']) . $option->value . '">';
			$html .= '<label for="' . str_replace(array('[', ']'), array('_', ''), $items[0]['varname']) . $option->value . '" ';
			$html .= 'class="';
			$html .= '">';
			$html .= $option->text;
			$html .= '</label>';
		}

		$html .= '</fieldset>';

		return $html;

	}

	public static function cat_order($items, $config = null)
	{
		# Sort Direction
		$sort[] = JHtml::_('select.option', "asc", JText::_('COM_MTREE_ASCENDING'));
		$sort[] = JHtml::_('select.option', "desc", JText::_('COM_MTREE_DESCENDING'));

		# Category Order
		$cat_order   = array();
		$cat_order[] = JHtml::_('select.option', '', JText::_(''));
		$cat_order[] = JHtml::_('select.option', "lft", JText::_('COM_MTREE_CONFIG_CUSTOM_ORDER'));
		$cat_order[] = JHtml::_('select.option', "cat_name", JText::_('COM_MTREE_NAME'));
		$cat_order[] = JHtml::_('select.option', "cat_featured", JText::_('COM_MTREE_FEATURED'));
		$cat_order[] = JHtml::_('select.option', "cat_created", JText::_('COM_MTREE_CREATED'));

		$html = JHtml::_('select.genericlist', $cat_order, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
		$html .= JHtml::_('select.genericlist', $sort, $items[1]['varname'], 'size="1"', 'value', 'text', self::getValue($items[1]));

		return $html;
	}


	public static function predefined_reply_title($items, $config = null)
	{
		$html = '<input name="' . $items[0]['varname'] . '" value="' . self::getValue($items[0]) . '" size="60" />';
		$html .= '<br />';
		$html .= '<textarea style="margin-top:5px" name="' . $items[1]['varname'] . '" cols="80" rows="8" />' . self::getValue($items[1]) . '</textarea>';

		return $html;
	}

	public static function note($items)
	{
		return JText::_('COM_MTREE_CONFIGNOTE_' . strtoupper($items[0]['varname']));
	}

	public static function listing_order($items, $config = null)
	{
		return self::_listing_order($items, $config);
	}

	public static function second_listing_order($items, $config = null)
	{
		return self::_listing_order($items, $config, true);
	}

	private static function _listing_order($items, $config = null, $hasNone = false )
	{
		# Sort Direction
		$sort[] = JHtml::_('select.option', "asc", JText::_('COM_MTREE_ASCENDING'));
		$sort[] = JHtml::_('select.option', "desc", JText::_('COM_MTREE_DESCENDING'));

		# Listing Order
		$listing_order   = array();
		if( $hasNone ) {
			$listing_order[] = JHtml::_('select.option', "none", JText::_('JNONE'));
		}
		$listing_order[] = JHtml::_('select.option', "link_name", JText::_('COM_MTREE_NAME'));
		$listing_order[] = JHtml::_('select.option', "link_hits", JText::_('COM_MTREE_HITS'));
		$listing_order[] = JHtml::_('select.option', "link_votes", JText::_('COM_MTREE_VOTES'));
		$listing_order[] = JHtml::_('select.option', "link_rating", JText::_('COM_MTREE_RATING'));
		$listing_order[] = JHtml::_('select.option', "link_visited", JText::_('COM_MTREE_VISIT'));
		$listing_order[] = JHtml::_('select.option', "link_featured", JText::_('COM_MTREE_FEATURED'));
		$listing_order[] = JHtml::_('select.option', "link_created", JText::_('COM_MTREE_CREATED'));
		$listing_order[] = JHtml::_('select.option', "link_modified", JText::_('COM_MTREE_MODIFIED'));
		$listing_order[] = JHtml::_('select.option', "firstname", JText::_('COM_MTREE_FIRSTNAME'));
		$listing_order[] = JHtml::_('select.option', "lastname", JText::_('COM_MTREE_LASTNAME'));
		$listing_order[] = JHtml::_('select.option', "address", JText::_('COM_MTREE_ADDRESS'));
		$listing_order[] = JHtml::_('select.option', "city", JText::_('COM_MTREE_CITY'));
		$listing_order[] = JHtml::_('select.option', "state", JText::_('COM_MTREE_STATE'));
		$listing_order[] = JHtml::_('select.option', "country", JText::_('COM_MTREE_COUNTRY'));
		$listing_order[] = JHtml::_('select.option', "postcode", JText::_('COM_MTREE_POSTCODE'));
		$listing_order[] = JHtml::_('select.option', "contactperson", JText::_('COM_MTREE_CONTACTPERSON'));
		$listing_order[] = JHtml::_('select.option', "mobile", JText::_('COM_MTREE_MOBILE'));
		$listing_order[] = JHtml::_('select.option', "date", JText::_('COM_MTREE_DATE'));
		$listing_order[] = JHtml::_('select.option', "year", JText::_('COM_MTREE_YEAR'));
		$listing_order[] = JHtml::_('select.option', "telephone", JText::_('COM_MTREE_TELEPHONE'));
		$listing_order[] = JHtml::_('select.option', "fax", JText::_('COM_MTREE_FAX'));
		$listing_order[] = JHtml::_('select.option', "email", JText::_('COM_MTREE_EMAIL'));
		$listing_order[] = JHtml::_('select.option', "website", JText::_('COM_MTREE_WEBSITE'));
		$listing_order[] = JHtml::_('select.option', "price", JText::_('COM_MTREE_PRICE'));
		$listing_order[] = JHtml::_('select.option', "random", JText::_('COM_MTREE_RANDOM'));

		if (in_array('l.ordering', array($items[0]['value'], $items[0]['override'], $items[1]['value'], $items[1]['override'])))
		{
			$listing_order[] = JHtml::_('select.option', "l.ordering", JText::_('COM_MTREE_ORDERING'));
		}

		$html = JHtml::_('select.genericlist', $listing_order, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
		$html .= JHtml::_('select.genericlist', $sort, $items[1]['varname'], 'size="1"', 'value', 'text', self::getValue($items[1]));

		return $html;
	}

	public static function random_listings_shuffle_frequency($items, $config = null)
	{
		$options   = array();
		$options[] = JHtml::_('select.option', "60", JText::_('COM_MTREE_EVERY_MINUTE'));
		$options[] = JHtml::_('select.option', "3600", JText::_('COM_MTREE_HOURLY'));
		$options[] = JHtml::_('select.option', "86400", JText::_('COM_MTREE_DAILY'));

		return JHtml::_('select.genericlist', $options, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

	}

	public static function review_order($items, $config = null)
	{
		# Sort Direction
		$sort[] = JHtml::_('select.option', "asc", JText::_('COM_MTREE_ASCENDING'));
		$sort[] = JHtml::_('select.option', "desc", JText::_('COM_MTREE_DESCENDING'));

		# Review Order
		$review_order[] = JHtml::_('select.option', '', JText::_(''));
		$review_order[] = JHtml::_('select.option', "rev_date", JText::_('COM_MTREE_REVIEW_DATE'));
		$review_order[] = JHtml::_('select.option', "vote_helpful", JText::_('COM_MTREE_TOTAL_HELPFUL_VOTES'));
		$review_order[] = JHtml::_('select.option', "vote_total", JText::_('COM_MTREE_TOTAL_VOTES'));

		$html = JHtml::_('select.genericlist', $review_order, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
		$html .= JHtml::_('select.genericlist', $sort, $items[1]['varname'], 'size="1"', 'value', 'text', self::getValue($items[1]));

		return $html;
	}

	public static function sort($items, $config = null)
	{
		return self::_sort($items, $config, false);
	}

	public static function sort2($items, $config = null)
	{
		return self::_sort($items, $config, true);
	}

	public static function _sort($items, $config = null, $hasNone = false)
	{
		$sort_by_options = array('-link_featured', '-link_created', '-link_modified', '-link_hits', '-link_visited', '-link_rating', '-link_votes', 'link_name', '-price', 'price', '-date', 'date', '-year', 'year', 'address', '-address', 'city', '-city', 'state', '-state', 'postcode', '-postcode', 'country', '-country', 'contactperson', '-contactperson', 'firstname', '-firstname', 'lastname', '-lastname', 'telephone', '-telephone', 'mobile', '-mobile', 'fax', '-fax', 'email', '-email', 'website', '-website', 'random');

		if( $hasNone ) {
			$sort_by[] = JHtml::_('select.option', "none", JText::_('JNONE'));
		}

		foreach ($sort_by_options AS $sort_by_option)
		{
			$sort_by[] = JHtml::_('select.option', $sort_by_option, JText::_('COM_MTREE_ALL_LISTINGS_SORT_OPTION_' . strtoupper($sort_by_option)));
		}
		$html = JHtml::_('select.genericlist', $sort_by, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

		return $html;
	}

	public static function taggable_fields($items, $config = null)
	{
		$db	= JFactory::getDBO();

//		$sort_by_options = array('-link_featured', '-link_created', '-link_modified', '-link_hits', '-link_visited', '-link_rating', '-link_votes', 'link_name', '-price', 'price', '-date', 'date', '-year', 'year', 'address', '-address', 'city', '-city', 'state', '-state', 'postcode', '-postcode', 'country', '-country', 'contactperson', '-contactperson', 'firstname', '-firstname', 'lastname', '-lastname', 'telephone', '-telephone', 'mobile', '-mobile', 'fax', '-fax', 'email', '-email', 'website', '-website', 'random');

		$db->setQuery( 'SELECT cf_id, caption, alias FROM #__mt_customfields WHERE tag_search = 1 ORDER BY ordering ASC' );
		$options	= $db->loadObjectList('cf_id');

		$option_values = self::getValue($items[0]);
		if (!is_array($option_values))
		{
			$option_values = explode('|', $option_values);
		}

		$html = '';
		$html .= '<fieldset>';
		$html .= <<<CSS_TAGGABLE_FIELDS
	<style scoped>
	#config_index_search_by ul {
  -webkit-column-count: 4;
     -moz-column-count: 4;
          column-count: 4;
          -webkit-column-width: 150px;
     -moz-column-width: 150px;
          column-width: 150px;
}
	</style>
CSS_TAGGABLE_FIELDS;

		$html .= '<ul>';
		foreach ($options AS $sort_by_option)
		{
			$html .= '<li>';
			$html .= '<label>';
			$html .= '<input type="checkbox" name="' . $items[0]['varname'] . '[]" value="' . $sort_by_option->cf_id . '"';
			$html .= ' style="clear:left"';
			if (isset($option_values) && in_array($sort_by_option->cf_id, $option_values))
			{
				$html .= ' checked';
			}
			$html .= ' />';
			$html .= $sort_by_option->caption;
			$html .= '</label>';
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</fieldset>';

		return $html;
	}

	public static function sort_options($items, $config = null)
	{
		$sort_by_options = array('-link_featured', '-link_created', '-link_modified', '-link_hits', '-link_visited', '-link_rating', '-link_votes', 'link_name', '-price', 'price', '-date', 'date', '-year', 'year', 'address', '-address', 'city', '-city', 'state', '-state', 'postcode', '-postcode', 'country', '-country', 'contactperson', '-contactperson', 'firstname', '-firstname', 'lastname', '-lastname', 'telephone', '-telephone', 'mobile', '-mobile', 'fax', '-fax', 'email', '-email', 'website', '-website', 'random');

		$sort_by_option_values = self::getValue($items[0]);
		if (!is_array($sort_by_option_values))
		{
			$sort_by_option_values = explode('|', $sort_by_option_values);
		}

		$html = '';
		$html .= '<fieldset>';
		$html .= '
	<style scoped>
	#config_all_listings_sort_by_options ul {
  -webkit-column-count: 4;
     -moz-column-count: 4;
          column-count: 4;
          -webkit-column-width: 150px;
     -moz-column-width: 150px;
          column-width: 150px;
}
	</style>';

		$html .= '<ul>';
		foreach ($sort_by_options AS $sort_by_option)
		{
			$html .= '<li>';
			$html .= '<label>';
			$html .= '<input type="checkbox" name="' . $items[0]['varname'] . '[]" value="' . $sort_by_option . '"';
			$html .= ' style="clear:left"';
			if (isset($sort_by_option_values) && in_array($sort_by_option, $sort_by_option_values))
			{
				$html .= ' checked';
			}
			$html .= ' />';
			$html .= JText::_('COM_MTREE_ALL_LISTINGS_SORT_OPTION_' . strtoupper($sort_by_option));
			$html .= '</label>';
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</fieldset>';

		return $html;
	}

	public static function access_level($items, $config = null)
	{
		$db	= JFactory::getDBO();

		$db->setQuery( 'SELECT id, title FROM #__viewlevels ORDER BY ordering ASC' );
		$access_levels	= $db->loadObjectList();

		foreach ($access_levels AS $access_level)
		{
			$jhtml_access_levels[] = JHtml::_('select.option', $access_level->id, $access_level->title);
		}
		$html = JHtml::_('select.genericlist', $jhtml_access_levels, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

		return $html;
	}

	public static function user_groups($items, $config = null)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

		$user_groups = UsersHelper::getGroups();

		$user_groups_option_values = self::getValue($items[0]);
		if (!is_array($user_groups_option_values))
		{
			$user_groups_option_values = explode('|', $user_groups_option_values);
		}

		$html = '';
		$html .= '<fieldset>';
		$html .= '<ul>';
		foreach ($user_groups AS $user_group)
		{
			// User group ID 1 and 3 represents Public and Guest user groups respectively. We don't allow either of
			// these 2 groups to be selected as managers.
			if( in_array($user_group->value,array(1,13))) {
				continue;
			}

			$html .= '<li>';
			$html .= '<label>';

			if($user_group->value == '8') {
				$html .= '&#10004;';
				$html .= '<input type="hidden" name="' . $items[0]['varname'] . '[]" value="' . $user_group->value . '" />';
			}
			else
			{
				$html .= '<input type="checkbox" name="' . $items[0]['varname'] . '[]" value="' . $user_group->value . '"';
				$html .= ' style="clear:left"';
				if (isset($user_groups_option_values) && in_array($user_group->value, $user_groups_option_values))
				{
					$html .= ' checked';
				}
				$html .= ' />';

			}

			$html .= $user_group->text;
			$html .= '</label>';
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</fieldset>';

		return $html;
	}

	public static function map_providers($items, $config = null)
	{
		$arr = array( 'CUSTOM', 'GOOGLE', 'MAPBOX', 'HERE');
		$options   = array();

		foreach( $arr AS $item )
		{
			$options[] = JHtml::_('select.option', strtolower($item), Jtext::_('COM_MTREE_MAP_PROVIDER_'.strtoupper($item)));
		}

		return JHtml::_('select.genericlist', $options, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

	}

	public static function map_type_ids($items, $config = null)
	{
		$arr = array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN', 'STYLED_MAP');

		$current_values = self::getValue($items[0]);
		if (!is_array($current_values))
		{
			$current_values = explode('|', $current_values);
		}

		$html = '<fieldset><ul>';

		foreach ($arr AS $item)
		{
			$html .= '<li>';
			$html .= '<label>';


			$html .= '<input type="checkbox" name="' . $items[0]['varname'] . '[]" value="' . $item . '"';
			if (in_array($item, $current_values))
			{
				$html .= ' checked';
			}
			$html .= ' />';


			$html .= ucwords(strtolower(str_replace('_',' ',$item)));
			$html .= '</label>';
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</fieldset>';

		return $html;
	}

	public static function map_type_id($items, $config = null)
	{
		$arr = array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN', 'STYLED_MAP');
		$options   = array();

		foreach( $arr AS $item )
		{
			$options[] = JHtml::_('select.option', $item, ucwords(strtolower(str_replace('_',' ',$item))));
		}

		return JHtml::_('select.genericlist', $options, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

	}

	public static function tile_servers($items, $config = null)
	{
		$arr = array(
			'OpenStreetMap' => '{"url":"https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", "attribution": "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"}',
			'Wikimedia' => '{"url": "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png", "attribution": "<a href=\"https://wikimediafoundation.org/wiki/Maps_Terms_of_Use\">Wikimedia</a>"}',
			'CartoDB.Positron' => '{"url": "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png", "attribution": "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors &copy; <a href=\"https://carto.com/attributions\">CARTO</a>"}',
			'CartoDB.Voyager' => '{"url": "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png", "attribution": "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors &copy; <a href=\"https://carto.com/attributions\">CARTO</a>"}',
			'CartoDB.VoyagerNoLabels' => '{"url": "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}{r}.png", "attribution": "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors &copy; <a href=\"https://carto.com/attributions\">CARTO</a>"}',
			'CartoDB.VoyagerLabelsUnder' => '{"url": "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png", "attribution": "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors &copy; <a href=\"https://carto.com/attributions\">CARTO</a>"}',
			'HikeBike.HikeBike' => '{"url": "https://tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png", "attribution": "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"}',
			'Stamen.Toner' => '{"url":"https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}{r}.png", "attribution": "Map tiles by <a href=\"http://stamen.com\">Stamen Design</a>, <a href=\"http://creativecommons.org/licenses/by/3.0\">CC BY 3.0</a> &mdash; Map data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"}',
			'Hydda.Full' => '{"url": "https://{s}.tile.openstreetmap.se/hydda/full/{z}/{x}/{y}.png", "attribution": "Tiles courtesy of <a href=\"http://openstreetmap.se/\" target=\"_blank\">OpenStreetMap Sweden</a> &mdash; Map data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"}',
			'Esri.WorldStreetMap' => '{"url": "https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}", "attribution": "Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012"}',
			'Esri.WorldImagery' => '{"url": "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", "attribution": "Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community"}',
			'Esri.WorldTopoMap' => '{"url": "https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}", "attribution": "Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community"}',
			'OpenStreetMap.DE' => '{"url": "https://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png", "attribution": "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"}',
			'OpenStreetMap.CH' => '{"url": "https://tile.osm.ch/switzerland/{z}/{x}/{y}.png", "attribution": "&copy; <a href=https://www.openstreetmap.org/copyright>OpenStreetMap</a> contributors"}',
			'OpenStreetMap.France' => '{"url": "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png", "attribution": "&copy; Openstreetmap France | &copy; <a href=https://www.openstreetmap.org/copyright>OpenStreetMap</a> contributors"}',
			'OpenStreetMap.HOT' => '{"url": "https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png", "attribution": "&copy; <a href=https://www.openstreetmap.org/copyright>OpenStreetMap</a> contributors, Tiles style by <a href=https://www.hotosm.org/ target=_blank>Humanitarian OpenStreetMap Team</a> hosted by <a href=https://openstreetmap.fr/ target=_blank>OpenStreetMap France</a>"}',
			'OpenMapSurfer.Roads' => '{"url": "https://maps.heigit.org/openmapsurfer/tiles/roads/webmercator/{z}/{x}/{y}.png", "attribution": "Imagery from <a href=\"http://giscience.uni-hd.de/\">GIScience Research Group @ University of Heidelberg</a> | Map data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"}',
			Jtext::_('COM_MTREE_ENTER_CUSTOM_TILE_SERVER_BELOW') => '',
		);
		$options   = array();

		foreach( $arr AS $name => $json )
		{
			$options[] = JHtml::_('select.option', $json, $name);
		}

		return JHtml::_('select.genericlist', $options, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

	}

	
	public static function getValue($item)
	{
		if (isset($item['override']) && $item['override'] != '')
		{
			return $item['override'];
		}
		else
		{
			return $item['value'];
		}
	}

}