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

/**
 * EngageBox Migrator Class helps us fix and prevent backward compatibility issues between release updates.
 */
class EngageBoxMigrator
{
    /**
     * The database class
     *
     * @var object
     */
    private $db;

    /**
     * Indicates the current installed version of the extension
     *
     * @var string
     */
    private $installedVersion;
    
    /**
     * Class constructor
     *
     * @param string $installedVersion  The current extension version
     */
    public function __construct($installedVersion)
    {
        $this->db = JFactory::getDbo();
        $this->installedVersion = $installedVersion;
    }
    
    /**
     * Start the migration process
     *
     * @return void
     */
    public function start()
    {
        if (!$data = $this->getBoxes())
        {
            return;
        }

        foreach ($data as $key => $box)
        {   
            $box->params = json_decode($box->params);

            // Remove onLocationHash Trigger Point and respective textfield.
            if (version_compare($this->installedVersion, '3.4.8', '<=')) 
            {
                $this->removeOnLocationHashTriggerPoint($box);
            }

            // Remove onLocationHash Trigger Point and respective textfield.
            if (version_compare($this->installedVersion, '3.5.0', '<=')) 
            {
                $this->deprecateWelcomematOption($box);
            }
           
            // Update box using id as the primary key.
            $box->params = json_encode($box->params);
            $this->db->updateObject('#__rstbox', $box, 'id');
        }
    }

    /**
     * Get all boxes from the database
     *
     * @return array
     */
    private function getBoxes()
    {
        $db = $this->db;
    
        $query = $db->getQuery(true)
            ->select('*')
            ->from("#__rstbox");
        
        $db->setQuery($query);
    
        return $db->loadObjectList();
    }

    /**
     * Remove the onLocationHash Trigger point. Please, use the URL Assignment instead.
     *
     * @param object $box
     *
     * @return void
     */
    private function removeOnLocationHashTriggerPoint(&$box)
    {
        if ($box->triggermethod != 'hashtag')
        {
            return;
        }

        if (empty($box->params->hashtag) || !in_array($box->params->assign_urls, ['0', '1']))
        {
            return;
        }
        
        $box->triggermethod = 'pageload';
        $box->params->assign_urls_list .= "\n" . $box->params->hashtag;

        if ($box->params->assign_urls == '0')
        {
            $box->params->assign_urls = '1';
        }
    }

    /**
     * Since version 3.5.0 the welcomemat option has been deprecated. A new option called 'mode' is now used.
     *
     * @param  object $box
     *
     * @return void
     */
    private function deprecateWelcomematOption(&$box)
    {
        if (!isset($box->params->welcomemat) || $box->params->welcomemat == '0')
        {
            return;
        }

        $box->params->mode = 'pageslide';
    }
}

?>