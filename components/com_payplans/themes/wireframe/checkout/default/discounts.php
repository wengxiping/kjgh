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
<div class="pp-discount">
	<div class="pp-discount__form" style="flex: 100%;">
		<div class="o-form-group" data-pp-discount-wrapper>
			<div class="o-input-group">
				<input type="text" class="o-form-control" placeholder="<?php echo JText::_('COM_PP_CHECKOUT_DISCOUNT_CODE');?>" data-pp-discount-code />
				<span class="o-input-group__append">
					<button class="btn btn-pp-default-o" type="button" data-pp-discount-apply>
						<?php echo JText::_('COM_PP_APPLY_BUTTON');?>
					</button>
				</span>
			</div>

			<div class="t-text--danger" data-pp-discount-message></div>
		</div>
	</div>
</div>