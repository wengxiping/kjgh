<?php 
//namespace administrator\components\com_jrealtimeanalytics\framework\graph;
/**
 * @author Joomla! Extensions Store
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework 
 * @subpackage graph
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ();

/**
 * Definisce le responsabilità delle classi generators dei charts
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework 
 * @subpackage graph
 * @since	1.0
 */
interface JRealtimeGraphGenerators {
	/**
	 * Ottenute le informazioni necessarie da avvio alla reale generazione dell'immagine del grafico in cache folder
	 * @access public 
	 * @param Object& $graphDataModel 
	 * @return Void
	 */
	public function buildBars(&$graphDataModel);
	
	/**
	 * Ottenute le informazioni necessarie da avvio alla reale generazione dell'immagine del grafico in cache folder
	 * @access public
	 * @param Object& $graphData
	 * @param array $geoTranslations
	 * @param array $sizes
	 * @param boolean $title
	 * @param array $legendPos
	 * @return Void
	 */
	public function buildPies(&$graphData, $geoTranslations, $sizes = array(), $title = true, $legendPos = array());
}
?>