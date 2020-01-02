<?php
// namespace administrator\components\com_jrealtimeanalytics\framework\helpers;
/** 
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage helpers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html   
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.language.language');

/**
 * Manage language inject translations for frontend client JS App
 * 
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage helpers
 * @since 2.0
 */ 
class JRealtimeHelpersLanguage extends JLanguage {
	/**
	 * Injector language const to JS domain with same name mapping
	 * @access protected
	 * @param $translations Object&
	 * @param $document Object&
	 * @return void
	 */
	public function injectJsTranslations(&$translations, &$document) {
		$jsInject = null;
 		// Do translations
		foreach ( $translations as $translation ) {
			$jsTranslation = strtoupper ( $translation );
			$translated = JText::_( $jsTranslation );
			$jsInject .= <<<JS
				var $translation = '{$translated}'; 
JS;
		}
		$document->addScriptDeclaration($jsInject);
	}

	/**
	 * Override Language instantiator 
	 * 
	 * @access	public
	 * @return	JLanguage  The Language object.
	 * @since	1.5
	 */
	public static function getInstance($lang = null, $debug = false) {
		static $lang;
		
		if(!is_object($lang)) {
			$conf	= JFactory::getConfig();
			$locale	= $conf->get('config.language');
			$lang = new JRealtimeHelpersLanguage($locale);
			$lang->setDebug($conf->get('config.debug_lang'));
		}
		
		return $lang;
	}
}
