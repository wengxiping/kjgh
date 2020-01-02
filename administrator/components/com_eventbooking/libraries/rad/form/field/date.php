<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a Date custom field.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class RADFormFieldDate extends RADFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Date';

	/**
	 * Method to get the field input markup.
	 *
	 * @param EventbookingHelperBootstrap $bootstrapHelper
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$config       = EventbookingHelper::getConfig();
		$dateFormat   = $config->get('date_field_format') ?: '%Y-%m-%d';
		$iconCalendar = $bootstrapHelper ? $bootstrapHelper->getClassMapping('icon-calendar') : 'icon-calendar';

		try
		{
			return str_replace('icon-calendar', $iconCalendar, JHtml::_('calendar', $this->value, $this->name, $this->name, $dateFormat, $this->attributes));
		}
		catch (Exception $e)
		{
			return str_replace('icon-calendar', $iconCalendar, JHtml::_('calendar', '', $this->name, $this->name, $dateFormat, $this->attributes)) . ' Value <strong>' . $this->value . '</strong> is invalid. Please correct it with format YYYY-MM-DD';
		}
	}
}
