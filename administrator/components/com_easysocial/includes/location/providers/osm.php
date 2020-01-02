<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/location/provider');

class SocialLocationProvidersOsm extends SociallocationProviders
{
	protected $queries = array(
		'lat' => '',
		'lon' => '',
		'q' => ''
	);

	public $url = 'https://nominatim.openstreetmap.org';

	public function __construct()
	{
		parent::__construct();
	}

	public function setCoordinates($lat, $lng)
	{
		return $this->setQuery('lat', $lat) && $this->setQuery('lon', $lng);
	}

	public function setSearch($search = '')
	{
		return $this->setQuery('q', $search);
	}

	public function getResult($queries = array())
	{
		$this->setQueries($queries);

		$options = array();

		$type = 'reverse';

		if (!empty($this->queries['q'])) {
			$options['q'] = $this->queries['q'];
			$type = 'search';
		} else {
			$options['lat'] = $this->queries['lat'];
			$options['lon'] = $this->queries['lon'];
		}

		$connector = ES::connector();
		$connector->setMethod('GET');
		$connector->addUrl($this->url . '/' . $type . '?format=json&addressdetails=1&' . http_build_query($options));
		$connector->execute();

		$result = $connector->getResult();

		$result = json_decode($result);

		if (empty($result) || isset($result->error)) {
			$error = isset($result->message) ? $result->message : JText::_('COM_EASYSOCIAL_LOCATION_PROVIDERS_OSM_UNKNOWN_ERROR');

			$this->setError($error);
			return $error;
		}

		$result = is_array($result) ? $result : array($result);
		$venues = array();

		foreach ($result as $row) {
			$obj = new SocialLocationData;
			$obj->latitude = $row->lat;
			$obj->longitude = $row->lon;
			$obj->name = $row->display_name;
			$obj->address = $row->address;
			$obj->formatted_address = $row->display_name;

			$venues[] = $obj;
		}

		return $venues;
	}
}
