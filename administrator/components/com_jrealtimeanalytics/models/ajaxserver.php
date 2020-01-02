<?php
//namespace components\com_jrealtimeanalytics\models; 
/** 
 * @package JREALTIMEANALYTICS::AJAXSERVER::components::com_jrealtimeanalytics 
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C)2014 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

/**
 * Ajax Server model responsibilities
 *
 * @package JREALTIMEANALYTICS::AJAXSERVER::components::com_jrealtimeanalytics  
 * @subpackage models
 * @since 2.0
 */
interface IAjaxserverModel {
	public function loadAjaxEntity($id, $param, $DIModels) ;
}

/** 
 * Classe che gestisce il recupero dei dati per il POST HTTP
 * @package JREALTIMEANALYTICS::AJAXSERVER::components::com_jrealtimeanalytics  
 * @subpackage models
 * @since 1.0
 */
class JRealtimeModelAjaxserver extends JRealtimeModel implements IAjaxserverModel {
	/**
	 * Get license informations about this user subscription license email code
	 * Use the RESTFul interface API on the remote License resource
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function getLicenseStatus($additionalModels = null) {
		// Get email license code
		$code = JComponentHelper::getParams('com_jrealtimeanalytics')->get('registration_email', null);
	
		// Instantiate HTTP client
		$HTTPClient = new JRealtimeHttp();
	
		/*
		 * Status domain code
		 * Remote API Call
		 */
		$headers = array('Accept'=>'application/json', 'User-agent' => 'JRealtime Analytics updater');
		if($code) {
			try {
				$prodCode = 'jrealtime';
				$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
				$HTTPResponse = $HTTPClient->get($cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet') . "/option,com_easycommerce/action,licenseCode/email,$code/productcode,$prodCode", $headers);
			} catch (Exception $e) {
				$HTTPResponse = new stdClass();
				$HTTPResponse->body = '{"success":false,"reason":"connection_error","details":"' . $e->getMessage() . '"}';
			}
		} else {
			$HTTPResponse = new stdClass();
			$HTTPResponse->body = '{"success":false,"reason":"nocode_inserted"}';
		}
			
		// Deserializing della response
		try {
			$objectHTTPResponse = json_decode($HTTPResponse->body);
			if(!is_object($objectHTTPResponse)) {
				throw new Exception('decoding_error');
			}
		} catch (Exception $e) {
			$HTTPResponse = new stdClass();
			$HTTPResponse->body = '{"success":false,"reason":"' . $e->getMessage() . '"}';
			$objectHTTPResponse = json_decode($HTTPResponse->body);
		}
	
		return $objectHTTPResponse;
	}
	
	/**
	 * Perform the asyncronous update of the component
	 * 1- Dowload the remote update package file
	 * 2- Use the Joomla installer to install it
	 * 3- Return status to the js app
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function downloadComponentUpdate($additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();
		$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
		$ep = $cdFuncUsed('uggc' . '://' . 'fgberwrkgrafvbaf' . '.bet' . '/XZY1306TQUOnifs3243564832kfuxnj35td1rtt1286f.ugzy');
		$file_path = JFactory::getConfig()->get('tmp_path', '/tmp') . '/KML1306GDHBavsf3243564832xshkaw35gq1egg1286s.zip';
	
		try {
			// Ensure CURL support
			if (! function_exists ( 'curl_init' )) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_CURL_NOT_SUPPORTED' ), 'error' );
			}
	
			// Firstly test if the server is up and HTTP 200 OK
			$ch = curl_init($ep);
			curl_setopt( $ch, CURLOPT_NOBODY, true );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
			curl_exec( $ch );
	
			$headerInfo = curl_getinfo( $ch );
			curl_close( $ch );
			if($headerInfo['http_code'] != 200 || !$headerInfo['download_content_length']) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_ERROR_DOWNLOADING_REMOTE_FILE' ), 'error' );
			}
	
			// 1- Download the remote update package file and put in local file
			$fp = fopen ($file_path, 'w+');
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $ep );
			curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 60 );
			curl_setopt( $ch, CURLOPT_FILE, $fp );
			curl_exec( $ch );
			curl_close( $ch );
			fclose( $fp );
	
			if (!filesize($file_path)) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_ERROR_WRITING_LOCAL_FILE' ), 'error' );
			}
	
			// All went well
			$response->result = true;
		} catch ( JRealtimeException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getErrorLevel();
			return $response;
		} catch ( Exception $e ) {
			$jrealtimeException = new JRealtimeException ( JText::sprintf ( 'COM_JREALTIME_ERROR_UPDATING_COMPONENT', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jrealtimeException->getMessage ();
			$response->errorlevel = $jrealtimeException->getErrorLevel();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Perform the asyncronous update of the component
	 * 1- Dowload the remote update package file
	 * 2- Use the Joomla installer to install it
	 * 3- Return status to the js app
	 *
	 * @static
	 * @access private
	 * @param Object[] $additionalModels Array for additional injected models type hinted by interface
	 * @return Object
	 */
	private function installComponentUpdate($additionalModels = null) {
		// Response JSON object
		$response = new stdClass ();
		$file_path = JFactory::getConfig()->get('tmp_path', '/tmp') . '/KML1306GDHBavsf3243564832xshkaw35gq1egg1286s.zip';
	
		try {
			// Unpack the downloaded package file.
			$package = JInstallerHelper::unpack($file_path, true);
			if(!$package) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_ERROR_EXTRACTING_UPDATES' ), 'error' );
			}
	
			// 2- Use the Joomla installer to install it
			// New plugin installer
			$updateInstaller = new JInstaller ();
			if (! $updateInstaller->install ( $package['extractdir'] )) {
				throw new JRealtimeException ( JText::_ ( 'COM_JREALTIME_ERROR_INSTALLING_UPDATES' ), 'error' );
			}
	
			// Delete dirty files and folder
			unlink($file_path);
			$it = new RecursiveDirectoryIterator($package['extractdir'], RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new RecursiveIteratorIterator($it,
					RecursiveIteratorIterator::CHILD_FIRST);
			foreach($files as $file) {
				if ($file->isDir()){
					rmdir($file->getRealPath());
				} else {
					unlink($file->getRealPath());
				}
			}
			// Delete the now empty folder
			rmdir($package['extractdir']);
	
			// All went well
			$response->result = true;
		} catch ( JRealtimeException $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->errorlevel = $e->getErrorLevel();
			return $response;
		} catch ( Exception $e ) {
			$jrealtimeException = new JRealtimeException ( JText::sprintf ( 'COM_JREALTIME_ERROR_UPDATING_COMPONENT', $e->getMessage () ), 'error' );
			$response->result = false;
			$response->exception_message = $jrealtimeException->getMessage ();
			$response->errorlevel = $jrealtimeException->getErrorLevel();
			return $response;
		}
	
		return $response;
	}
	
	/**
	 * Mimic an entities list, as ajax calls arrive are redirected to loadEntity public responsibility to get handled
	 * by specific subtask. Responses are returned to controller and encoded from view over HTTP to JS client
	 * 
	 * @access public 
	 * @param string $id Rappresenta l'op da eseguire tra le private properties
	 * @param mixed $param Parametri da passare al private handler
	 * @param Object[]& $DIModels
	 * @return Object& $utenteSelezionato
	 */
	public function loadAjaxEntity($id, $param , $DIModels) {
		//Delega la private functions delegata dalla richiesta sulla entity
		$response = $this->$id($param, $DIModels);

		return $response;
	}
}