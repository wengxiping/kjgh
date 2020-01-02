<?php
// namespace components\com_jrealtimeanalytics\libraries\framework\view;
/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage view
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.application.component.view' );
jimport ( 'joomla.html.pagination' );

/**
 * Base view for all display core
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage view
 * @since 2.0
 */
class JRealtimeView extends JViewLegacy {
	/**
	 * Reference to application
	 *
	 * @access public
	 * @var Object
	 */
	public $app;
	
	/**
	 * Reference to option executed
	 *
	 * @access public
	 * @var string
	 */
	public $option;
	
	/**
	 * Inject language constant into JS Domain maintaining same name mapping
	 *
	 * @access protected
	 * @param $translations Object&        	
	 * @param $document Object&        	
	 * @return void
	 */
	protected function injectJsTranslations($translations, $document) {
		$jsInject = null;
		// Do translations
		foreach ( $translations as $translation ) {
			$jsTranslation = strtoupper ( $translation );
			$translated = JText::_ ( $jsTranslation, true );
			$jsInject .= <<<JS
				var $translation = '{$translated}'; 
JS;
		}
		$document->addScriptDeclaration ( $jsInject );
	}
	
	/**
	 * Manage injecting jQuery framework into document with class inheritance support
	 *
	 * @access protected
	 * @param Object& $doc
	 * @param boolean $fullStack         	
	 * @return void
	 */
	protected function loadJQuery($document, $fullStack = true) {
		if($fullStack) {
			JHtml::_ ( 'bootstrap.framework' );
		} else {
			JHtml::_ ( 'jquery.framework' );
		}
		
		// jQuery foundation framework
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/jstorage.min.js' );
		$base = JUri::root ();
		$document->addScriptDeclaration ( "var jrealtimeBaseURI='$base';" );
	}
	
	/**
	 * Manage injecting jQuery framework into document with class inheritance support
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadJQueryClass($document) {
		// jQuery foundation framework and class support
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/classnative.js' );
	}
	
	/**
	 * Manage injecting Bootstrap framework into document
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @param string $application
	 * @return void
	 */
	protected function loadBootstrap($document, $application = '/administrator') {
		// Main styles for admin interface
		$document->addStylesheet ( JUri::root ( true ) . $application . '/components/com_jrealtimeanalytics/css/bootstrap-interface.css' );
		
		// Main JS file for admin interface
		$document->addScript ( JUri::root ( true ) . $application . '/components/com_jrealtimeanalytics/js/bootstrap-interface.js' );
	}
	
	/**
	 * Manage injecting jQuery Widgets script into document
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadJQWidgets($document) {
		// Main JS file for admin interface
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqwidgets/jqxcore.js' );
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqwidgets/jqxchart.js' );
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqwidgets/jqxgauge.js' );
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/raty/jquery.raty.js' );
	}
	
	/**
	 * Manage injecting jQuery Fancybox
	 *
	 * @access protected
	 * @param Object& $doc
	 * @return void
	 */
	protected function loadJQFancybox($document) {
		// Main JS file for admin interface
		$document->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/fancybox/jquery.fancybox-1.3.4.css' );
		$document->addScript(JUri::root(true) . '/administrator/components/com_jrealtimeanalytics/js/libraries/fancybox/jquery.fancybox-1.3.4.pack.js');
	}
	
	/**
	 * Manage injecting valildation plugin into document
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadValidation($document) {
		$document->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/css/simplevalidation.css' );
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/jquery.simplevalidation.js' );
	}
	
	/**
	 * Manage injecting jQuery UI framework into document
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadJQueryUI($document) {
		$document->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/css/jqueryui/jquery.ui.all.css' );
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/jquery.ui.js' );
	}
	
	/**
	 * Manage injecting JQVMap plugin
	 *
	 * @access protected
	 * @param Object& $doc        	
	 * @return void
	 */
	protected function loadJQVMap($document) {
		$document->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqvmap/jqvmap.css' );
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqvmap/jquery.vmap.js' );
		$document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/libraries/jqvmap/jquery.vmap.world.js' );
	}
	
	/**
	 * Class constructor
	 *
	 * @param array $config
	 *        	return Object
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		
		$this->app = JFactory::getApplication ();
		$this->user = JFactory::getUser ();
		$this->document = JFactory::getDocument();
		$this->option = $this->app->input->get ( 'option' );
	}
}