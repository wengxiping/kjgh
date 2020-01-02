<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();?>
<?php $attr = array();?>
<div class="container-fluid well">
	<div class="row-fluid">

		<div class="span4 hidden-phone">&nbsp;</div>
		<div class=" span8 row-fluid">

			<div class="span1 hidden-phone"></div>
			<div class="span11">

				<div class="span3" style="min-width: 100px;">
					<label><?php echo JText::_('COM_PAYPLANS_PLAN_GRID_FILTER_TITLE');?></label>
						<?php $attr['style'] = 'class="pp-filter-width"';?>
						<?php echo PayplansHtml::_('text.filter', 'title', 'app', $filters, 'filter_payplans', $attr);?>
				</div>
				
				<div class="hidden-phone">&nbsp;</div>

				<div class="span3 hidden-phone pp-filter-gap-top" style="min-width: 100px;">
					<?php echo PayplansHtml::_('apptypes.filter', 'type', 'app', $filters, 'filter_payplans', $attr);?>
				</div>
				<div class="span3 hidden-phone pp-filter-gap-top" style="min-width: 100px;">
					<?php echo PayplansHtml::_('boolean.filter', 'published', 'app', $filters, 'filter_payplans', $attr);?>
				</div>
				<div class="span3 pp-filter-gap-top">
					<div><input type="submit" name="filter_submit" class="btn btn-primary pp-filter-width" value="<?php echo JText::_('COM_PAYPLANS_FILTERS_GO');?>" /></div>
					<div><input type="reset"  name="filter_reset"  class="btn pp-filter-width pp-filter-gap-top" value="<?php echo JText::_('COM_PAYPLANS_FILTERS_RESET');?>" onclick="payplansAdmin.resetFilters(this.form);" /></div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
