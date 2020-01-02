<?php 
/** 
 * @package JREALTIMEANALYTICS::SERVERSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage serverstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );  ?>

<div class="lefttext_stats span12"> 
	<?php foreach ($this->data[NUMUSERSGEOGROUPED]['serverside'] as $geo):?>
		<span class="label label-info positions">
			<img onerror="this.style.display='none'" src="<?php echo $this->livesite;?>administrator/components/com_jrealtimeanalytics/images/flags/<?php echo strtolower($geo[1]);?>.png"/>
			<?php echo @$this->geotrans[$geo[1]]['name'] ? $this->geotrans[$geo[1]]['name'] : JText::_('COM_JREALTIME_NOTSET');?>
			<span class="badge badge-inverse-info"><?php echo $geo[0];?></span>
		</span>
	<?php endforeach;?> 
</div>

<div class="rightgraph_stats pie span12">
	<a title="<?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION');?>" class="fancybox-image" href="<?php echo JUri::root();?>administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_pie_geolocation.png' . $this->nocache;?>">
		<img src="<?php echo JUri::root();?>administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_pie_geolocation.png' . $this->nocache;?>" />
	</a>
</div>

