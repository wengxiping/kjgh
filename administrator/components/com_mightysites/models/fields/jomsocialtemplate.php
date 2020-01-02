<?php
/**
* @package		Mightysites
* @copyright	Copyright (C) 2009-2017 AlterBrains.com. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
*/
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports a list of installed application languages
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JFormFieldContentLanguage for a select list of content languages.
 * @since       11.1
 */
class JFormFieldJomsocialtemplate extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Jomsocialtemplate';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		$path = JPATH_SITE . '/components/com_community/templates';

		if (is_dir($path))
		{

			$folders = JFolder::folders($path);

			// Build the options list from the list of folders.
			if (is_array($folders))
			{
				foreach ($folders as $folder)
				{
					$options[] = JHtml::_('select.option', $folder, $folder);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
