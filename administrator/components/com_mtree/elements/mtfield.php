<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('JPATH_BASE') or die();

JFormHelper::loadFieldClass('list');

/**
 * Renders a list of fields
 *
 * @author 	Lee Cher Yeong <mtree@mosets.com>
 * @package 	Mosets Tree
 * @subpackage	Parameter
 * @since	3.7
 */

class JFormFieldMTField extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 */
	protected $type = 'Field';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 */
	protected function getOptions()
	{
		$restrict = '';

		if( $this->element['onlycustomfields'] )
		{
			$restrict .= 'iscore = 0';
		}
		elseif( $this->element['onlycorefields'] )
		{
			$restrict .= 'iscore = 1';
		}

		$db		=  JFactory::getDBO();
		$sql = 'SELECT * FROM #__mt_customfields WHERE published = 1';

		if( !empty($restrict) ) {
			$sql .= ' AND ' . $restrict;
		}

		$db->setQuery( $sql );
		$fields		= $db->loadObjectList();

		$tmp = JHtml::_('select.option', '', '', 'value', 'text');
		$tmp->checked = '';
		$options[] = $tmp;

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
