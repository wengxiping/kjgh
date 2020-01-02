<?php
// namespace components\com_jrealtimeanalytics\framework\html;
/**
 *
 * @package JREALTIMEANALYTICS::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Form Field for ip address
 *
 * @package JREALTIMEANALYTICS::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage html
 * @since 2.0
 */
class JFormFieldIpaddress extends JFormFieldText {
	protected function getInput() {
		// Translate placeholder text
		$hint = $this->translateHint ? JText::_ ( $this->hint ) : $this->hint;
		
		// Initialize some field attributes.
		$size = ! empty ( $this->size ) ? ' size="' . $this->size . '"' : '';
		$maxLength = ! empty ( $this->maxLength ) ? ' maxlength="' . $this->maxLength . '"' : '';
		$class = ! empty ( $this->class ) ? ' class="' . $this->class . '"' : '';
		$readonly = $this->readonly ? ' readonly' : '';
		$disabled = $this->disabled ? ' disabled' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$hint = $hint ? ' placeholder="' . $hint . '"' : '';
		$autocomplete = ! $this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
		$autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$spellcheck = $this->spellcheck ? '' : ' spellcheck="false"';
		$pattern = ! empty ( $this->pattern ) ? ' pattern="' . $this->pattern . '"' : '';
		$inputmode = ! empty ( $this->inputmode ) ? ' inputmode="' . $this->inputmode . '"' : '';
		$dirname = ! empty ( $this->dirname ) ? ' dirname="' . $this->dirname . '"' : '';
		$list = '';
		
		// Initialize JavaScript field attributes.
		$onchange = ! empty ( $this->onchange ) ? ' onchange="' . $this->onchange . '"' : '';
		
		// Including fallback code for HTML5 non supported browsers.
		JHtml::_ ( 'jquery.framework' );
		JHtml::_ ( 'script', 'system/html5fallback.js', false, true );
		
		// Get the field suggestions.
		if(method_exists($this, 'getSuggestions')) {
			$options = ( array ) $this->getSuggestions ();
			if (! empty ( $options )) {
				$html [] = JHtml::_ ( 'select.suggestionlist', $options, 'value', 'text', $this->id . '_datalist"' );
				$list = ' list="' . $this->id . '_datalist"';
			}
		}
		
		$html [] = '<input type="text" data-validation="ipaddress" name="' . $this->name . '" id="' . $this->id . '"' . $dirname . ' value="' . htmlspecialchars ( $this->value, ENT_COMPAT, 'UTF-8' ) . '"' . $class . $size . $disabled . $readonly . $list . $hint . $onchange . $maxLength . $required . $autocomplete . $autofocus . $spellcheck . $inputmode . $pattern . ' />';
		
		return implode ( $html );
	}
}
