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
<style>
	.label {
		display: inline-block;
		padding: 4px 4px;
		font-size: 15px;
		font-weight: bold;
		color: #fff;
		white-space: nowrap;
		text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
		background-color: #3a87ad;
		height: 20px;
		width: 200px;
	}
	.label-info {
		font-size: 12px;
	}
	.badge {
		background-color: #FFF;
		color: #3a87ad !important;
		padding: 15px;
	}
</style>

<br/><br/>
<img src="<?php echo JPATH_ROOT;?>/administrator/components/com_jrealtimeanalytics/images/icon-48-statspdf.png"/>&nbsp;&nbsp;&nbsp;&nbsp;
<b style="font-size:18px;">
	<?php echo JText::sprintf('COM_JREALTIME_SERVERSTATS_IP_DETAILS', 
			'<span class="label label-info">' . $this->app->input->get('identifier') . '</span>');
		$addressWidth = '18%';
	?>
</b>
<hr/>
<table>
	<tr>
		<td style="background-color: #d9edf7;" width="<?php echo $addressWidth;?>">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_NAME');?></b><br/>
		</td>
		<td width="34%">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_USERS_DETAILS_VISITEDPAGE');?></b>
		</td>
		<td width="8%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISIT_LIFE');?></b>
		</td>
		<td width="9%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT');?></b>
		</td>
		<td width="7%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS');?></b>
		</td>
		<td width="9%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_BROWSERNAME');?></b>
		</td>
		<td width="9%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_OS_STATS');?></b>
		</td>
		<td width="9%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_DEVICE');?></b>
		</td>
	</tr>
	<?php 
		$totalTime = 0;
		$totalAverageTime = 0;
		$counter = 0;
		foreach ($this->detailData as $userDetail):
	?> 
		<tr>
			<td><?php echo $userDetail->customer_name;?></td>
			<td><?php echo $userDetail->visitedpage;?></td>
			<td><?php echo gmdate('H:i:s', $userDetail->impulse * $this->daemonRefresh);?></td>
			<td><?php echo date('Y-m-d H:i:s',  $userDetail->visit_timestamp);?></td>
			<td><?php echo $userDetail->geolocation;?> <img src="<?php echo JPATH_ROOT;?>/administrator/components/com_jrealtimeanalytics/images/flags/<?php echo strtolower($userDetail->geolocation);?>.png"/></td>
			<td><?php echo $userDetail->browser;?></td>
			<td><?php echo $userDetail->os;?></td>
			<td><?php echo $userDetail->device;?></td>
		</tr>
	<?php 
		$counter++;
		$totalTime += $userDetail->impulse * $this->daemonRefresh;
		$totalAverageTime = $totalTime / $counter;
		endforeach;
	?> 
</table>

<br/><br/>
<div class="statslabel blue">
	<span class="label label-info">
		<?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGES_DETAILS_TOTALDURATION');?>
		<?php echo gmdate('H:i:s', $totalTime);?>
	</span>
</div>
<div class="statslabel blue">
	<span class="label label-info">
		<?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGES_DETAILS_AVERAGEPAGE_DURATION')?>
		<?php echo gmdate('H:i:s', $totalAverageTime);?>
	</span>
</div>