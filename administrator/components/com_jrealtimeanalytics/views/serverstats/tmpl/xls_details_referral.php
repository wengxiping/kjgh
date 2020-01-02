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
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
$rowSpacer = '<p></p>';
$reportDelimiter = '_________________';
?>

<b>
	<font size="4" color="#0028D3">
		<?php echo JText::sprintf('COM_JREALTIME_SERVERSTATS_REFERRAL_DETAILS', 
			$this->app->input->getString('identifier'));
		?>
	</font>
</b>
<?php echo $rowSpacer;?>

<table>
	<tr>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_SERVERSTATS_IPADDRESS');?></b></font>
		</td>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION_STATS');?></b></font>
		</td>
		<td>
			<font size="3" color="#CE1300"><b><?php echo JText::_('COM_JREALTIME_SERVERSTATS_USERS_DETAILS_LASTVISIT');?></b></font>
		</td>
	</tr>
	<?php 
		foreach ($this->detailData as $userDetail):
	?> 
		<tr>
			<td><?php echo "( " . $userDetail->ip . " )";?></td>
			<td><?php echo ($userDetail->geolocation ? $userDetail->geolocation : JText::_('COM_JREALTIME_NOTSET'));?></td>
			<td><?php echo $userDetail->record_date;?></td>
		</tr>
	<?php 
		endforeach;
	?> 
</table>
<?php echo $rowSpacer;?>