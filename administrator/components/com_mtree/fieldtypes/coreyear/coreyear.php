<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_coreyear extends mFieldType_year
{
	var $name = 'year';

	function getInputHTML()
	{
		if ($this->isUsingElements())
		{
			return mFieldType::getInputHTML();
		}

		return parent::getInputHTML();
	}

	function getSearchOptionsHtml($showSearchValue = false)
	{
		if ($this->isUsingElements())
		{
			return self::getSearchOptionsHtmlForElements($showSearchValue);
		}

		return parent::getSearchOptionsHtml($showSearchValue);
	}

	function getSearchOptionsHtmlForElements($showSearchValue = false) {

		$searchValue = $this->getSearchValue();

		$html = '<option value="">&nbsp;</option>';
		foreach( $this->arrayFieldElements AS $key => $value ) {
			$html .= '<option value="' . $value . '"';
			if(
					$showSearchValue
					&&
					isset($searchValue[$this->getSearchFieldName(1)])
					&&
					$searchValue[$this->getSearchFieldName(1)] == $value
			) {
				$html .= ' selected=selected';
			}
			$html .= '>';
			$html .= $value;
			$html .= '</option>';
		}

		return $html;
	}

	function isUsingElements()
	{
		if (count($this->arrayFieldElements) > 0 && !empty($this->arrayFieldElements[0])) {
			return true;
		}

		return false;
	}
}
