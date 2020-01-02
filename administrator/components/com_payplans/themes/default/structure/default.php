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
<div id="pp" class="pp-backend">
	<div class="pp-backend__overlay"><div class="o-loader is-active"></div></div>

	<?php if ($this->tmpl != 'component') { ?>
	<div style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;" class="app-alert o-alert o-alert--danger t-hidden">
		<div class="row-table">
			<div class="col-cell cell-tight"><i class="app-alert__icon fa fa-bolt"></i></div>
			<div class="col-cell alert-message">You do not have a subscription or your subscription has already expired. You will not be able to utilize our versioning services</div>
		</div>
	</div>
	<div class="app-alert o-alert o-alert--danger t-hidden" style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;" data-outdated-banner="">
		<div class="row-table">
			<div class="col-cell cell-tight">
				<i class="app-alert__icon fa fa-bolt"></i>
			</div>
			<div class="col-cell alert-message">
				<?php echo JText::_('COM_PP_OUTDATED_VERSION'); ?>
			</div>
			<div class="col-cell cell-tight">
				<a href="<?php echo JRoute::_('index.php?option=com_payplans&task=system.upgrade');?>" class="btn btn-pp-primary">
					<b><i class="fa fa-bolt"></i>&nbsp; <?php echo JText::_('COM_PP_UPDATE_NOW'); ?></b>
				</a>
			</div>
		</div>
	</div>
	<div class="app-devmode is-on alert warning hide">
		<div class="row-table">
			<div class="col-cell cell-tight">
				<i class="fa fa-info-circle"></i>
			</div>
			<div class="col-cell pl-10 pr-10">
				You are currently running on <b>Development environment</b> and your <b>javascript files are not compressed</b>. This will cause performance downgrade while using EasySocial.
			</div>
			<div class="col-cell cell-tight">
				<a href="/administrator/index.php?option=com_payplans&amp;view=config&amp;layout=system" class="btn btn-pp-danger">
					<b><i class="fa fa-cog"></i>&nbsp; Configure</b>view=config&layout=system
				</a>
			</div>
		</div>
	</div>
	<?php } ?>

	<div class="app " data-pp-app="">

		<div class="container-nav hidden">
			<a class="nav-sidebar-toggle" data-bp-toggle="collapse" data-target=".app-sidebar-collapse">
				<i class="fa fa-bars"></i>
			</a>
			<a class="nav-subhead-toggle" data-bp-toggle="collapse" data-target=".subhead-collapse">
				<i class="fa fa-cog"></i>
			</a>
		</div>

		<?php if (!$isStyleguide && $this->tmpl != 'component') { ?>
			<?php echo $sidebar; ?>
		<?php } ?>

		<div class="app-content">
			<?php if ($this->tmpl != 'component') { ?>
				<?php echo PP::info()->html(); ?>

				<div class="app-head">
					<h2 data-structure-heading=""><?php echo $page->heading; ?></h2>
					<p data-structure-description=""><?php echo $page->description; ?></p>
					<div class="app-head__action t-hidden" data-structure-custom-action="">
					</div>
				</div>
			<?php } ?>
			
			<div class="app-body accordion">

				<?php echo $contents; ?>
			</div>
		</div>
	</div>
</div>
