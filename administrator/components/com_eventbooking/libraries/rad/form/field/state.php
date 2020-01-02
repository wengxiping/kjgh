<?php
/**
 * Supports a custom field which display list of countries
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class RADFormFieldState extends RADFormFieldList
{
	/**
	 * The current form country
	 *
	 * @var string
	 */
	public $country;

	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'State';

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_STATE'));

		if ($this->country)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('state_name AS value, state_name AS text')
				->from('#__eb_states AS a')
				->innerJoin('#__eb_countries AS b ON a.country_id = b.country_id')
				->where('b.name = ' . $db->quote($this->country));
			$db->setQuery($query);
			$options = array_merge($options, $db->loadObjectList());
		}

		return $options;
	}
}
