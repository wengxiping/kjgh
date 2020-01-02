<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	06 August 2012
 * @file name	:	views/membership/tmpl/invoice.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Generate and Print invoice (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 $app 			 = JFactory::getApplication();
 $config 		 = JblanceHelper::getConfig();
 $dformat 		 = $config->dateFormat;
 $invoicedetails = $config->invoiceDetails;
 $tax_name	 	 = $config->taxName;
 
 $type 		= $app->input->get('type', '', 'string');
 $usertype 	= $app->input->get('usertype', '', 'string');
 
 $this->row->dformat = $dformat;
 $this->row->taxname  = $tax_name;
 $this->row->usertype = $usertype;
?>
	<button type="button" class="btn" onclick="window.print();"><i class="jbf-icon-print"></i> <?php echo JText::_('COM_JBLANCE_PRINT'); ?></button>
	<div class="sp10">&nbsp;</div>
	<table style="width: 100%;">
		<tr class="well">
			<td style="padding: 20px"><strong><?php echo $this->row->invoiceTitle; ?></strong>: <?php echo $this->row->invoiceNo; ?></td>
			<td style="padding: 20px"><strong><?php echo JText::_('COM_JBLANCE_INVOICE_DATE'); ?></strong>: <?php echo JHtml::_('date', $this->row->invoiceDate, $dformat, true); ?></td>
		</tr>
		<tr>
			<td valign="top" style="padding: 20px">
				<?php echo JText::_('COM_JBLANCE_INVOICE_TO'); ?>:<br />
				<address>
					<strong><?php echo !empty($this->row->biz_name) ? $this->row->biz_name.'<br/>' : ''; ?></strong>
					<strong><?php echo $this->row->name; ?></strong><br />
					<?php echo $this->row->address; ?><br>
					<?php echo JblanceHelper::getLocationNames($this->row->id_location, 'only-location', ','); ?><br>
					<?php echo $this->row->postcode; ?><br>
					<?php echo JText::_('COM_JBLANCE_EMAIL'); ?>: <?php echo $this->row->email; ?>
				</address>
			</td>
			<td valign="top" style="padding: 20px">
				<?php echo JText::_('COM_JBLANCE_PROVIDED_BY'); ?>:<br />
				<address>
					<strong><?php echo $app->get('sitename');?></strong> <br />
					<?php echo JUri::base(); ?><br/>
					<?php echo $invoicedetails; ?>
				</address>
			</td>
		</tr>
		<?php if(!($type == 'project' || $type == 'service')) { ?>
		<tr class="well">
			<td style="padding: 10px" colspan="2"><strong><?php echo JText::_('COM_JBLANCE_PAY_MODE'); ?>:</strong> <?php echo JblanceHelper::getGwayName($this->row->gateway); ?></td>
		</tr>
		<?php } ?>
	</table>
	
	<div class="sp10">&nbsp;</div>
	
	<?php 
	if($type == 'deposit')
		echo JLayoutHelper::render('invoice.deposit',  $this->row);
	elseif($type == 'withdraw')
		echo JLayoutHelper::render('invoice.withdraw',  $this->row); 
	elseif($type == 'plan')
		echo JLayoutHelper::render('invoice.plan',  $this->row); 
	elseif($type == 'project')
		echo JLayoutHelper::render('invoice.project',  array('display' => $this->row, 'escrow' => $this->escrows)); 
	elseif($type == 'service')
		echo JLayoutHelper::render('invoice.service',  array('display' => $this->row, 'escrow' => $this->escrows)); 
	?>
	<div class="well well-small jb-aligncenter fontupper">
		<?php echo JText::_('COM_JBLANCE_WE_THANK_YOU_FOR_YOUR_BUSINESS'); ?>
	</div>
