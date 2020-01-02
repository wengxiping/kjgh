<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_7_1 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Use placeholder for mselectmultiple and multipledates fieldtype.
		$database->setQuery( "UPDATE `#__mt_fieldtypes` SET use_placeholder = 1 WHERE field_type IN ('mselectmultiple', 'multipledates');" );
		$database->execute();

		updateVersion(3,7,1);
		$this->updated = true;
		return true;
	}
}

