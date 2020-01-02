<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2018 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_10_6 extends mUpgrade {

    function upgrade() {
        $database =& JFactory::getDBO();

        // Insert new sef_owner_page config
        $database->setQuery('INSERT IGNORE INTO `#__mt_config` VALUES(\'sef_owner_page\', \'sef\', \'opage\', \'opage\', \'text\', 1325, 1, 0)');
        $database->execute();

        $database->setQuery('INSERT IGNORE INTO `#__mt_config` VALUES(\'sef_owner_slug_type\', \'sef\', \'1\', \'1\', \'sef_owner_slug_type\', 130, 1, 0)');
        $database->execute();

        updateVersion(3,10,6);
        $this->updated = true;
        return true;
    }
}