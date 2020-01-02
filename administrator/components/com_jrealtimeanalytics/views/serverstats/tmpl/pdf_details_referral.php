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
<b style="font-size:16px;">
	<?php echo JText::sprintf('COM_JREALTIME_SERVERSTATS_REFERRAL_DETAILS', 
			'<br/><span class="label label-info">' . $this->app->input->getString('identifier') . '</span>');
		$addressWidth = '18%';
	?>
</b>
<hr/>
<table>
	<tr>
		<td style="background-color: #d9edf7;" width="<?php echo $addressWidth;?>">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_IPADDRESS');?></b><br/>
		</td>
		<td width="40%">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS');?></b>
		</td>
		<td width="40%">
			<b style="font-size:13px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT');?></b>
		</td>
	</tr>
	<?php 
		foreach ($this->detailData as $userDetail):
	?> 
		<tr>
			<td><?php echo $userDetail->ip;?></td>
			<td><?php if($userDetail->geolocation) : ?>
					<?php echo $userDetail->geolocation;?> 
					<img src="<?php echo JPATH_ROOT;?>/administrator/components/com_jrealtimeanalytics/images/flags/<?php echo strtolower($userDetail->geolocation);?>.png"/>
				<?php else : ?>
					<?php echo JText::_('COM_JREALTIME_NOTSET');?>
				<?php endif; ?>
			</td>
			<td><?php echo $userDetail->record_date;?></td>
		</tr>
	<?php 
		endforeach;
	?> 
</table>