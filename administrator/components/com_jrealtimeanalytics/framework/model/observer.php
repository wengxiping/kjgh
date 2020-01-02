<?php
// namespace components\com_jrealtimeanalytics\libraries\framework\model;
/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage model
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Abstract class for Observer concrete models, it needs to be derived
 * and implemented the update method by single concrete SMVC models
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage model
 * @since 2.0
 */
abstract class JRealtimeModelObserver extends JRealtimeModel {
	/**
	 * Observable object subject
	 *
	 * @var object
	 */
	protected $subject = null;
	
	/**
	 * Method to update the state of the observable object
	 *
	 * @param IObservableModel $subject
	 * @return mixed If some exceptions occur return an Exception object otherwise boolean true
	 */
	public abstract function update(IObservableModel $subject);
}
