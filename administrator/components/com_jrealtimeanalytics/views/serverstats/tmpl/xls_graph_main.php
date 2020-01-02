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
$anonymizeIpAddress = $this->cparams->get('anonymize_ipaddress', 0);
?>

<?php if($this->cparams->get('details_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_DETAILS') . $reportDelimiter;?></font></b>
	<table>
		<tr>
			<td>
				<b><?php echo JText::_('COM_JREALTIME_TOTAL_VISITED_PAGES');?>:</b>
				<?php echo $this->data[TOTALVISITEDPAGES];?>
			</td>
			<td>
				<b><?php echo JText::_('COM_JREALTIME_TOTAL_VISITORS');?>:</b>
				<?php echo $this->data[TOTALVISITORS];?>
			</td>
			<td>
				<b><?php echo JText::_('COM_JREALTIME_TOTAL_UNIQUE_VISITORS');?>:</b>
				<?php echo $this->data[TOTALUNIQUEVISITORS];?>
			</td>
			<td>
				<b><?php echo JText::_('COM_JREALTIME_MEDIUM_VISIT_TIME');?>:</b>
				<?php echo $this->data[MEDIUMVISITTIME];?>
			</td>
			<td>
				<b><?php echo JText::_('COM_JREALTIME_MEDIUM_VISITED_PAGES_PERUSER');?>:</b>
				<?php echo $this->data[MEDIUMVISITEDPAGESPERSINGLEUSER];?>
			</td>
			<td>
				<b><?php echo JText::_('COM_JREALTIME_BOUNCE_RATE');?>:</b>
				<?php echo $this->data[BOUNCERATE];?>
			</td>
		</tr>
	</table>
	<?php echo $rowSpacer;?>
<?php endif;?>

<?php if($this->cparams->get('geolocation_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION_AWARE') . $reportDelimiter;?></font></b>
	<table>
		<?php foreach ($this->data[NUMUSERSGEOGROUPED]['serverside'] as $index=>$geo):?>
			<tr>
				<td>
					<b><?php echo !empty($this->geotrans[$geo[1]]['name']) ? $this->geotrans[$geo[1]]['name'] : JText::_('COM_JREALTIME_NOTSET');?>:</b>
				</td>
				<td>
					<?php echo $geo[0];?>
				</td>
			</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif;?>

<?php if($this->cparams->get('os_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_OS') . $reportDelimiter;?></font></b>
	<table>
		<?php foreach ($this->data[NUMUSERSOSGROUPED] as $index=>$os):?>
			<tr>
				<td>
					<b><?php echo $os[1]?>:</b>
				</td>
				<td>
					<?php echo $os[0];?>
				</td>
			</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif;?>

<?php if($this->cparams->get('browser_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_BROWSER') . $reportDelimiter;?></font></b>
	<table>
		<?php foreach ($this->data[NUMUSERSBROWSERGROUPED] as $index=>$browser):?>
			<tr>
				<td>
					<b><?php echo $browser[1]?>:</b>
				</td>
				<td>
					<?php echo $browser[0];?>
				</td>
			</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>

<?php if($this->cparams->get('device_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_DEVICE') . $reportDelimiter;?></font></b>
	<table>
		<?php foreach ($this->data[NUMUSERSDEVICEGROUPED] as $index=>$device):?>
			<tr>
				<td>
					<b><?php echo $device[1]?>:</b>
				</td>
				<td>
					<?php echo $device[0];?>
				</td>
			</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>

<?php if($this->cparams->get('landing_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_LANDING_PAGES') . $reportDelimiter;?></font></b>
	<table>
		<tr>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGE');?></font></b><br/>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_NUMUSERS');?></font></b>
			</td>
		</tr>
		<?php foreach ($this->data[LANDING_PAGES] as $page):?> 
		<tr>
			<td> 
				<?php echo strlen($page[1]) > 100 ? substr($page[1], 0, 100) . '...' : $page[1];?> 
			</td> 
			<td> 
				<?php echo $page[0]?> 
			</td>
		</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>


<?php if($this->cparams->get('leaveoff_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_LEAVEOFF_PAGES') . $reportDelimiter;?></font></b>
	<table>
		<tr>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGE');?></font></b><br/>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_NUMUSERS');?></font></b>
			</td>
		</tr>
		<?php foreach ($this->data[LEAVEOFF_PAGES] as $page):?> 
		<tr>
			<td> 
				<?php echo strlen($page[1]) > 100 ? substr($page[1], 0, 100) . '...' : $page[1];?> 
			</td> 
			<td> 
				<?php echo $page[0]?> 
			</td>
		</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>

<?php if($this->cparams->get('visitsbypage_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGES') . $reportDelimiter;?></font></b>
	<table>
		<tr>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_PAGE');?></font></b><br/>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_LASTVISIT');?></font></b>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_NUMVISITS');?></font></b>
			</td>
		</tr>
		<?php foreach ($this->data[VISITSPERPAGE] as $page):?> 
		<tr>
			<td>  
				<?php echo strlen($page[2]) > 85 ? substr($page[2], 0, 85) . '...' : $page[2];?> 
			</td> 
			<td> 
				<?php echo date('Y-m-d H:i:s', $page[1]);?> 
			</td>
			<td> 
				<?php echo $page[0];?> 
			</td> 
		</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>

<?php if($this->cparams->get('visitsbyuser_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_USERS') . $reportDelimiter;?></font></b>
	<table>
		<tr>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_NAME');?></font></b><br/>
			</td>
			<?php if($this->cparams->get('show_usergroup', 0)):?>
				<td>
					<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_TITLETYPE');?></font></b><br/>
				</td>
			<?php endif;?>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_LASTVISIT');?></font></b>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISIT_LIFE');?></font></b>
			</td>
			<td>
				<b><font size="3" color="#CE1300">Browser</font></b>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_OS_TITLE');?></font></b>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_DEVICE');?></font></b>
			</td>
			<?php if(!$anonymizeIpAddress):?>
				<td>
					<b><font size="3" color="#CE1300"><?php echo JText::_ ( 'COM_JREALTIME_SERVERSTATS_IPADDRESS' ); ?></font></b>
				</td>
			<?php endif;?>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_GEOLOCATION');?></font></b>
			</td>
			<?php if($this->cparams->get('show_referral', 0)):?>
				<td>
					<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_LABEL');?></font></b>
				</td>
			<?php endif;?>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISITED_PAGES');?></font></b>
			</td>
		</tr>
		<?php foreach ($this->data[TOTALVISITEDPAGESPERUSER] as $user):?> 
		<tr>
			<td>  
				<?php echo $user[1];?> 
			</td>
			<?php if($this->cparams->get('show_usergroup', 0)):?>
				<?php if($this->cparams->get('show_referral', 0)):?>
					<td>
						<?php echo $user[11] ? $user[11] : JText::_('COM_JREALTIME_NA');?>
					</td>
				<?php else:?>
					<td>
						<?php echo $user[10] ? $user[10] : JText::_('COM_JREALTIME_NA');?>
					</td>
				<?php endif;?>
			<?php endif;?>
			<td> 
				<?php echo date('Y-m-d H:i:s', $user[2]);?> 
			</td>
			<td> 
				<?php echo gmdate('H:i:s', $user[7] * $this->cparams->get('daemonrefresh'));?>
			</td>
			<td> 
				<?php echo $user[3];?> 
			</td> 
			<td> 
				<?php echo $user[4];?> 
			</td> 
			<td> 
				<?php echo $user[9];?> 
			</td> 
			<?php if(!$anonymizeIpAddress):?>
				<td> 
					<?php echo "( " . $user[6] . " )";?> 
				</td>
			<?php endif;?>
			<td>
				<?php echo $user[8];?>
			</td>
			<?php if($this->cparams->get('show_referral', 0)):?>
				<td>
					<?php echo $user[10] ? $user[10] : JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_LABEL_DIRECT');?>
				</td>
			<?php endif;?>
			<td> 
				<?php echo $user[0];?> 
			</td> 
		</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>

<?php if($this->cparams->get('visitsbyip_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISITSBY_IPADDRESS') . $reportDelimiter;?></font></b>
	<table>
		<tr>
			<td width="18%">
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_IPADDRESS');?></font></b><br/>
			</td>
			<td width="18%">
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_EVENTTITLE_GEOLOCATION');?></font></b><br/>
			</td>
			<td width="18%">
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_LASTVISIT');?></font></b><br/>
			</td>
			<td width="18%">
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISIT_LIFE');?></font></b><br/>
			</td>
			<td width="18%">
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_VISITED_PAGES');?></font></b><br/>
			</td>
		</tr>
		<?php foreach ($this->data[TOTALVISITEDPAGESPERIPADDRESS] as $iprecord):?> 
		<tr>
			<td>
				<?php echo "( " . $iprecord[2] . " )";?>
			</td>
			<td>
				<?php echo $iprecord[4];?>
			</td>
			<td>
				<?php echo date('Y-m-d H:i:s', $iprecord[1]);?>
			</td>
			<td>
				<?php echo gmdate('H:i:s', $iprecord[3] * $this->cparams->get('daemonrefresh'));?>
			</td>
			<td>
				<?php echo $iprecord[0];?>
			</td>
		</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>

<?php if($this->cparams->get('referral_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL') . $reportDelimiter;?></font></b>
	<table>
		<tr>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_LINK');?></font></b><br/>
			</td>
			<?php if(!$anonymizeIpAddress):?>
				<td>
					<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_IPADDRESS');?></font></b><br/>
				</td>
			<?php endif;?>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_COUNTER');?></font></b>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_PERCENTAGE');?></font></b>
			</td>
		</tr>
		<?php foreach ($this->data[REFERRALTRAFFIC] as $referral):?> 
		<tr>
			<td>  
				<?php echo strlen($referral[0]) > 95 ? substr($referral[0], 0, 95) . '...' : $referral[0];?> 
			</td> 
			<?php if(!$anonymizeIpAddress):?>
				<td>  
					<?php echo "( " . ($referral[2] == 1 ? $referral[1] : $referral[2] .  ' ' . JText::_('COM_JREALTIME_SERVERSTATS_MULTIPLE_IP')) . " )";?> 
				</td> 
			<?php endif;?>
			<td>  
				<?php echo $referral[3];?> 
			</td> 
			<td> 
				<?php printf('%.2f', ($referral[3] / $referral[4]) * 100);?>%
			</td> 
		</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>

<?php if($this->cparams->get('searchkeys_stats', true)): ?>
	<b><font size="4" color="#0028D3"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES') . $reportDelimiter;?></font></b>
	<table width="100%">
		<tr>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES_PHRASE');?></font></b><br/>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES_COUNTER');?></font></b>
			</td>
			<td>
				<b><font size="3" color="#CE1300"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_SEARCHES_PERCENTAGE');?></font></b>
			</td>
		</tr>
		<?php foreach ($this->data[SEARCHEDPHRASE] as $phrase):?> 
		<tr>
			<td>  
				<?php echo $phrase[0];?> 
			</td> 
			<td>  
				<?php echo $phrase[1];?> 
			</td> 
			<td> 
				<?php printf('%.2f', ($phrase[1] / $phrase[2]) * 100);?>%
			</td> 
		</tr>
		<?php endforeach;?> 
	</table>
	<?php echo $rowSpacer;?>
<?php endif; ?>