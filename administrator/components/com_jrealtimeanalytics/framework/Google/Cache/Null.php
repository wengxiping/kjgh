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
 * A blank storage class, for cases where caching is not
 * required.
 */
class Google_Cache_Null extends Google_Cache_Abstract
{
  public function __construct(Google_Client $client)
  {

  }

   /**
   * @inheritDoc
   */
  public function get($key, $expiration = false)
  {
    return false;
  }

  /**
   * @inheritDoc
   */
  public function set($key, $value)
  {
    // Nop.
  }

  /**
   * @inheritDoc
   * @param String $key
   */
  public function delete($key)
  {
    // Nop.
  }
}
