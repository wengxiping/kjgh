<?php

/**
 * @package         EngageBox
 * @version         3.5.2 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemRstboxInstallerScript extends PlgSystemRstboxInstallerScriptHelper
{
	public $name = 'RSTBOXRENDER';
	public $alias = 'rstbox';
	public $extension_type = 'plugin';

	public function onAfterInstall()
	{
		// Render Plugin should be always ordered after Jooml Framework plugins 
		// such as T3 Framework, YT Framework and jQueryEasy
    	$this->pluginOrderAfter(array(
			"t3",
        	"jat3",
        	"jqueryeasy",
        	"yt",
        	"nrframework"
		));
	}

}
