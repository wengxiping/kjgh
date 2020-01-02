<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('JPATH_BASE') or die();

JFormHelper::loadFieldClass('list');

require_once( JPATH_ROOT.'/components/com_mtree/mtree.tools.php');

/**
 * Renders a list of fields
 *
 * @author 	Lee Cher Yeong <mtree@mosets.com>
 * @package 	Mosets Tree
 * @subpackage	Parameter
 * @since	3.6
 */

class JFormFieldMTCategory extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 */
	protected $type = 'MT Category';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 */
	protected function getOptions()
	{
		getCatsSelectlist( 0, $cat_tree, 3 );

		if( !empty($cat_tree) ) {
			$cat_options[] = JHtml::_('select.option', 0, JText::_( 'COM_MTREE_FORM_FIELD_MTCATEGORY_ALL_CATEGORIES' ));
			foreach( $cat_tree AS $ct ) {
				$cat_options[] = JHtml::_('select.option', $ct["cat_id"], str_repeat("-",($ct["level"]*3)) .(($ct["level"]>0) ? "":''). $ct["cat_name"]);
			}
		}

		return $cat_options;
	}
}
