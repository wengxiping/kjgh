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
 *  Engage Box Assignments Class
 */
class ebAssignments
{
	/**
	 *  Item
	 *
	 *  @var  object
	 */
	private $item;

	/**
	 *  Item params
	 *
	 *  @var  object
	 */
	private $params;

	/**
	 *  Local assignments list
	 *
	 *  @var  array
	 */
	private $assignments = array(
		"impressions",
		"cookietype",
        "offline",
        "onotherbox"
    );

	/**
	 *  Class Constructor
	 *
	 *  @param  object  $item  The object to be checked
	 */
	function __construct($item, $params)
	{
		if (!is_object($item) || !is_object($params))
		{
			return;
        }
        
        $this->item   = $item;
		$this->params = $params;
	}

	/**
     *  Pass all checks
     *
     *  @return  boolean  Returns true if all assignments pass
     */
    public function passAll()
    {
        // Temporary fix for the cookie assignment check
        // @TODO - cookietype field should be renamed to "assign_cookietype"
        $this->params->set("assign_cookietype", true);

        $pass = true;

        foreach ($this->assignments as $key => $assignment)
        {
            // Break if not passed
            if (!$pass)
            {
                break;
            }
            
            $method = "pass".$assignment;

            // Skip item if there is no assosiated method
            if (!method_exists($this, $method))
            {
                continue;
            }

            $assign = "assign_".$assignment;

            // Skip item if assignment is missing
            if (!$this->params->exists($assign))
            {
                continue;
            }

            $pass = $this->$method();
        }

        return $pass;
    }

    /**
     *  Pass Check for Offline Mode
     *
     *  @return  bool
     */
    private function passOffline()
    {
        // Skip check if offline mode is disabled
        if (!JFactory::getConfig()->get('offline', false))
        {
            return true;
        }

        $component   = JComponentHelper::getParams('com_rstbox');
        $globalState = $component->get("assign_offline", true);
        $boxState    = $this->params->get("assign_offline", null);

        return is_null($boxState) ? $globalState : $boxState;
    }

    /**
     *  Pass Check for Box Cookie
     *
     *  @return  bool
     */
    private function passCookieType()
    {
        // Skip if assignment is disabled
        if ($this->params->get("cookietype") == "never")
        {
            return true;
        }

        // Skip if a Super User is logged in
        if (JFactory::getUser()->authorise('core.admin'))
        {
            return true;
        }

        return EBHelper::boxHasCookie($this->item->id) ? false : true;
    }

    /**
     *  Checks box maximum impressions assignment
     *
     *  @return  boolean  Returns true if the assignment passes
     */
    private function passImpressions()
    {
        // Skip if assignment is disabled
        if (!$this->params->get("assign_impressions", false))
        {
            return true;
        }

        $period = $this->params->get("assign_impressions_param_type", "session");
        $limit  = (int) $this->params->get("assign_impressions_list");

        if ($limit == 0)
        {
            return;
        }

        $db = JFactory::getDBO();
        $date = JFactory::getDate();

        $query = $db->getQuery(true);

        $query
            ->select('COUNT(id)')
            ->from($db->quoteName('#__rstbox_logs'))
            ->where($db->quoteName('event') . ' = 1')
            ->where($db->quoteName('box') . ' = ' . $this->item->id);

        if ($period == "session")
        {
            $query->where($db->quoteName('sessionid') . ' = '. $db->quote(JFactory::getSession()->getId()));
        } else
        {
            $query->where($db->quoteName('visitorid') . ' = '. $db->quote(EBHelper::getVisitorID()));
        }

        switch ($period)
        {
            case 'day':
                $query->where('DATE(date) = ' . $db->quote($date->format("Y-m-d")));
                break;
            case 'week':
                $query->where('YEARWEEK(date, 3) = ' . $date->format("oW"));
                break;
            case 'month':
                $query->where('MONTH(date) = ' . $date->format("m"));
                $query->where('YEAR(date) = ' . $date->format("Y"));
                break;
        }

        $db->setQuery($query);

        $pass = (int) $limit > (int) $db->loadResult();

        return (bool) $pass;
    }

    /**
     * On User Viewed Another Box Asignment check
     * Checks if the user viewed any of the given boxes
     *
     * @return bool
     */
    private function passOnOtherBox()
    {
        // Skip if assignment is disabled
        if (!$this->params->get("assign_onotherbox", false))
        {
            return true;
        }

        // Skip assignment if the visitorID is not set
        $visitorID = EBHelper::getVisitorID();
        if (empty($visitorID))
        {
            return true;
        }

        $box_ids  = $this->params->get("assign_onotherbox_list");
        if (!is_array($box_ids) || empty($box_ids))
        {
            return true;
        }

        $box_ids = implode(',', $box_ids);
        
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query
            ->select('COUNT(id)')
            ->from($db->quoteName('#__rstbox_logs'))
            ->where($db->quoteName('event') . ' = 1')
            ->where($db->quoteName('box') . " IN ( $box_ids )")
            ->where($db->quoteName('visitorid') . ' = '. $db->quote($visitorID));
        
        $db->setQuery($query);

        $pass = (int) $db->loadResult();

        return (bool) $pass;
    }
}

?>
