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
 * A persistent storage class based on the APC cache, which is not
 * really very persistent, as soon as you restart your web server
 * the storage will be wiped, however for debugging and/or speed
 * it can be useful, and cache is a lot cheaper then storage.
 *
 * @author Chris Chabot <chabotc@google.com>
 */
class Google_Cache_Apc extends Google_Cache_Abstract
{
  public function __construct(Google_Client $client)
  {
    if (! function_exists('apc_add') ) {
      throw new Google_Cache_Exception("Apc functions not available");
    }
  }

   /**
   * @inheritDoc
   */
  public function get($key, $expiration = false)
  {
    $ret = apc_fetch($key);
    if ($ret === false) {
      return false;
    }
    if (is_numeric($expiration) && (time() - $ret['time'] > $expiration)) {
      $this->delete($key);
      return false;
    }
    return $ret['data'];
  }

  /**
   * @inheritDoc
   */
  public function set($key, $value)
  {
    $rc = apc_store($key, array('time' => time(), 'data' => $value));
    if ($rc == false) {
      throw new Google_Cache_Exception("Couldn't store data");
    }
  }

  /**
   * @inheritDoc
   * @param String $key
   */
  public function delete($key)
  {
    apc_delete($key);
  }
}
