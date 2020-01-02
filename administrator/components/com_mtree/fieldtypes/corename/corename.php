<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2011-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

use \Joomla\String\StringHelper;

class mFieldType_corename extends mFieldType {
	var $name = 'link_name';

	function getInputHTML()
	{
		$html = '<input'
				. ($this->isRequired() ? ' required':'')
				. $this->getDataValidatorAttr()
				. ' class="'.($this->isRequired() ? ' required':'')
				. '" type="text" name="' . $this->getInputFieldName(1)
				. '" id="' . $this->getInputFieldID(1)
				. '" size="' . ($this->getSize()?$this->getSize():'30');
		$html .= '" value="' . htmlspecialchars($this->getInputValue()) ;
		$html .= '" autofocus';
		$html .= ' />';
		return $html;
	}

	function getOutput($view=1) {
		$params['maxSummaryChars'] = intval($this->getParam('maxSummaryChars',55));
		$params['maxDetailsChars'] = intval($this->getParam('maxDetailsChars',0));
		$value = $this->getValue();
		$output = '';
		if($view == 1 AND $params['maxDetailsChars'] > 0 AND StringHelper::strlen($value) > $params['maxDetailsChars']) {
			$output .= StringHelper::substr($value,0,$params['maxDetailsChars']);
			$output .= '...';
		} elseif($view == 2 AND $params['maxSummaryChars'] > 0 AND StringHelper::strlen($value) > $params['maxSummaryChars']) {
			$output .= StringHelper::substr($value,0,$params['maxSummaryChars']);
			$output .= '...';
		} else {
			$output = $value;
		}
		return $output;
	}
}