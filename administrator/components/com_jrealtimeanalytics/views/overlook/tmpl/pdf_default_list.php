<?php 
/** 
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overlook
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>

<br/><br/>
<img src="<?php echo JPATH_ROOT;?>/administrator/components/com_jrealtimeanalytics/images/icon-48-statspdf.png"/>&nbsp;&nbsp;&nbsp;&nbsp;<b style="font-size:18px;"><?php echo JText::sprintf('COM_JREALTIME_OVERVIEW_STATS', $this->monthString);?></b>
<hr/>

<div>
	<img src="<?php echo JPATH_ROOT;?>/administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_overview.png';?>" />
</div>
	
