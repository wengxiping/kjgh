<?php
//namespace administrator\components\com_jrealtimeanalytics\framework\graph\generators;
/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::::administrator::components::com_jrealtimeanalytics
 * @subpackage framework 
 * @subpackage graph
 * @subpackage generators
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();
define ( 'SHOWGRAPH_TOTALVISITEDPAGES', 1 );
define ( 'SHOWGRAPH_TOTALVISITORS', 3 );
define ( 'SHOWGRAPH_MEDIUMVISITTIME', 4 );
define ( 'SHOWGRAPH_MEDIUMVISITEDPAGESPERSINGLEUSER', 5 );
define ( 'SHOWGRAPH_NUMUSERSGEOGROUPED', 6 );
define ( 'SHOWGRAPH_NUMUSERSBROWSERGROUPED', 7 );
define ( 'SHOWGRAPH_NUMUSERSOSGROUPED', 8 );
define ( 'SHOWGRAPH_BOUNCERATE', 14 );
define ( 'SHOWGRAPH_UNIQUEVISITORS', 15 );
define ( 'SHOWGRAPH_NUMUSERSDEVICEGROUPED', 16 );

require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/graph/lib/jpgraph.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/graph/lib/jpgraph_bar.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/graph/lib/jpgraph_line.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/graph/lib/jpgraph_pie.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/graph/lib/jpgraph_pie3d.php';

/**
 * Realizza l'interfaccia JRealtimeGenerators per la generazione di grafici su file dato
 * un array di informazioni in ingresso
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework 
 * @subpackage graph
 * @subpackage generators
 * @since 1.2
 */
class JRealtimeGraphGeneratorsCharts implements JRealtimeGraphGenerators {
	/**
	 * Reference unique for every user
	 *
	 *  @access private
	 *  @var mixed
	 */
	private $userImageIdentifier;
	
	/**
	 * Oggetto generatore del grafico
	 *
	 * @access protected
	 * @var Object
	 */
	protected $graphInstance;
	
	/**
	 * Theme chosen to generate graphs
	 *
	 * @access protected
	 * @var Object
	 */
	protected $graphTheme;
	
	/**
	 * Calculate pie graph offset height
	 *
	 * @access private
	 * @param array& $sizes        	
	 * @return void
	 */
	private function calculatePieOffsetWidth(&$sizes) {
		if ($this->maxLegendlabelLength > 15) {
			$extraChars = $this->maxLegendlabelLength - 15;
			$widthOffset = $extraChars * 9;
			$sizes [0] += $widthOffset;
		}
	}
	
	/**
	 * Calculate pie graph offset height
	 *
	 * @access private
	 * @param Object $graphData        	
	 * @param array& $sizes        	
	 * @return void
	 */
	private function calculatePieOffsetHeight($graphData, &$sizes) {
		// Calculate Y offset dimension and legend columns layout based on data count
		// Reset columns from previous graph
		$this->legendColumns = 1;
		$this->pieCenterX = 0.62;
		$this->pieCenterY = 0.65;
		$numberOfEntries = count ( $graphData );
		// 20 entries fit 1 column/350px dedault
		if ($numberOfEntries > 20) {
			$extraEntries = $numberOfEntries - 20;
			$heightOffset = $extraEntries * 40;
			$sizes [1] += $heightOffset;
		}
		if ($numberOfEntries > 30) {
			$this->legendColumns = 2;
			$this->pieCenterX = 0.50;
			$this->pieCenterY = 0.67;
		}
	}
	
	/**
	 * Geolocation generator func for pie graph
	 *
	 * @access private
	 * @param array $graphData        	
	 * @param array $geoTranslations        	
	 * @param array $sizes        	
	 * @param array $title        	
	 * @param array $legendPos        	
	 * @return void
	 */
	private function buildGeolocationPie($graphData, $geoTranslations, $sizes, $title, $legendPos) {
		// Reset resources
		$YData = array ();
		if (! is_array ( $graphData [SHOWGRAPH_NUMUSERSGEOGROUPED]['serverside'] ) || ! count ( $graphData [SHOWGRAPH_NUMUSERSGEOGROUPED]['serverside'] )) {
			$YData = array (
					1 
			);
		}
		
		// Calculate Pie height $sizes[1] offset, based on number of entries
		$this->calculatePieOffsetHeight ( $graphData [SHOWGRAPH_NUMUSERSGEOGROUPED]['serverside'], $sizes );
		
		$legends = array ();
		foreach ( $graphData [SHOWGRAPH_NUMUSERSGEOGROUPED]['serverside'] as $geoData ) {
			$YData [] = $geoData [0];
			$label = isset ( $geoTranslations [$geoData [1]] ) ? $geoTranslations [$geoData [1]] ['name'] : JText::_('COM_JREALTIME_NOTSET');
			$legends [] = $label . ' (%.1f%%)';
			// Calculate max label string length
			$this->maxLegendlabelLength = max ( array (
					$this->maxLegendlabelLength,
					strlen ( $label ) 
			) );
		}
		
		// Calculate Pie width $sizes[0] offset, based on longest entry string
		$this->calculatePieOffsetWidth ( $sizes );
		
		// Set output filename
		$filename = $this->userImageIdentifier . '_serverstats_pie_geolocation.png';
		// Istanza del context dove settiamo anche le dimensioni e del grafico
		$this->graphInstance = new PieGraph ( $sizes [0], $sizes [1] );
		$this->graphInstance->SetTheme ( $this->graphTheme );
		if ($title) {
			$this->graphInstance->footer->center->Set ( JText::_ ( 'COM_JREALTIME_GRAPH_SERVERSTATS_GEOLOCATION' ) );
			$this->graphInstance->footer->center->SetFont ( FF_FONT1, FS_BOLD, 12 );
			$this->graphInstance->footer->center->SetColor ( '#3a87ad' );
		}
		
		$this->graphInstance->legend->SetColumns ( $this->legendColumns );
		$this->graphInstance->legend->SetShadow ( 'gray@0.4', 3 );
		$this->graphInstance->legend->SetPos ( $legendPos [0], $legendPos [1], 'left', 'top' );
		
		// Dimensioniamo in larghezza il grafico
		$pie3D = new PiePlot3d ( $YData );
		
		$pie3D->SetCenter ( $this->pieCenterX, $this->pieCenterY );
		$pie3D->SetAngle ( 30 );
		$pie3D->value->Show ( true );
		$pie3D->SetLegends ( $legends );
		
		// Add the plot to the graph
		$this->graphInstance->Add ( $pie3D );
		$this->graphInstance->SetShadow ( 'darkgray', 5 );
		
		// Controllo esistenza cartella cache e eventuale creazione
		if (! is_dir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' )) {
			mkdir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/', 0755 );
		}
		
		// Pre garbage collector graph image
		if (is_file ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename )) {
			unlink ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
		}
		// Display the graph
		$this->graphInstance->Stroke ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
	}
	
	/**
	 * Operating system generator func for pie graph
	 *
	 * @access private
	 * @param array $graphData        	
	 * @param array $sizes        	
	 * @param array $title        	
	 * @param array $legendPos        	
	 * @return void
	 */
	private function buildOSPie($graphData, $sizes, $title, $legendPos) {
		// Reset resources
		$YData = array ();
		if (! is_array ( $graphData [SHOWGRAPH_NUMUSERSOSGROUPED] ) || ! count ( $graphData [SHOWGRAPH_NUMUSERSOSGROUPED] )) {
			$YData = array (
					1 
			);
		}
		
		// Calculate Pie height $sizes[1] offset, based on number of entries
		$this->calculatePieOffsetHeight ( $graphData [SHOWGRAPH_NUMUSERSOSGROUPED], $sizes );
		
		$legends = array ();
		foreach ( $graphData [SHOWGRAPH_NUMUSERSOSGROUPED] as $osData ) {
			$YData [] = $osData [0];
			$legends [] = $osData [1] . ' (%.1f%%)';
			// Calculate max label string length
			$this->maxLegendlabelLength = max ( array (
					$this->maxLegendlabelLength,
					strlen ( $osData [1] )
			) );
		}
		
		// Calculate Pie width $sizes[0] offset, based on longest entry string
		$this->calculatePieOffsetWidth ( $sizes );
		
		// Set output filename
		$filename = $this->userImageIdentifier . '_serverstats_pie_os.png';
		// Istanza del context dove settiamo anche le dimensioni e del grafico
		$this->graphInstance = new PieGraph ( $sizes [0], $sizes [1] );
		$this->graphInstance->SetTheme ( $this->graphTheme );
		if ($title) {
			$this->graphInstance->footer->center->Set ( JText::_ ( 'COM_JREALTIME_GRAPH_SERVERSTATS_OS' ) );
			$this->graphInstance->footer->center->SetFont ( FF_FONT1, FS_BOLD, 12 );
			$this->graphInstance->footer->center->SetColor ( '#3a87ad' );
		}
		
		$this->graphInstance->legend->SetColumns ( $this->legendColumns );
		$this->graphInstance->legend->SetShadow ( 'gray@0.4', 3 );
		$this->graphInstance->legend->SetPos ( $legendPos [0], $legendPos [1], 'left', 'top' );
		
		// Dimensioniamo in larghezza il grafico
		$pie3D = new PiePlot3d ( $YData );
		$pie3D->SetCenter ( $this->pieCenterX, $this->pieCenterY );
		$pie3D->SetAngle ( 30 );
		$pie3D->value->Show ( true );
		$pie3D->SetLegends ( $legends );
		
		// Add the plot to the graph
		$this->graphInstance->Add ( $pie3D );
		$this->graphInstance->SetShadow ( 'darkgray', 5 );
		
		// Pre garbage collector graph image
		if (is_file ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename )) {
			unlink ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
		}
		// Display the graph
		$this->graphInstance->Stroke ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
	}
	
	/**
	 * Device generator func for pie graph
	 *
	 * @access private
	 * @param array $graphData
	 * @param array $sizes
	 * @param array $title
	 * @param array $legendPos
	 * @return void
	 */
	private function buildDevicePie($graphData, $sizes, $title, $legendPos) {
		// Reset resources
		$YData = array ();
		if (! is_array ( $graphData [SHOWGRAPH_NUMUSERSDEVICEGROUPED] ) || ! count ( $graphData [SHOWGRAPH_NUMUSERSDEVICEGROUPED] )) {
			$YData = array (
					1
			);
		}
	
		// Calculate Pie height $sizes[1] offset, based on number of entries
		$this->calculatePieOffsetHeight ( $graphData [SHOWGRAPH_NUMUSERSDEVICEGROUPED], $sizes );
	
		$legends = array ();
		foreach ( $graphData [SHOWGRAPH_NUMUSERSDEVICEGROUPED] as $deviceData ) {
			$YData [] = $deviceData [0];
			$legends [] = $deviceData [1] . ' (%.1f%%)';
			// Calculate max label string length
			$this->maxLegendlabelLength = max ( array (
					$this->maxLegendlabelLength,
					strlen ( $deviceData [1] )
			) );
		}
	
		// Calculate Pie width $sizes[0] offset, based on longest entry string
		$this->calculatePieOffsetWidth ( $sizes );
	
		// Set output filename
		$filename = $this->userImageIdentifier . '_serverstats_pie_device.png';
		// Istanza del context dove settiamo anche le dimensioni e del grafico
		$this->graphInstance = new PieGraph ( $sizes [0], $sizes [1] );
		$this->graphInstance->SetTheme ( $this->graphTheme );
		if ($title) {
			$this->graphInstance->footer->center->Set ( JText::_ ( 'COM_JREALTIME_GRAPH_SERVERSTATS_DEVICE' ) );
			$this->graphInstance->footer->center->SetFont ( FF_FONT1, FS_BOLD, 12 );
			$this->graphInstance->footer->center->SetColor ( '#3a87ad' );
		}
	
		$this->graphInstance->legend->SetColumns ( $this->legendColumns );
		$this->graphInstance->legend->SetShadow ( 'gray@0.4', 3 );
		$this->graphInstance->legend->SetPos ( $legendPos [0], $legendPos [1], 'left', 'top' );
	
		// Dimensioniamo in larghezza il grafico
		$pie3D = new PiePlot3d ( $YData );
		$pie3D->SetCenter ( $this->pieCenterX, $this->pieCenterY );
		$pie3D->SetAngle ( 30 );
		$pie3D->value->Show ( true );
		$pie3D->SetLegends ( $legends );
	
		// Add the plot to the graph
		$this->graphInstance->Add ( $pie3D );
		$this->graphInstance->SetShadow ( 'darkgray', 5 );
	
		// Pre garbage collector graph image
		if (is_file ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename )) {
			unlink ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
		}
		// Display the graph
		$this->graphInstance->Stroke ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
	}
	
	/**
	 * Browsers generator func for pie graph
	 *
	 * @access private
	 * @param array $graphData        	
	 * @param array $sizes        	
	 * @param array $title        	
	 * @param array $legendPos        	
	 * @return void
	 */
	private function buildBrowserPie($graphData, $sizes, $title, $legendPos) {
		// Reset resources
		$YData = array ();
		if (! is_array ( $graphData [SHOWGRAPH_NUMUSERSBROWSERGROUPED] ) || ! count ( $graphData [SHOWGRAPH_NUMUSERSBROWSERGROUPED] )) {
			$YData = array (
					1 
			);
		}
		
		// Calculate Pie height $sizes[1] offset, based on number of entries
		$this->calculatePieOffsetHeight ( $graphData [SHOWGRAPH_NUMUSERSBROWSERGROUPED], $sizes );
		
		$legends = array ();
		foreach ( $graphData [SHOWGRAPH_NUMUSERSBROWSERGROUPED] as $browserData ) {
			$YData [] = $browserData [0];
			$legends [] = $browserData [1] . ' (%.1f%%)';
			// Calculate max label string length
			$this->maxLegendlabelLength = max ( array (
					$this->maxLegendlabelLength,
					strlen ( $browserData [1] ) 
			) );
		}
		
		// Calculate Pie width $sizes[0] offset, based on longest entry string
		$this->calculatePieOffsetWidth ( $sizes );
		
		// Set output filename
		$filename = $this->userImageIdentifier . '_serverstats_pie_browser.png';
		// Istanza del context dove settiamo anche le dimensioni e del grafico
		$this->graphInstance = new PieGraph ( $sizes [0], $sizes [1] );
		$this->graphInstance->SetTheme ( $this->graphTheme );
		if ($title) {
			$this->graphInstance->footer->center->Set ( JText::_ ( 'COM_JREALTIME_GRAPH_SERVERSTATS_BROWSER' ) );
			$this->graphInstance->footer->center->SetFont ( FF_FONT1, FS_BOLD, 12 );
			$this->graphInstance->footer->center->SetColor ( '#3a87ad' );
		}
		
		$this->graphInstance->legend->SetColumns ( $this->legendColumns );
		$this->graphInstance->legend->SetShadow ( 'gray@0.4', 3 );
		$this->graphInstance->legend->SetPos ( $legendPos [0], $legendPos [1], 'left', 'top' );
		
		// Dimensioniamo in larghezza il grafico
		$pie3D = new PiePlot3d ( $YData );
		$pie3D->SetCenter ( $this->pieCenterX, $this->pieCenterY );
		$pie3D->SetAngle ( 30 );
		$pie3D->value->Show ( true );
		$pie3D->SetLegends ( $legends );
		
		// Add the plot to the graph
		$this->graphInstance->Add ( $pie3D );
		$this->graphInstance->SetShadow ( 'darkgray', 5 );
		
		// Pre garbage collector graph image
		if (is_file ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename )) {
			unlink ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
		}
		// Display the graph
		$this->graphInstance->Stroke ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
	}
	
	/**
	 * Ottenute le informazioni necessarie da avvio alla reale generazione dell'immagine del grafico in cache folder
	 *
	 * @access public
	 * @param Object& $graphData
	 * @param String $graphName	
	 * @return Void
	 */
	public function buildBars(&$graphData, $graphName = '_serverstats_bars.png') {
		$filename = $this->userImageIdentifier . $graphName;
		// Dimensioniamo in larghezza il grafico
		$graph = $this->graphInstance = new Graph ( 550, 350 );
		
		$datax = array (
				JText::_ ( 'COM_JREALTIME_GRAPH_TOTAL_VISITED_PAGES' ),
				JText::_ ( 'COM_JREALTIME_GRAPH_TOTAL_VISITORS' ),
				JText::_ ( 'COM_JREALTIME_GRAPH_TOTAL_UNIQUE_VISITORS' ),
				JText::_ ( 'COM_JREALTIME_GRAPH_MEDIUM_VISIT_TIME' ),
				JText::_ ( 'COM_JREALTIME_GRAPH_MEDIUM_VISITED_PAGES_PERUSER' ),
				JText::_ ( 'COM_JREALTIME_GRAPH_BOUNCERATE' )
		);
		
		$mediumVisitTimeSeconds = 0;
		if ($graphData [SHOWGRAPH_MEDIUMVISITTIME]) {
			$mediumVisitTimeSeconds = (strtotime ( $graphData [SHOWGRAPH_MEDIUMVISITTIME] ) - strtotime ( 'TODAY' )) / 60;
		}
		$datay = array (
				$graphData [SHOWGRAPH_TOTALVISITEDPAGES],
				$graphData [SHOWGRAPH_TOTALVISITORS],
				$graphData [SHOWGRAPH_UNIQUEVISITORS],
				$mediumVisitTimeSeconds,
				floatval ( $graphData [SHOWGRAPH_MEDIUMVISITEDPAGESPERSINGLEUSER] ),
				floatval ( $graphData [SHOWGRAPH_BOUNCERATE] )
		);
		
		$graph->SetScale ( "textlin" );
		$this->graphInstance->setTheme ( $this->graphTheme );
		$graph->xaxis->SetTickLabels ( $datax );
		
		$graph->SetShadow ( 'darkgray' );
		$graph->img->SetMargin ( 40, 20, 10, 30 );
		$graph->yaxis->scale->SetGrace ( 1 );
		// Sondaggio sul primo valore se float oppure no per formato valori bar
		$formato = '%0.2f';
		
		// Create del bar plot1
		$b1plot = new BarPlot ( $datay );
		$b1plot->SetWidth ( 1.0 );
		$b1plot->SetShadow ( 'darkgray' );
		// $b1plot->SetFillGradient("orange","#EEDD99",GRAD_WIDE_MIDVER);
		$b1plot->value->show ( false );
		$b1plot->value->SetFormat ( $formato );
		
		// Create the grouped bar plot
		$gbplot = new GroupBarPlot ( array (
				$b1plot 
		) );
		
		// ...and add it to the graph
		$graph->Add ( $gbplot );
		
		$graph->title->Set ( JText::_ ( 'COM_JREALTIME_SERVERSTATS_PANEL' ) );
		// $graph->xaxis->title->Set ( "" );
		// $graph->yaxis->title->Set ( $ytitle );
		
		$graph->title->SetFont ( FF_FONT1, FS_BOLD );
		$graph->yaxis->title->SetFont ( FF_FONT1, FS_BOLD );
		$graph->xaxis->title->SetFont ( FF_FONT1, FS_BOLD );
		
		// Controllo esistenza cartella cache e eventuale creazione
		if (! is_dir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' )) {
			mkdir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/', 0755 );
		}
		
		// Pre garbage collector
		if (is_file ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename )) {
			unlink ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
		}
		// Display the graph
		$graph->Stroke ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
	}
	
	/**
	 * Ottenute le informazioni necessarie da avvio alla reale generazione dell'immagine del grafico in cache folder
	 * Si basa su generici dati di input label->value
	 * @access public
	 * @param Object& $graphData
	 * @param String $graphName
	 * @param String $title
	 * @param array $legend
	 * @return Void
	 */
	public function buildGenericBars(&$graphData, $graphName = '_generic_bars.png', $title = 'COM_JREALTIME_SERVERSTATS_PANEL', $legends = array()) {
		$filename = $this->userImageIdentifier . $graphName;
		// Dimensioniamo in larghezza il grafico
		$graph = $this->graphInstance = new Graph ( 1920, 800 );
		$barPlots = array();
		$b2plot = null;
		
		// Reset resources
		if(empty($graphData)) {
			$graphData = array(null=>0);
		}
	
		$datax = array();
		$datay = array();
		$datay2 = array();
		foreach ($graphData as $label=>$value) {
			// Assign x axis labels
			$datax[] = ucfirst($label);
			if(is_array($value)) {
				// Assign y axis values
				$datay[] = $value[0];
				$datay2[] = $value[1];
			} else {
				// Assign y axis values
				$datay[] = $value;
			}
		}

		$graph->SetScale ( "textlin" );
		$this->graphInstance->setTheme ( $this->graphTheme );
		$graph->xaxis->SetTickLabels($datax);
	
		$graph->SetShadow ('darkgray');
		$graph->img->SetMargin ( 60, 40, 50, 30 );
		$graph->yaxis->scale->SetGrace ( 3 );
		//Sondaggio sul primo valore se float oppure no per formato valori bar
		$formato =  '%d';
	
		// Create del bar plot1
		$b1plot = new BarPlot ( $datay );
		$b1plot->SetWidth(1.0);
		$b1plot->SetShadow('darkgray');
		$barPlots[] = $b1plot;
		//$b1plot->SetFillGradient("orange", "#EEDD99", GRAD_WIDE_MIDVER);
		
		// Is there 2 bar plot required?
		if(count($datay2)) {
			// Create del bar plot2
			$b2plot = new BarPlot ( $datay2 );
			$b2plot->SetWidth(1.0);
			$b2plot->SetShadow('darkgray');
			$barPlots[] = $b2plot;
		}
	
		// Create the grouped bar plot
		$gbplot = new GroupBarPlot ($barPlots);
	
		// ...and add it to the graph
		$graph->Add ( $gbplot );

		// Set AFTER adding BarPlot to the graph object the label value to be showed
		$b1plot->value->SetFormat ( $formato );
		$b1plot->value->Show(true);
		if(is_object($b2plot)) {
			$b2plot->value->SetFormat ( $formato );
			$b2plot->value->Show(true);
		}
		
		// Is there any legend required?
		if(count($legends)) {
			foreach ($legends as $indexLegend=>$legendText) {
				$barPlotname = ${'b' . ($indexLegend+1) . 'plot'};
				$barPlotname->setLegend(JText::_($legendText));
			}
			// Adjust the legend position
			$graph->legend->SetLayout(LEGEND_HOR);
			$graph->legend->Pos(0.5,0.9,"center","bottom");
		}
		
		$graph->title->Set ( JText::_ ( $title ) );
	
		$graph->yaxis->title->SetMargin(10);
	
		$graph->title->SetFont ( FF_FONT2, FS_BOLD );
		$graph->yaxis->title->SetFont ( FF_FONT2, FS_BOLD );
		$graph->xaxis->title->SetFont ( FF_FONT2, FS_BOLD );

		// Controllo esistenza cartella cache e eventuale creazione
		if (! is_dir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' )) {
			mkdir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/', 0755 );
		}

		// Pre garbage collector
		if (is_file ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename )) {
			unlink ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
		}
		// Display the graph
		$graph->Stroke ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
	}
	
	/**
	 * Ottenute le informazioni necessarie da avvio alla reale generazione dell'immagine del grafico in cache folder
	 * Si basa su generici dati di input label->value
	 * @access public
	 * @param Object& $graphData
	 * @param String $graphName
	 * @param String $title
	 * @param array $legend
	 * @return Void
	 */
	public function buildGenericLines(&$graphData, $graphName = '_generic_lines.png', $title = 'COM_JREALTIME_SERVERSTATS_PANEL', $legends = array(), $axisTitles = array()) {
		$filename = $this->userImageIdentifier . $graphName;
		// Dimensioniamo in larghezza il grafico
		$graph = $this->graphInstance = new Graph ( 1920, 800 );
		$b2plot = null;
	
		// Reset resources
		if(empty($graphData)) {
			$graphData = array(null=>0);
		}
	
		$datax = array();
		$datay = array();
		$datay2 = array();
		foreach ($graphData as $label=>$value) {
			// Assign x axis labels
			$datax[] = ucfirst($label);
			if(is_array($value)) {
				// Assign y axis values
				$datay[] = $value[0];
				$datay2[] = $value[1];
			} else {
				// Assign y axis values
				$datay[] = $value;
			}
		}
	
		$graph->SetScale ( "textlin" );
		$this->graphInstance->setTheme ( $this->graphTheme );
		$graph->xaxis->SetTickLabels($datax);
	
		$graph->SetShadow ('darkgray');
		$graph->img->SetMargin ( 60, 40, 50, 30 );
		$graph->yaxis->scale->SetGrace ( 2 );
		//Sondaggio sul primo valore se float oppure no per formato valori bar
		$formato =  '%d';
	
		// Create del bar plot1
		$line1Plot = new LinePlot ( $datay );
		$graph->Add($line1Plot);
	
		// Is there 2 bar plot required?
		if(count($datay2)) {
			// Create del bar plot2
			$line2Plot = new LinePlot ( $datay2 );
			$barPlots[] = $line2Plot;
		}
	
		// ...and add it to the graph
		$graph->Add ( $line2Plot );
	
		// Set AFTER adding BarPlot to the graph object the label value to be showed
		$line1Plot->value->SetFormat ( $formato );
		$line1Plot->value->Show(true);
		if(is_object($line2Plot)) {
			$line2Plot->value->SetFormat ( $formato );
			$line2Plot->value->Show(true);
		}
	
		// Is there any legend required?
		if(count($legends)) {
			foreach ($legends as $indexLegend=>$legendText) {
				$linePlotname = ${'line' . ($indexLegend+1) . 'Plot'};
				$linePlotname->setLegend(JText::_($legendText));
			}
			// Adjust the legend position
			$graph->legend->SetLayout(LEGEND_HOR);
			$graph->legend->Pos(0.5,0.9,"center","bottom");
		}
	
		$graph->title->Set ( JText::_ ( $title ) );
		if(isset($axisTitles['x'])) {
			$graph->xaxis->title->Set($axisTitles['x']);
		}
	
		$graph->yaxis->title->SetMargin(10);
	
		$graph->title->SetFont ( FF_FONT2, FS_BOLD );
		$graph->yaxis->title->SetFont ( FF_FONT2, FS_BOLD );
		$graph->xaxis->title->SetFont ( FF_FONT2, FS_BOLD );
	
		// Controllo esistenza cartella cache e eventuale creazione
		if (! is_dir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' )) {
			mkdir ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/', 0755 );
		}
	
		// Pre garbage collector
		if (is_file ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename )) {
			unlink ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
		}
		// Display the graph
		$graph->Stroke ( JPATH_COMPONENT_ADMINISTRATOR . '/cache/' . $filename );
	}
	
	/**
	 * Ottenute le informazioni necessarie da avvio alla reale generazione dell'immagine del grafico in cache folder
	 *
	 * @access public
	 * @param Object& $graphData        	
	 * @param array $geoTranslations        	
	 * @param array $sizes        	
	 * @param boolean $title        	
	 * @param array $legendPos        	
	 * @return Void
	 */
	public function buildPies(&$graphData, $geoTranslations, $sizes = array(600, 350), $title = true, $legendPos = array(0, 0.02)) {
		// GRAFICO GeoLocation
		$this->buildGeolocationPie ( $graphData, $geoTranslations, $sizes, $title, $legendPos );
		
		// GRAFICO O/S
		$this->buildOSPie ( $graphData, $sizes, $title, $legendPos );
		
		// GRAFICO Browser
		$this->buildBrowserPie ( $graphData, $sizes, $title, $legendPos );
		
		// GRAFICO Device
		$this->buildDevicePie ( $graphData, $sizes, $title, $legendPos );
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param string $templateName        	
	 * @return Object
	 */
	public function __construct($templateName) {
		// Standard initialization to common pies
		$this->pieCenterX = 0.62;
		$this->pieCenterY = 0.65;
		$this->legendColumns = 1;
		$this->maxLegendlabelLength = 15;
		
		$templateClassName = $templateName . 'Theme';
		if (class_exists ( $templateClassName )) {
			$this->graphTheme = new $templateClassName ();
		} else {
			// Fallback on default template
			$this->graphTheme = new UniversalTheme ();
		}
		
		// Set user identifier for images
		$userObject = JFactory::getUser();
		if($userObject->id) {
			$this->userImageIdentifier = $userObject->id;
		} else {
			$this->userImageIdentifier = session_id();
		}
	}
}