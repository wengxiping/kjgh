<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined ( '_JEXEC' ) or die ();

/**
 * Form Field class for the Joomla Platform.
 * Provides radio button inputs
 *
 * @package Joomla.Platform
 * @subpackage Form
 * @link http://www.w3.org/TR/html-markup/command.radio.html#command.radio
 * @since 11.1
 */
class JFormFieldModuleStatus extends JFormField {
	/**
	 * The form field type.
	 *
	 * @var string
	 * @since 11.1
	 */
	protected $type = 'ModuleStatus';
	
	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return string The field input markup.
	 *        
	 * @since 11.1
	 */
	protected function getInput() {
		// Initialize variables.
		$html = null;
		$isModulePublished = null;
		
		// Retrieve status informations about the chat module
		$db = JFactory::getDbo();
		$queryModuleStatus = "SELECT id, published, position" .
							 "\n FROM #__modules" .
							 "\n WHERE " . $db->quoteName('module') . "=" . $db->quote('mod_jrealtimeanalytics') .
							 "\n AND " . $db->quoteName('published') . ">= 0" ;
		$db->setQuery($queryModuleStatus);
		$publishedModule = $db->loadObject();
		if (is_object($publishedModule)) {
			$isModulePublished = $publishedModule->published && ($publishedModule->position != '');
		}
		
		// Initialize some field attributes.
		
		if ($isModulePublished) {
			$html =	'<a target="_blank" href="index.php?option=com_modules&amp;task=module.edit&amp;id=' . $publishedModule->id . '">' .
					'<span data-content="' . JText::sprintf ( 'COM_JREALTIME_MODULE_ENABLED_DESC', $publishedModule->position) . 
					'" class="label label-success label-large hasPopover">' . '<span class="icon-checkmark"></span>' . 
					JText::sprintf ( 'COM_JREALTIME_MODULE_ENABLED' ) . '</span></a>';
		} elseif(is_object($publishedModule)) {
			$html = '<a target="_blank" href="index.php?option=com_modules&amp;task=module.edit&amp;id=' . $publishedModule->id . '">' .
					'<span data-content="' . JText::_ ( 'COM_JREALTIME_MODULE_DISABLED_DESC' ) . 
					'" class="label label-important label-large hasPopover">' . '<span class="icon-remove"></span>' . 
					JText::sprintf ( 'COM_JREALTIME_MODULE_DISABLED' ) . '</span></a>';
		} else {
			$html = '<span data-content="' . JText::_ ( 'COM_JREALTIME_MODULE_NOTFOUND_DESC' ) .
					'" class="label label-important label-large hasPopover">' . '<span class="icon-remove"></span>' .
					JText::sprintf ( 'COM_JREALTIME_MODULE_NOTFOUND' ) . '</span>';
		}
		
		return $html;
	}
}
