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
<form action="<?php echo JRoute::_('index.php');?>" method="post">
<div class="o-row">
	<div class="o-col--12 o-col--top">
		<div class="o-card o-card--borderless t-lg-mb--lg">
			<div class="o-card__header o-card__header--nobg t-lg-pl--no">
				<?php echo JText::_('COM_PAYPLANS_FRONT_END_DASHBOARD_USER_DOWNLOAD_REQUEST');?>
			</div>

			<div class="o-card__body">
				<?php if (!$requested) { ?>
					<p data-pp-download-response><?php echo JText::_('COM_PAYPLANS_FRONT_END_DASHBOARD_USER_DOWNLOAD_REQUEST_INFORMATION');?></p>

					<div class="t-text--center t-lg-mt--xl">
						<a href="javascript:void(0);" class="btn btn-pp-primary" data-pp-download-request>
							<?php echo JText::_('COM_PP_REQUEST_ACCOUNT_DATA'); ?>
						</a>
					</div>
				<?php } ?>

				<?php if ($requested && in_array($downloadState, array(PP_DOWNLOAD_REQ_LOCKED, PP_DOWNLOAD_REQ_NEW, PP_DOWNLOAD_REQ_PROCESS))) { ?>
						<?php echo JText::_('COM_PAYPLANS_FRONT_END_DASHBOARD_USER_DOWNLOAD_REQUEST_INPROCESS'); ?>
				<?php } ?>

				<?php if ($downloadState == PP_DOWNLOAD_REQ_READY) { ?>
					<p class="t-lg-mb--xl">
						<?php echo JText::_('COM_PAYPLANS_FRONT_END_DASHBOARD_USER_DOWNLOAD_REQUEST_COMPLETED');?>
					</p>

					<div class="t-text--center">
						<a href="<?php echo PPR::_('index.php?option=com_payplans&view=download&layout=downloadFile'); ?>" class="btn btn-pp-primary">
							<?php echo JText::_('COM_PAYPLANS_FRONT_END_DASHBOARD_USER_DOWNLOAD_DATA_BUTTON'); ?>
						</a>
					</div>
				<?php } ?>

			</div>
		</div>
	</div>
</div>