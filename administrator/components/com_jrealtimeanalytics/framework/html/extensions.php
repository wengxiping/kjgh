<?php
//namespace components\com_jrealtimeanalytics\framework\html;
/**  
 * @package JREALTIMEANALYTICS::components::com_jrealtimeanalytics
 * @subpackage framework 
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html   
 */ 
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.form.fields.list');

/**
 * Form Field for ACL Groups
 * @package JREALTIMEANALYTICS::components::com_jrealtimeanalytics
 * @subpackage framework 
 * @subpackage html
 * @since 2.0
 */
class JFormFieldExtensions extends JFormFieldList {
	  
	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return string The field input markup.
	 *        
	 * @since 11.1
	 */
	protected function getInput() {
		// Initialize variables.
		$html = array ();
		$attr = '';
		
		// Initialize some field attributes.
		$attr .= $this->element ['class'] ? ' class="' . ( string ) $this->element ['class'] . '"' : '';
		
		// To avoid user's confusion, readonly="true" should imply
		// disabled="true".
		if (( string ) $this->element ['readonly'] == 'true' || ( string ) $this->element ['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}
		
		$attr .= $this->element ['size'] ? ' size="' . ( int ) $this->element ['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		
		// Initialize JavaScript field attributes.
		$attr .= $this->element ['onchange'] ? ' onchange="' . ( string ) $this->element ['onchange'] . '"' : '';
		
		// Get the field options.
		$options = ( array ) $this->getOptions ();
		
		$html = JHtml::_ ( 'select.genericlist', $options, $this->name, trim ( $attr ), 'value', 'text', $this->value, $this->id );
		
		return $html;
	}
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions() {
		$db = JFactory::getDbo ();
		$queryComponent = "SELECT DISTINCT " . $db->quoteName('element') . " AS value, SUBSTRING(" . $db->quoteName('element') . ", 5) AS text" .
						  "\n FROM #__extensions" .
						  "\n WHERE (" . $db->quoteName('protected') . " = 0" .
						  "\n OR " . $db->quoteName('element') . " = " . $db->quote('com_content') . // Exception for custom content sources
						  "\n OR " . $db->quoteName('element') . " = " . $db->quote('com_tags') . // Exception for custom tags sources
						  "\n OR " . $db->quoteName('element') . " = " . $db->quote('com_docman') . ")" . // Exception for docman 2 protected
		 				  "\n AND ". $db->quoteName('type') . " = " . $db->quote('component') .
		 				  "\n AND ". $db->quoteName('element') . " != " . $db->quote('com_jrealtimeanalytics') .
		 				  "\n ORDER BY text ASC";
		$db->setQuery($queryComponent);
		$options = $db->loadObjectList ();
		
		$noActiveOption = JHtml::_('select.option', '0', JText::_('COM_JREALTIME_SELECT_EXTENSION'));
		array_unshift($options, $noActiveOption);
		
		// Check for a database error.
		if ($db->getErrorNum ()) {
			return array();
		}
		
		return $options;
	}
}
