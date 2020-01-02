<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-card o-card--borderless t-lg-mt--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_GIFT_FORM_HEADING');?></div>

	<div class="o-card__body">
		<div>
			<p class="t-lg-mb--md">
				<?php echo JText::_('COM_PP_GIFT_DESC');?>
			</p>
			
			<a href="javascript:void(0);" class="btn btn-pp-primary" data-pp-gift-purchase>
				<i class="fa fa-gift"></i>&nbsp; <?php echo JText::_('COM_PP_ADD_GIFTS');?>
			</a>
		</div>
	</div>
</div>