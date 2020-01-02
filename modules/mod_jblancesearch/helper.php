<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	29 March 2012
 * @file name	:	modules/mod_jblancesearch/helper.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

 // no direct access
 defined('_JEXEC') or die('Restricted access');
 include_once(JPATH_ADMINISTRATOR.'/components/com_jblance/helpers/jblance.php');	//include this helper file to make the class accessible in all other PHP files
 JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jblance/tables');

class ModJblanceSearchHelper {	

	public static function getListJobCateg($id_categ){
		$select = JblanceHelper::get('helper.select');		// create an instance of the class selectHelper
		$attribs = "class='input-large advancedSelect' size='1'";
		$lists = $select->getSelectCategoryTree('id_categ', $id_categ, 'MOD_JBLANCE_SEARCH_ALL', $attribs, '', true);
		return $lists;
	}
	
	public static function getSelectProjectStatus($status){
		$types[] = JHtml::_('select.option', '', JText::_('MOD_JBLANCE_SEARCH_ALL'));
		$types[] = JHtml::_('select.option', 'COM_JBLANCE_OPEN', JText::_('MOD_JBLANCE_OPEN'));
		$types[] = JHtml::_('select.option', 'COM_JBLANCE_FROZEN', JText::_('MOD_JBLANCE_FROZEN'));
		$types[] = JHtml::_('select.option', 'COM_JBLANCE_CLOSED', JText::_('MOD_JBLANCE_CLOSED'));
	
		$lists 	 = JHtml::_('select.genericlist', $types, 'status', 'class="input-large advancedSelect" size="1"', 'value', 'text', $status);
	
		return $lists;
	}
	
}
