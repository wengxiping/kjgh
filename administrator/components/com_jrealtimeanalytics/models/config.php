<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::CONFIG::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport('joomla.application.component.modelform');

/**
 * Config model responsibilities
 *
 * @package JREALTIMEANALYTICS::CONFIG::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
interface IConfigModel {
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData();
	
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity();
}

/**
 * Config model concrete implementation
 *
 * @package JREALTIMEANALYTICS::CONFIG::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
class JRealtimeModelConfig extends JModelForm implements IConfigModel {
	/**
	 * Variables in request array
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestArray;
	
	/**
	 * Variables in request array name
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestName;
	
	/**
	 * Clean the cache
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 * @return  void
	 * @since   11.1
	 */
	private function cleanComponentCache($group = null, $client_id = 0) {
		// Initialise variables;
		$conf = JFactory::getConfig();
		$dispatcher = JDispatcher::getInstance();
	
		$options = array(
				'defaultgroup' => ($group) ? $group : $this->app->input->get('option'),
				'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache'));
	
		$cache = JCache::getInstance('callback', $options);
		$cache->clean();
	
		// Trigger the onContentCleanCache event.
		$dispatcher->trigger('onContentCleanCache', $options);
	}
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	private function &getConfigData() { 
		$instance = JComponentHelper::getParams('com_jrealtimeanalytics'); 
		return $instance;
	}
	
	/**
	 * Effettua lo storing dell'asset delle permissions sul component level
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function storePermissionsAsset($data) {
		// Save the rules.
		if (isset($data['params']) && isset($data['params']['rules'])) {
			$form = $this->getForm($data);
			// Validate the posted data.
			$postedRules = $this->validate($form, $data['params']);
			
			$rules = new JAccessRules($postedRules['rules']);
			$asset = JTable::getInstance('asset');
	
			if (!$asset->loadByName($data['option'])) {
				$root = JTable::getInstance('asset');
				$root->loadByName('root.1');
				$asset->name = $data['option'];
				$asset->title = $data['option'];
				$asset->setLocation($root->id, 'last-child');
			}
			$asset->rules = (string) $rules;
	
			if (!$asset->check() || !$asset->store()) {
				$this->setError($asset->getError());
				return false;
			}
		}
	
		return true;
	}
	
	/**
	 * Method to get a form object.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		jimport ( 'joomla.form.form' );
		JForm::addFormPath ( JPATH_ADMINISTRATOR . '/components/com_jrealtimeanalytics' );
	
		// Get the form.
		$form = $this->loadForm ( 'com_jrealtimeanalytics.component', 'config', array ('control' => 'params', 'load_data' => $loadData ), false, '/config' );
	
		if (empty ( $form )) {
			return false;
		}
	
		return $form;
	}
	
	/**
	 * Ottiene i dati di configurazione del componente
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData() {
		return $this->getConfigData ();
	}
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity() {
		$table = JTable::getInstance ( 'extension' );
		
		try {
			// Found as installed extension
			if (!$extensionID = $table->find(array('element' => 'com_jrealtimeanalytics'))) {
				throw new JRealtimeException($table->getError (), 'error');
			}
			
			$table->load($extensionID);
		
			// Translate posted jform array to params for ORM table binding
			$post = $this->app->input->post;
			
			// If default period changed, set it into user session
			$currentPeriod = json_decode($table->params)->default_period_interval;
			$postArray = $post->get('params', array(), 'array');
			$postedPeriod = $postArray['default_period_interval'];
			// Period has changed, refresh current user session
			if($currentPeriod != $postedPeriod) {
				$this->app->setUserState("{$table->element}.serverstats.fromperiod", null);
				$this->app->setUserState("{$table->element}.serverstats.toperiod", null);
			}
			
			// Try to firstly validate form, validate also for rules
			$formData = $post->getArray($this->requestArray[$this->requestName]);
			$form = $this->getForm();
			if(!$this->validate($form, $formData['params'])) {
				$exceptions = $this->getErrors();
				if(is_array($exceptions) && $exceptions[0] instanceof Exception) {
					$exceptionMessage = $exceptions[0]->getMessage();
					throw new JRealtimeException($exceptionMessage, 'error');
				}
			}
				
			if (!$table->bind ($post->getArray($this->requestArray[$this->requestName]))) {
				throw new JRealtimeException($table->getError (), 'error');
			}
		
			// pre-save checks
			if (!$table->check()) {
				throw new JRealtimeException($table->getError (), 'error');
			}
		
			// save the changes
			if (!$table->store()) {
				throw new JRealtimeException($table->getError (), 'error');
			}
			
			// save the changes
			if (!$this->storePermissionsAsset($post->getArray($this->requestArray[$this->requestName]))) {
				throw new JRealtimeException(JTEXT::_('COM_JREALTIME_ERROR_STORING_PERMISSIONS'), 'error');
			}
		
		} catch (JRealtimeException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jRealtimeException = new JRealtimeException($e->getMessage(), 'error');
			$this->setError($jRealtimeException);
			return false;
		}
		
		// Clean the cache.
		$this->cleanComponentCache ( '_system', 0 );
		$this->cleanComponentCache ( '_system', 1 );
		return true;
	}
	
	/**
	 * Class contructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
		
		// App reference
		$this->app = JFactory::getApplication();
		$this->requestArray = &$GLOBALS;
		$this->requestName = '_' . strtoupper('post');
	}
}