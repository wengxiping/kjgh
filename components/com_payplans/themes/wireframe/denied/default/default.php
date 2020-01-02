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
<div class="pp-access-alert">
	<div class="pp-access-alert__icon"><i class="fas fa-lock"></i></div>
	<div class="pp-access-alert__content">
		<div class="pp-access-alert__title">
			<?php echo JText::_('COM_PP_ACCESS_PROHIBITED');?>
		</div>
		<div class="pp-access-alert__desc t-lg-mt--xl">
			<?php echo JText::_('COM_PP_ACCESS_PROHIBITED_INFO');?>
		</div>
	</div>

	<div class="pp-access-alert__action">
		<a href="<?php echo PPR::_('index.php?option=com_payplans&view=plan');?>" class="btn btn-pp-primary t-lg-mt--xl">
			<?php echo JText::_('COM_PP_VIEW_AVAILABLE_PLANS');?>
		</a>
	</div>
</div>