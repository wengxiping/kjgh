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

use NRFramework\Cache;

class eBoxLog 
{
	/**
	 *  List of valid event IDs
	 *
     *  1 = Impressions
     *  2 = Closes
     *  3 = Engage
	 */
	private $events = array(1);

    /**
     *  Logs table
     *
     *  @var  string
     */
    private $table = '#__rstbox_logs';
	
    /**
     *  Logs box events to the database
     *
     *  @param   integer  $boxid    The box id
     *  @param   integer  $eventid  Event id
     *
     *  @return  bool     Returns a boolean indicating if the event logged successfully
     */
    function track($boxid, $eventid = 1)
    {
    	// Making sure we have a valid Boxid and Eventid
        if (!$boxid || !$eventid || !in_array($eventid, $this->events))
        {
            return;
        }

        // Get visitor's token id
        if (!$visitorID = EBHelper::getVisitorID())
        {
        	return;
        }

        // Everything seems OK. Let's save data to db.
        $data = new stdClass();

        $data->sessionid = JFactory::getSession()->getId();
        $data->user      = JFactory::getUser()->id;
        $data->visitorid = $visitorID;
        $data->box       = $boxid;
        $data->event     = $eventid;
        $data->date      = JFactory::getDate()->toSql();
         
        // Insert the object into the user profile table.
        try
        {
            return JFactory::getDbo()->insertObject($this->table, $data);
        } 
        catch (Exception $e)
        {
            
        }
    }

    /**
     *  Removes old rows from the logs table
     *  Runs every 12 hours with a self-check
     *
     *  @return void
     */
    function clean()
    {
        $hash = md5('eboxclean');

        if (Cache::read($hash, true))
        {
            return;
        }

        // Removes rows older than x days
        $days = JComponentHelper::getParams('com_rstbox')->get('statsdays', 90);

        $db = JFactory::getDbo();
         
        $query = $db->getQuery(true);
        $query
            ->delete($db->quoteName($this->table))
            ->where($db->quoteName('date') . ' < DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)');
         
        $db->setQuery($query);
        $db->execute();

        // Write to cache file
        Cache::write($hash, 1, 720);

        return true;
    }
}

?>