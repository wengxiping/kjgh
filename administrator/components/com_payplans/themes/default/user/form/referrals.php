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
defined('_JEXEC') or die('Unauthorized Access');;
?>
<div class="panel-table">
	<table class="app-table table">
		<thead>
			<tr>
				<th width="10%">
					<?php echo JText::_('COM_PAYPLANS_USER_EDIT_USER_ID'); ?>
				</th>

				<th class="center" width="45%">
					<?php echo JText::_('COM_PP_REFERRAL_USER'); ?>
				</th>

				<th class="center" width="45%">
					<?php echo JText::_('COM_PP_REFERRAL_USER_PLAN'); ?>
				</th>

			</tr>
		</thead>

		<tbody>
			<?php if ($referralDetails) { ?>
				<?php foreach ($referralDetails as $referral) { ?>
				<tr>
					<td> 
						<?php echo $referral->referral_id->getId(); ?>
					</td>

					<td class="center" width="45%">
						<a href="index.php?option=com_payplans&view=user&layout=form&id=<?php echo $referral->referral_id->getId();?>">
							<?php echo $referral->referral_id->getUsername();?>
						</a>
					</td>

					<td class="center" width="45%">
						<?php echo $referral->plan_id->getTitle();?>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>

			<?php if (!$referralDetails) { ?>
				<?php echo $this->html('grid.emptyBlock', 'COM_PP_USER_EMPTY_REFERRALS', 7); ?>
			<?php } ?>
		</tbody>
	</table>
</div>