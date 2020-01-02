<?php
// namespace administrator\components\com_jrealtimeanalytics\views\cpanel;
/**
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage cpanel
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * CPanel view
 *
 * @package JREALTIMEANALYTICS::CPANEL::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage cpanel
 * @since 1.0
 */
class JRealtimeViewCpanel extends JRealtimeView {
	/**
	 * Renderizza l'iconset del cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function getIcon($link, $image, $text, $target = '', $title = null, $class = 'icons') {
		$mainframe = JFactory::getApplication ();
		$lang = JFactory::getLanguage ();
		$option = $this->option;
		?>
<div class="<?php echo $class;?>" style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
	<div class="icon">
		<a <?php echo $title;?> <?php echo $target;?> href="<?php echo $link; ?>"> 
			<div class="task <?php echo $image;?>"></div> 
			<span class="task"><?php echo $text; ?></span>
		</a>
	</div>
</div>
<?php
		}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_CPANEL_TOOLBAR' ), 'jrealtimeanalytics' );
		JToolBarHelper::custom('cpanel.display', 'home', 'home', 'COM_JREALTIME_CPANEL', false);
	}
	
	/**
	 * Effettua il rendering del pannello di controllo
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = JFactory::getDocument ();
		$this->loadJQuery ( $doc );
		$this->loadBootstrap ( $doc );
		$this->loadJQWidgets ( $doc );
		$this->loadJQVMap( $doc );
		$doc->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/css/cpanel.css' );
		$doc->addStylesheet ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/css/sitespeed.css' );
		
		
		// Add JS Libs
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/chart.js' );
		
		// Core MVC JS
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/views/sitespeed.view.js' );
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/models/sitespeed.model.js' );
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/controllers/sitespeed.controller.js' );
		$doc->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/cpanel.js' );
		
		// Inject js translations
		$translations = array (
				'COM_JREALTIME_PAGELOADED_TIME',
				'COM_JREALTIME_TOTALPAGES_CHART',
				'COM_JREALTIME_TOTALUSERS_CHART',
				'COM_JREALTIME_SYSTEMUSERS_CHART',
				'COM_JREALTIME_SYSTEMEVENTS_CHART',
				'COM_JREALTIME_PAGELOADED_TIME',
				'COM_JREALTIME_VERYFAST',
				'COM_JREALTIME_FAST',
				'COM_JREALTIME_AVERAGE',
				'COM_JREALTIME_SLOW',
				'COM_JREALTIME_VERYSLOW',
				'COM_JREALTIME_SLOWER',
				'COM_JREALTIME_FASTER',
				'COM_JREALTIME_SPEEDTEST_ADVICE',
				'COM_JREALTIME_SITESPEED_POPUP_TITLE',
				'COM_JREALTIME_EXPIREON',
				'COM_JREALTIME_CLICKTOUPDATE',
				'COM_JREALTIME_UPDATEPROGRESSTITLE',
				'COM_JREALTIME_DOWNLOADING_UPDATE_SUBTITLE',
				'COM_JREALTIME_INSTALLING_UPDATE_SUBTITLE',
				'COM_JREALTIME_COMPLETED_UPDATE_SUBTITLE'
		);
		$this->injectJsTranslations ( $translations, $doc );
		
		$infoData = $this->getModel ()->getData ();
		$doc->addScriptDeclaration ( 'var jrealtimeChartData = ' . json_encode ( $infoData ) . ';' );
		
		// Buffer delle icons
		ob_start ();
		// Full stats report
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=serverstats.display&amp;statsreport=full', 'icon-chart', JText::_ ( 'COM_JREALTIME_SERVERSTATS' ), null, null, 'icons-first' );
		
		// Specific stats report
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=serverstats.display&amp;statsreport=landingleave', 'icon-copy', JText::_ ( 'COM_JREALTIME_LANDINGLEAVE' ), null, null, 'icons-first' );
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=serverstats.display&amp;statsreport=visitsbypage', 'icon-file', JText::_ ( 'COM_JREALTIME_VISITS_BYPAGE' ), null, null, 'icons-first' );
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=serverstats.display&amp;statsreport=visitsbyuser', 'icon-users', JText::_ ( 'COM_JREALTIME_VISITS_BYUSER' ), null, null, 'icons-first' );
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=serverstats.display&amp;statsreport=visitsbyip', 'icon-location', JText::_ ( 'COM_JREALTIME_VISITS_BYIP' ), null, null, 'icons-first' );
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=serverstats.display&amp;statsreport=referralkeys', 'icon-contract', JText::_ ( 'COM_JREALTIME_VISITS_REFERRAL_KEYWORDS' ), null, null, 'icons-first' );
		
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=heatmap.display', 'icon-health', JText::_ ( 'COM_JREALTIME_HEATMAP' ) );
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=overlook.display', 'icon-bars', JText::_ ( 'COM_JREALTIME_OVERVIEW' ) );
		
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=realstats.display', 'icon-bars', JText::_ ( 'COM_JREALTIME_REALSTATS' ) );
		
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=eventstats.display', 'icon-lightning', JText::_ ( 'COM_JREALTIME_EVENTSTATS' ) );
		$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=categories.display', 'icon-folder', JText::_ ( 'COM_JREALTIME_EVENTS_CATEGORIES' ) );
		
		// Access check.
		if ($this->user->authorise('jrealtime.google', 'com_jrealtimeanalytics')) {
			$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=google.display', 'icon-power-cord', JText::_ ( 'COM_JREALTIME_GOOGLE' ) );
		}
		
		// Access check.
		if ($this->user->authorise('jrealtime.webmasters', 'com_jrealtimeanalytics')) {
			$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=webmasters.display', 'icon-list-view', JText::_ ( 'COM_JREALTIME_GOOGLE_WEBMASTERS' ) );
		}
		
		// Access check.
		if ($this->user->authorise('core.admin', 'com_jrealtimeanalytics')) {
			$this->getIcon ( 'index.php?option=com_jrealtimeanalytics&amp;task=config.display', 'icon-cog', JText::_ ( 'COM_JREALTIME_CONFIG_ICON' ) );
		}
		
		$this->getIcon ( 'http://storejextensions.org/jrealtime_analytics_documentation.html', 'icon-help', JText::_ ( 'COM_JREALTIME_HELP' ) );
		$contents = ob_get_clean ();
		
		// Assign reference variables
		$this->icons = $contents;
		$this->updatesData = $this->getModel ()->getUpdates ( $this->get ( 'httpclient' ) );
		$this->infoData = $infoData;
		$this->cParams = $this->getModel()->getState('cparams');
		$this->currentVersion = strval(simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/jrealtimeanalytics.xml')->version);
		
		// Aggiunta toolbar
		$this->addDisplayToolbar ();
		
		// Output del template
		parent::display ();
	}
}