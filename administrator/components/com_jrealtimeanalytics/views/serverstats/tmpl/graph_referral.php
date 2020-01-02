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
<table id="jrealtime_table_serverstats_referral" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_LINK');?></span></th>
			<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
				<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_IPADDRESS');?></span></th>
			<?php endif;?>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_COUNTER');?></span></th>
			<th class="title"><span class="label label-info"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_REFERRAL_PERCENTAGE');?></span></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->data[REFERRALTRAFFIC] as $referral):?> 
			<tr>
				<td><?php echo $referral[0];?></td>
				<?php if(!$this->cparams->get('anonymize_ipaddress', 0)):?>
					<td>
						<?php if($referral[2] == 1):?>
							<a data-ip="<?php echo $referral[1];?>" class="hasClickPopover" href="javascript:void(0);"><?php echo $referral[1];?></a>
						<?php else: ?>
							<a class="preview badge badge-info" href="<?php echo JRoute::_('index.php?option=com_jrealtimeanalytics&task=serverstats.showEntity&tmpl=component&details=referral&identifier=' . rawurlencode($referral[0]));?>" class="preview"><?php echo $referral[2];?></a>
						<?php endif; ?>
					</td>
				<?php endif;?>
				<td><?php echo $referral[3];?></td>
				<td><?php printf('%.2f', ($referral[3] / $referral[4]) * 100);?>%</td>
			</tr>
		<?php endforeach;?> 
	</tbody>
</table>