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
 * Signs data.
 *
 * @author Brian Eaton <beaton@google.com>
 */
abstract class Google_Signer_Abstract
{
  /**
   * Signs data, returns the signature as binary data.
   */
  abstract public function sign($data);
}
