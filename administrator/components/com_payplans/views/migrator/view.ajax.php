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

class PayplansViewMigrator extends PayPlansAdminView
{
	/**
	 * Import sample data
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function importSampleData()
	{
		$sampleType = $this->input->get('type', 'basic');

		// Truncate all the payplans table first
		$this->truncateTables();

		// Get the file path
		$path = PP_DEFAULTS . '/sampledata/sample' . ucfirst($sampleType) . '.sql';

		// Import the data
		$this->import($path);

		return $this->ajax->resolve();
	}

	/**
	 * Import data from SQL file
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function import($filePath)
	{
		$db	= PP::db();
		
		// Read file
		$sql = JFile::read($filePath);

		// Clean comments
		$sql = preg_replace("!/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/!s", "", $sql);

		//break into queries
		$queries = $db->splitSql($sql);

		//run queries
		foreach ($queries as $query) {
			
			// filter whitespace
			$query = trim($query,"\n\r\t");
			$query = preg_replace(array('/^\s+/', '/\s+\$/'), array('', ''), $query);

			//if query is blank
			if (empty($query)) {
				continue;
			}

			$db->setQuery($query);
			// dump($db->query());
			$db->query();
		}

		return true;
	}

	/**
	 * Allows caller to truncate table
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	private function truncateTables()
	{
		$db = PP::db();

		$db->truncateTable('#__payplans_plan');
		$db->truncateTable('#__payplans_app');
		$db->truncateTable('#__payplans_planapp');
		$db->truncateTable('#__payplans_order');
		$db->truncateTable('#__payplans_subscription');
		$db->truncateTable('#__payplans_payment');
		$db->truncateTable('#__payplans_group');
		$db->truncateTable('#__payplans_plangroup');
		$db->truncateTable('#__payplans_config');
		$db->truncateTable('#__payplans_invoice');
		$db->truncateTable('#__payplans_transaction');
		$db->truncateTable('#__payplans_modifier');
		$db->truncateTable('#__payplans_resource');
		$db->truncateTable('#__payplans_statistics');
		$db->truncateTable('#__payplans_log');
	}
}