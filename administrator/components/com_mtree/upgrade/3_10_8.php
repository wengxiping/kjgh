<?php
/**
 * @package    Mosets Tree
 * @copyright    (C) 2019 Mosets Consulting. All rights reserved.
 * @license    GNU General Public License
 * @author    Lee Cher Yeong <mtree@mosets.com>
 * @url        http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_10_8 extends mUpgrade
{
    public function upgrade()
    {
        updateVersion(3, 10, 8);
        $this->updated = true;
        return true;
    }
}
