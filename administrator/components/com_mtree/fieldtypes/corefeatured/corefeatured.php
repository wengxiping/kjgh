<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2011-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_corefeatured extends mFieldType_radiobutton {
	var $name = 'link_featured';
	var $numOfInputFields = 1;

	function getOutput($view=1) {
		$featured = $this->getValue();
		$html = '';
		if($featured) {
			$html .= JText::_( 'JYES' );
		} else {
			$html .= JText::_( 'JNO' );
		}
		return $html;
	}

	function getInputHTML()
	{
		$value = $this->getInputValue();
		$html = '';
		$i = 0;

		$this->arrayFieldElements = array(
				1 => JText::_( 'JYES' ),
				0 => JText::_( 'JNO' )
		);

		$html .= '<ul>';

		foreach($this->arrayFieldElements AS $fieldElement => $caption)
		{
			$html .= '<li style="background-image:none;padding:0">';
			$html .= '<label for="' . $this->getInputFieldID(1) . '_' . $i . '" class="radio">';
			$html .= '<input'
					. ($this->isRequired() ? ' required':'')
					. $this->getDataValidatorAttr()
					. ' type="radio" name="' . $this->getInputFieldName(1)
					. '" value="'.htmlspecialchars($fieldElement)
					. '" id="' . $this->getInputFieldID(1) . '_' . $i . '" ';

			if( $fieldElement == $value )
			{
				$html .= 'checked ';
			}

			$html .= '/>';
			$html .= $caption;
			$html .= '</label>';
			$html .= '</li>';
			$i++;
		}

		$html .= '</ul>';

		return $html;
	}

	function getSearchHTML( $showSearchValue=false, $showPlaceholder=false, $idprefix='search_' ) {
		$searchValue = $this->getSearchValue();

		$options = array(
			''	=> '',
			'1'	=> JText::_( 'FLD_COREFEATURED_FEATURED_ONLY' ),
			'0'	=> JText::_( 'FLD_COREFEATURED_NON_FEATURED_ONLY' )
		);
		
		$html = '';
		$html = '<select name="' . $this->getSearchFieldName(1) . '">';
		foreach( $options AS $key => $value ) {
			$html .= '<option value="'.$key.'"';
			if( 
				($showSearchValue && $searchValue !== false && $key == $searchValue)
				||
				($searchValue === false && $key === '')
			 ) {
				$html .= ' selected=selected';
			}
			$html .= '>';
			$html .= $value;
			$html .= '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	function getWhereCondition() {
		$args = func_get_args();

		$fieldname = $this->getName();
		
		if(  is_numeric($args[0]) ) {
			switch($args[0]) {
				case -1:
				case '':
					return null;
					break;
				case 1:
					return $fieldname . ' = 1';
					break;
				case 0:
				return $fieldname . ' = 0';
					break;
			}
		} else {
			return null;
		}
	}
}
