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
 * Observable model responsibilities
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage model
 * @since 2.0
 */
interface IObservableModel {
	/**
	 * Update each attached observer object and return an array of their return values
	 *
	 * @return array Array of return values from the observers
	 */
	public function notify();
	
	/**
	 * Attach an observer object
	 *
	 * @param JRealtimeModelObserver $observer
	 *        	An observer object to attach
	 *        	
	 * @return void
	 */
	public function attach(JRealtimeModelObserver $observer);
	
	/**
	 * Detach an observer object
	 *
	 * @param JRealtimeModelObserver $observer
	 *        	An observer object to detach.
	 *        	
	 * @return boolean True if the observer object was detached.
	 */
	public function detach(JRealtimeModelObserver $observer);
	
	/**
	 * Method to get model state variables
	 *
	 * @param string $property        	
	 * @param mixed $default        	
	 *
	 * @return object The property where specified, the state object where omitted
	 */
	public function getState($property = null, $default = null);
	
	/**
	 * Method to set model state variables
	 *
	 * @param string $property        	
	 * @param mixed $value        	
	 *
	 * @return mixed The previous value of the property or null if not set.
	 */
	public function setState($property, $value = null);
}

/**
 * Observable model concrete class, can be derived by SMVC models and
 * his interface will be used by root controller to attach observers
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage model
 * @since 2.0
 */
class JRealtimeModelObservable extends JRealtimeModel implements IObservableModel {
	/**
	 * An array of Observer objects to notify
	 *
	 * @access protected
	 * @var array
	 */
	protected $observers = array ();
	
	/**
	 * Update each attached observer object and return an array of their return values
	 *
	 * @return array Array of return values from the observers
	 */
	public function notify() {
		// Iterate through the _observers array
		foreach ( $this->observers as $observer ) {
			$return [] = $observer->update ($this);
		}
		
		return $return;
	}
	
	/**
	 * Attach an observer object
	 *
	 * @param JRealtimeModelObserver $observer
	 *        	An observer object to attach
	 *        	
	 * @return void
	 */
	public function attach(JRealtimeModelObserver $observer) {
		// Make sure we haven't already attached this object as an observer
		$class = get_class ( $observer );
		
		foreach ( $this->observers as $check ) {
			if ($check instanceof $class) {
				return;
			}
		}
		
		// Assign this observer to Observable collection aggregation
		$this->observers [] = $observer;
	}
	
	/**
	 * Detach an observer object
	 *
	 * @param JRealtimeModelObserver $observer
	 *        	An observer object to detach.
	 *        	
	 * @return boolean True if the observer object was detached.
	 */
	public function detach(JRealtimeModelObserver $observer) {
		$retval = false;
		
		$key = array_search ( $observer, $this->observers );
		
		if ($key !== false) {
			unset ( $this->observers [$key] );
			$retval = true;
		}
		
		return $retval;
	}
}