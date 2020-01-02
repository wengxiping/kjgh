<?php
// namespace administrator\components\com_jrealtimeanalytics\framework\renderers;
/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage renderers
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage renderers
 * @since 1.0
 */
interface JRealtimeRenderersAdapter {
	/**
	 * Main renderer responsibilites, format and render the model data based on the user adapter chosen
	 * 
	 * @access public
	 * @param string $data        	
	 * @param Object $model        	
	 * @return Void
	 */
	public function renderContent($data, $model);
}