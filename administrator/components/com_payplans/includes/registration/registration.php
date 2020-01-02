<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class PPRegistration extends PayPlans
{
	/**
	 * Proxy other event methods
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$adapter = $this->getAdapter();

		return call_user_func_array(array($adapter, $method), $args);
	}

	public function getAdapter()
	{
		static $adapter = null;

		if (is_null($adapter)) {
			$adapter = $this->getType();

			require_once(__DIR__ . '/adapters/' . $adapter . '.php');

			$className = 'PPRegistration' . ucfirst($adapter);

			$adapter = new $className();
		}

		return $adapter;
	}

	/**
	 * Retrieves a list of adapters on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAdapters()
	{
		static $adapters = null;

		if (is_null($adapters)) {
			$folder = __DIR__ . '/adapters';

			$adapters = JFolder::files($folder, '.php');

			foreach ($adapters as &$adapter) {
				$adapter = JFile::stripExt($adapter);
			}
		}

		return $adapters;
	}

	/**
	 * Remap the legacy registration name to the new one
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remapType($adapter)
	{
		$mapping = array('easysocialregistration' => 'easysocial');

		if (isset($mapping[$adapter])) {

			// Update the config
			$this->config->set('registrationType', $mapping[$adapter]);
			$data = $this->config->toArray();

			$model = PP::model('Config');
			$model->save($data);

			return $mapping[$adapter];
		}

		return $adapter;
	}

	/**
	 * Get the registration type
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function getType()
	{
		$type = $this->config->get('registrationType');
		$type = $this->remapType($type);

		return $type;
	}

	/**
	 * Determine if the registration type is built in
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function isBuiltIn()
	{
		$type = $this->getType();

		return $type == 'auto';
	}
}
