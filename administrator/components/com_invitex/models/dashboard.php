<?php
/**
 * @version     SVN: <svn_id>
 * @package     Invitex
 * @subpackage  mod_inviters
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * model for dashboard
 *
 * @package     Invitex
 * @subpackage  mod_inviter
 * @since       3.1.4
 */
class InvitexModelDashboard extends JModelLegacy
{
	protected $downloadid;

	protected $extensionsDetails;

	/**
	 * construtor function
	 *
	 */
	public function __construct()
	{
		$this->db = JFactory::getDbo();

		// Get download id
		$params     = JComponentHelper::getParams('com_invitex');
		$this->downloadid = $params->get('downloadid');

		// Setup vars
		$this->extensionsDetails = new stdClass;
		$this->extensionsDetails->extension        = 'com_invitex';
		$this->extensionsDetails->extensionElement = 'com_invitex';
		$this->extensionsDetails->extensionType    = 'component';
		$this->extensionsDetails->updateStreamName = 'InviteX';
		$this->extensionsDetails->updateStreamType = 'extension';
		$this->extensionsDetails->updateStreamUrl  = 'https://techjoomla.com/updates/stream/invitex.xml?format=xml';
		$this->extensionsDetails->downloadidParam  = 'downloadid';

		parent::__construct();
	}

	/**
	 * Function to get line chart value
	 *
	 * @return  void
	 */
	public function getLineChartValues()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$post = $input->post->getArray();

		if (isset($post['todate']))
		{
			$to_date = $post['todate'];
		}
		else
		{
			$to_date = date('Y-m-d');
		}

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		if (strtotime($from_date) == strtotime($to_date))
		{
			// Add 1 day to to_date
			$to_date = date('Y-m-d', strtotime($to_date . ' + 1 day'));
		}

		$diff = strtotime($to_date) - strtotime($from_date);
		$days = round($diff / 86400);
		$days_arr = array();

		$que = "SELECT invites_count ,date FROM #__invitex_imports
			WHERE date>=" . strtotime($from_date);
			$this->db->setQuery($que);

		try
		{
			$sent_result = $this->db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('COM_INVITEX_SOMETHING_WENT_WRONG'), 'error');
		}

		$que = "SELECT id,modified FROM #__invitex_imports_emails where modified >= '" . strtotime($from_date) . "'
		AND invitee_id <> 0 && friend_count <> 0";
		$this->db->setQuery($que);

		try
		{
			$acc_result = $this->db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('COM_INVITEX_SOMETHING_WENT_WRONG'), 'error');
		}

		for ($i = 0;$i <= $days;$i++)
		{
			$ondate = date('Y-m-d', strtotime($from_date . ' +  ' . $i . 'days'));
			$line_chart['line_chart'][$i] = new stdClass;
			$line_chart['line_chart'][$i]->date = $ondate;
			$sent = 0;
			$acc = 0;

			foreach ($sent_result as $k => $v)
			{
				if ($ondate === date('Y-m-d', $v->date))
				{
					$sent += $v->invites_count;
				}
			}

			$line_chart['line_chart'][$i]->sent = $sent;

			foreach ($acc_result as $k => $v)
			{
				if ($ondate === date('Y-m-d', $v->modified))
				{
					$acc++;
				}
			}

			$line_chart['line_chart'][$i]->accepted = $acc;
		}

		return $line_chart['line_chart'];
	}

	/**
	 * Function to get data for pie chart
	 *
	 * @return  void
	 */
	public function getstatsforpie()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		if (isset($post['todate']))
		{
			$to_date = $post['todate'];
		}
		else
		{
			$to_date = date('Y-m-d');
		}

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		if (strtotime($from_date) == strtotime($to_date))
		{
			// Add 1 day to to_date
			$to_date = date('Y-m-d', strtotime($to_date . ' + 1 day'));
		}

		$st = array();
		$this->db	= JFactory::getDbo();
		$query = "SELECT COUNT(invitee_id) as acc
							FROM #__invitex_imports_emails
							WHERE invitee_id<>0 && friend_count <> 0";
		$this->db->setQuery($query);
		$acct = $this->db->loadResult();
		$query = "SELECT COUNT(invitee_email) as invcount
							FROM #__invitex_imports_emails WHERE invitee_id=0";
		$this->db->setQuery($query);
		$nacct = $this->db->loadResult();
		$st = array($acct, $nacct);

		return $st;
	}

	/**
	 * Function to get data for stats chart
	 *
	 * @return  void
	 */
	public function getstatsforpiemethod()
	{
			$input = JFactory::getApplication()->input;
			$post = $input->getArray($_POST);

			if (isset($post['todate']))
			{
				$to_date = $post['todate'];
			}
			else
			{
				$to_date = date('Y-m-d');
			}

			if (isset($post['fromdate']))
			{
				$from_date = $post['fromdate'];
			}
			else
			{
				$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
			}

			if (strtotime($from_date) == strtotime($to_date))
			{
				// Add 1 day to to_date
				$to_date = date('Y-m-d', strtotime($to_date . ' + 1 day'));
			}

			$st = array();
			$this->db	= JFactory::getDbo();

			// Get all tecjoomlaAPI enabled plugins
			$enabledPlugins = JPluginHelper::getPlugin('techjoomlaAPI');

			$pro_method = array('0' => 'SEND_MANUAL');

			if (!empty($enabledPlugins))
			{
				foreach ($enabledPlugins as $enabledPlugin)
				{
					$pro_method[] = $enabledPlugin->name;
				}
			}

			$sent_per_method = 0;
			$acc_per_method = 0;

			foreach ($pro_method as $method)
			{
					$que = "SELECT sum(invites_count) FROM #__invitex_imports
					WHERE provider_email='" . $method . "'  AND DATE(FROM_UNIXTIME(date))>='" . $from_date . "' AND DATE(FROM_UNIXTIME(date))<='" . $to_date . "'";

					$this->db->setQuery($que);

					$results = $this->db->loadResult();

					if (!empty($results))
					{
						$sent[$method] = $results;
					}
					else
					{
						$sent[$method] = 0;
					}

					$sent_per_method = $sent_per_method + $sent[$method];

					$query = "SELECT if(count(iie.invitee_email),count(iie.invitee_email),'0') as acc FROM
					#__invitex_imports_emails as iie LEFT JOIN #__invitex_imports AS ii on iie.import_id =ii.id
					where provider_email='" . $method . "'  AND DATE(FROM_UNIXTIME(modified))>='" . $from_date . "' AND
					DATE(FROM_UNIXTIME(modified))<='" . $to_date . "' AND iie.invitee_id<>0 && iie.friend_count <> 0";
					$this->db->setQuery($query);
					$acc[$method] = $this->db->loadResult();

					$acc_per_method = $acc_per_method + $acc[$method];
			}

		$que = "SELECT sum(invites_count) FROM #__invitex_imports
					WHERE DATE(FROM_UNIXTIME(date))>='" . $from_date . "' AND DATE(FROM_UNIXTIME(date))<='" . $to_date . "'";
		$this->db->setQuery($que);
		$total_sent = $this->db->loadResult();
		$sent['other'] = $total_sent - $sent_per_method;

		$que = "SELECT if(count(iie.invitee_email),count(iie.invitee_email),'0') as total_acc FROM
		#__invitex_imports_emails as iie where DATE(FROM_UNIXTIME(iie.modified))>='" . $from_date . "' AND
		DATE(FROM_UNIXTIME(iie.modified))<='" . $to_date . "' AND iie.invitee_id<>0 && iie.friend_count <> 0";
		$this->db->setQuery($que);
		$total_acc = $this->db->loadResult();
		$acc['other'] = $total_acc - $acc_per_method;

		return array($sent, $acc);
	}

	/**
	 * Function to get count of invites
	 *
	 * @return  void
	 */
	public function getAll_time_invites_count()
	{
		$query = $this->db->getQuery(true);
		$query->select('sum(invites_count)');
		$query->from('`#__invitex_imports`');
		$this->db->setQuery($query);

		return $invites_cnt = $this->db->loadResult();
	}

	/**
	 * Function to get count of invites accepted count
	 *
	 * @return  void
	 */
	public function getAll_time_invites_accepted_count()
	{
		$query = $this->db->getQuery(true);
		$query->select('count(id)');
		$query->from('`#__invitex_imports_emails` ');
		$query->where('invitee_id<>0 AND friend_count<>0 ');
		$this->db->setQuery($query);

		return $inviters_cnt	=	$this->db->loadResult();
	}

	/**
	 * Function to get inviters count
	 *
	 * @return  void
	 */
	public function getInviters_count()
	{
		$query = $this->db->getQuery(true);
		$query->select('count(distinct(inviter_id))');
		$query->from('`#__invitex_imports` WHERE inviter_id<>0');
		$this->db->setQuery($query);

		return $invites_cnt = $this->db->loadResult();
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  void
	 */
	public function refreshUpdateSite()
	{
		// Trigger plugin
		$dispatcher  = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system', 'tjupdates');
		$dispatcher->trigger('refreshUpdateSite', array($this->extensionsDetails));
	}

	/**
	 * Function to get latest version of invitex
	 *
	 * @return  void
	 */
	public function getLatestVersion()
	{
		// Trigger plugin
		$dispatcher  = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system', 'tjupdates');

		$latestVersion = $dispatcher->trigger('getLatestVersion', array($this->extensionsDetails));

		return (isset($latestVersion[0]) ? $latestVersion[0] : false);
	}
}
