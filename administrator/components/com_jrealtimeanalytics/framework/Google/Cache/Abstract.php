<?php
// namespace administrator\components\com_jrealtimeanalytics\framework\google;
/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

/**
 * Abstract storage class
 *
 * @author Chris Chabot <chabotc@google.com>
 */
abstract class Google_Cache_Abstract
{
  
  abstract public function __construct(Google_Client $client);

  /**
   * Retrieves the data for the given key, or false if they
   * key is unknown or expired
   *
   * @param String $key The key who's data to retrieve
   * @param boolean|int $expiration Expiration time in seconds
   *
   */
  abstract public function get($key, $expiration = false);

  /**
   * Store the key => $value set. The $value is serialized
   * by this function so can be of any type
   *
   * @param string $key Key of the data
   * @param string $value data
   */
  abstract public function set($key, $value);

  /**
   * Removes the key/data pair for the given $key
   *
   * @param String $key
   */
  abstract public function delete($key);
}
