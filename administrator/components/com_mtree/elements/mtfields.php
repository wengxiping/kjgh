<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2005-2015 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('JPATH_BASE') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('checkboxes');

/**
 * Renders a list of fields
 *
 * @author 	Lee Cher Yeong <mtree@mosets.com>
 * @package 	Mosets Tree
 * @subpackage	Parameter
 * @since	2.2
 */

class JFormFieldMTFields extends JFormFieldCheckboxes
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 */
	protected $type = 'Fields';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 */
	protected function getOptions()
	{
		$exclude = (string) $this->element['exclude'];
		$arrExclude = array();

		if( !empty($exclude) ) {
			$arrExclude = explode(',', $exclude);
		}

		$db		=  JFactory::getDBO();
		$sql = 'SELECT * FROM #__mt_customfields WHERE published = 1';

		if( !empty($exclude) ) {
			$sql .= ' AND field_type NOT IN (';

			$arrNotIn = array();
			foreach( $arrExclude AS $fieldtype ) {
				$arrNotIn[] = $db->quote($fieldtype);
			}

			$sql .= implode(',', $arrNotIn);
			$sql .= ')';
		}
		$sql .= ';';

		$db->setQuery( $sql );
		$fields		= $db->loadObjectList();

		foreach ($fields as $field)
		{
			$tmp = JHtml::_('select.option', (string) $field->cf_id, trim((string) $field->caption), 'value', 'text');
            $tmp->checked = '';
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
