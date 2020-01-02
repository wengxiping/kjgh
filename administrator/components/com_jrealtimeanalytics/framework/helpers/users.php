<?php
// namespace administrator\components\com_jrealtimeanalytics\framework\helpers;
/**
 * @author Joomla! Extensions Store
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage helpers
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

/**
 * Helper static class
 * 
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage helpers
 * @since 2.0
 */
class JRealtimeHelpersUsers {
	/**
	 * Cross system filemtime no bugged
	 * 
	 * @access private
	 * @param string $filePath        	
	 * @return int
	 */
	public static function crossFileMTime($filePath) {
		$time = filemtime ( $filePath );
		
		$isDST = (date ( 'I', $time ) == 1);
		$systemDST = (date ( 'I' ) == 1);
		
		$adjustment = 0;
		
		if ($isDST == false && $systemDST == true)
			$adjustment = 3600;
		
		else if ($isDST == true && $systemDST == false)
			$adjustment = - 3600;
		
		else
			$adjustment = 0;
		
		return ($time + $adjustment);
	}
	
	/**
	 * Effettua un reverse reduce sul'ID di sessione MD5 per arrivare
	 * ad una stringa da appendere al prefix del name assegnato ai guest users
	 *
	 * @access public
	 * @static
	 *
	 * @param string $sessionID        	
	 * @param Object $cParams        	
	 * @return string
	 */
	public static function generateRandomGuestNameSuffix($sessionID, $cParams) {
		// Recuperiamo la parte numerica dell'hash in base 36
		preg_match_all ( '/\d/i', $sessionID, $matches );
		
		if (is_array ( $matches [0] ) && count ( $matches [0] )) {
			$numericHashArray = ( float ) (implode ( '', $matches [0] ));
		}
		
		$appendHashSuffix = '_' . $numericHashArray;
		// Limitiamo a 4 cifre il numeric suffix
		$appendHashSuffix = $cParams->get ( 'guestprefix', 'Visitor' ) . substr ( $appendHashSuffix, 0, 5 );
		
		return $appendHashSuffix;
	}
	
	/**
	 * Get names for users based on current state, logged or guest
	 *
	 * @access public
	 * @static
	 *
	 * @param string $sessionIDFrom        	
	 * @param string $sessionIDTo        	
	 * @param Object $componentParams        	
	 * @return array
	 */
	public static function getActualNames($sessionIDFrom, $sessionIDTo, $componentParams) {
		// Load user table
		$userTable = JTable::getInstance ( 'user' );
		
		// Load user session table
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jrealtimeanalytics/tables');
		$userSessionTable = JTable::getInstance('Session', 'JRealtimeTable');
		
		// Current chosen user field name
		$userFieldName = $componentParams->get ( 'usefullname', 'username' );
		
		// Sender actualfrom
		$userSessionTable->load ( $sessionIDFrom );
		$userTable->load ( $userSessionTable->userid );
		$actualFrom = $userTable->{$userFieldName};
		if (! $actualFrom) {
			$actualFrom = self::generateRandomGuestNameSuffix ( $sessionIDFrom, $componentParams );
		}
		
		// Receiver actualto
		$receiverSessionTable = clone ($userSessionTable);
		$receiverSessionTable->load ( $sessionIDTo );
		$userTable->load ( $receiverSessionTable->userid );
		$actualTo = $userTable->{$userFieldName};
		if (! $actualTo) {
			$actualTo = self::generateRandomGuestNameSuffix ( $receiverSessionTable->session_id, $componentParams );
		}
		
		$result = array ();
		$result ['fromActualName'] = $actualFrom;
		$result ['toActualName'] = $actualTo;
		
		return $result;
	}
	
	/**
	 * Return current user session table object with singleton
	 * 
	 * @access private
	 * @static
	 *
	 * @return Object
	 */
	public static function getSessionTable() {
		// Lazy loading user session
		static $userSessionTable;
		
		if(!is_object($userSessionTable)) {
			JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jrealtimeanalytics/tables');
			$userSessionTable = JTable::getInstance('Session', 'JRealtimeTable');
			$userSessionTable->load(session_id());
		}
		
		return $userSessionTable;
	}
	
	/**
	 * Singleton for session object
	 * 
	 * @static
	 *
	 *
	 * @access private
	 * @return Object
	 */
	public static function getEmptySessionTable() {
		static $sessionTable;
		
		if (! is_object ( $sessionTable )) {
			$sessionTable = JTable::getInstance ( 'session' );
		}
		
		return $sessionTable;
	}
}