<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPHelperMysqlquery extends PPHelperStandardApp
{
	/**
	 * Retrieves the query to be executed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getQuery($state)
	{
		$query = $this->params->get('queryOn' . ucfirst($state));

		return $query;
	}

	/**
	 * Retrieves the database connection
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDatabase()
	{
		$db = PP::db();

		if (!$this->useCurrentDatabase()) {

			$options = array(
				'host' => $this->params->get('db_host'),
				'database' => $this->params->get('db_name'),
				'user' => $this->params->get('db_username'),
				'password' => $this->params->get('db_password'),
				'prefix' => $this->params->get('table_prefix')
			);

		   $db = JDatabase::getInstance($options);
		}

		return $db;
	}

	/**
	 * Executes query
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function executeQuery(PPSubscription $subscription, $query)
	{
		if (!$query) {
			return false;
		}

		// Filter out mysql comments
		$query = PP::filterComments($query);

		//split input query in multiple queries
		$db = $this->getDatabase();
		$queries = $db->splitSql($query);

		$rewriter = PP::rewriter();

		foreach ($queries as $query) {
			$query = $rewriter->rewrite($query, $subscription);

			$db->setQuery($query);
			$state = $db->query();

			if (!$state) {
				$this->setError("MYSQL ERROR: " . $db->stderr());
			}
		}

		return true;	
	}

	/**
	 * Determines if we should connect to the current site's database
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function useCurrentDatabase()
	{
		return (bool) $this->params->get('use_default_db');
	}
}
