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

PP::import('admin:/includes/model');

class PayplansModelLanguages extends PayPlansModel
{
	protected $data = null;
	protected $pagination = null;
	protected $total = null;

	public function __construct()
	{
		parent::__construct('languages');
	}

	/**
	 * Purges non installed languages
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purge()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'DELETE FROM ' . $db->quoteName('#__payplans_languages');
		$query[] = 'WHERE ' . $db->quoteName('state') . ' = ' . $db->Quote(PP_LANGUAGES_NOT_INSTALLED);

		$db->setQuery($query);

		return $db->Query();
	}

	/**
	 * Retrieve languages
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLanguages()
	{
		static $data = null;

		if (!$data) {
			$db = PP::db();

			$query = 'SELECT * FROM ' . $db->quoteName('#__payplans_languages');

			$db->setQuery($query);
			$data = $db->loadObjectList();
		}

		return $data;
	}

	/**
	 * Discover new languages
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function discover()
	{
		$config = PP::config();
		$key = $config->get('main_apikey');

		$connector = PP::connector();
		$connector->addUrl(PP_LANGUAGES_SERVER);
		$connector->addQuery('key', $key);
		$connector->setMethod('POST');
		$connector->execute();

		$contents = $connector->getResult(PP_LANGUAGES_SERVER);

		if (!$contents) {
			$result = new stdClass();
			$result->message = 'No language found';
			return $result;
		}

		// Decode the result
		$result	= json_decode($contents);

		if ($result->code != 200) {
			$return = base64_encode('index.php?option=com_payplans&view=languages');

			return $result;
		}

		foreach ($result->languages as $language) {

			$table = PP::table('Language');
			$exists = $table->load(array('locale' => $language->locale));

			// We do not want to bind the id
			unset($language->id);

			// Since this is the retrieval, the state should always be disabled
			if (!$exists) {
				$table->state = PP_LANGUAGES_NOT_INSTALLED;
			}

			// Then check if the language needs to be updated. If it does, update the ->state to PP_LANGUAGES_NEEDS_UPDATING
			// We need to check if the language updated time is greater than the local updated time
			if ($exists && $table->state == PP_LANGUAGES_INSTALLED) {
				$languageTime = strtotime($language->updated);
				$localLanguageTime = strtotime($table->updated);

				if ($languageTime > $localLanguageTime && $table->state == PP_LANGUAGES_INSTALLED) {
					$table->state = PP_LANGUAGES_NEEDS_UPDATING;
				}
			}


			$table->title = $language->title;
			$table->locale = $language->locale;
			$table->translator = $language->translator;
			$table->updated = $language->updated;
			$table->progress = $language->progress;

			$params = new JRegistry();
			$params->set('download' , $language->download);
			$params->set('md5', $language->md5);
			$table->params = $params->toString();

			$table->store();
		}

		return true;
	}

	/**
	 * Determines if the language rows has been populated
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initialized()
	{
		$db = PP::db();
		$query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__payplans_languages');
		$db->setQuery($query);

		$initialized = $db->loadResult() > 0;

		return $initialized;
	}
}