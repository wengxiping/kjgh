<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/includes/model');

class PayplansModelConfig extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('config');
	}

	public function getConfig($reload = false)
	{
		static $configData = null;

		if (is_null($configData) || $reload) {

			$db = PP::db();
			$query = 'SELECT * FROM `#__payplans_config`';
			$db->setQuery($query);

			$items = $db->loadObjectList();

			if ($items) {
				$configData = array();

				foreach ($items as $item) {
					$configData[$item->key] = isset($item->value) ? $item->value : '';
				}
			}
		}

		return $configData;
	}

	public function save($data = array(), $pk = NULL, $new = false)
	{
		$keys = array_keys($data);

		$db = PP::db();
		$query = "DELETE FROM `#__payplans_config` WHERE `key` IN ('".implode("', '", $keys)."')" ;
		$db->setQuery($query);
		$db->query();

		$query = "INSERT INTO `#__payplans_config` (`key`, `value`) VALUES ";
		$queryValue = array();

		foreach ($data as $key => $value){
			if (is_array($value)) {
				$value = json_encode($value);
			}
			
			$queryValue[] = "(".$db->quote($key).",". $db->quote($value).")";
		}
		$query .= implode(",", $queryValue);

		$db->setQuery($query);
		return $db->query();
	}

	/**
	 * Allow caller to upload company logo
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function updateCompanyLogo($file)
	{
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];

		$path = '/images/payplans/companylogo.png';

		if (JFile::exists(JPATH_ROOT . $path)) {
			JFile::delete(JPATH_ROOT . $path);
		}

		$state = JFile::upload($source, JPATH_ROOT . $path);

		if (!$state) {
			$this->setError(JText::_('COM_PP_COMPANY_LOGO_UPLOAD_ERROR'));
			return false;
		}

		return $path;
	}

	/**
	 * Allow caller to upload company logo
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function removeCompanyLogo()
	{
		$path = JPATH_ROOT . '/images/payplans/companylogo.png';
		$exists = JFile::exists($path);

		if ($exists) {
			$state = JFile::delete($path);

			if ($state) {
				$this->save(array('companyLogo' => ''));
			}

			return $state;
		}

		return false;
	}

	// XITODO : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}
}

class PayplansModelformConfig extends PayPlansModelform {}
