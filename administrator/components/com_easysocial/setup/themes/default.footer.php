<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($active != 'complete') { ?>
<script type="text/javascript">
$(document).ready( function(){

	var previous = $('[data-installation-nav-prev]'),
		active = $('[data-installation-form-nav-active]'),
		nav = $('[data-installation-form-nav]'),
		retry = $('[data-installation-retry]'),
		cancel = $('[data-installation-nav-cancel]'),
		loading = $('[data-installation-loading]'),
		steps = $('[data-installation-steps]');


	previous.on('click', function() {
		active.val(<?php echo $active - 2;?>);
		nav.submit();
	});

	cancel.on('click', function() {
		window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&exitInstallation=true';
	});

	retry.on('click', function() {

		steps.removeClass('error');

		var step = $(this).data('retry-step');

		$(this).addClass('hide');

		loading.removeClass('hide');

		window['es']['installation'][step]();
	});
});
</script>

<form action="index.php?option=com_easysocial" method="post" data-installation-form-nav class="hidden">
	<input type="hidden" name="active" value="" data-installation-form-nav-active />
	<input type="hidden" name="option" value="com_easysocial" />

	<?php if (ES_INSTALLER == 'launcher') { ?>
	<input type="hidden" name="method" value="network" />
	<?php } ?>

	<?php if (ES_INSTALLER == 'full' || ES_BETA) { ?>
	<input type="hidden" name="method" value="directory" />
	<?php } ?>

	<?php if ($reinstall) { ?>
	<input type="hidden" name="reinstall" value="1" />
	<?php } ?>

	<?php if ($update) { ?>
	<input type="hidden" name="update" value="1" />
	<?php } ?>
</form>


<div class="container">
	<div class="navi row-table">
		<a href="javascript:void(0);" class="col-cell" <?php echo $active > 1 ? ' data-installation-nav-prev' : ' data-installation-nav-cancel';?>>
			<b>
				<span>
					<?php if ($active > 1) { ?>
						<?php echo JText::_('Previous'); ?>
					<?php } else { ?>
						<?php echo JText::_('Exit Installation'); ?>
					<?php } ?>
				</span>
			</b>
		</a>

		<a href="javascript:void(0);" class="col-cell primary" data-installation-submit>
			<b>
				<span><?php echo JText::_('Next'); ?></span>
			</b>
		</a>

		<a href="javascript:void(0);" class="col-cell loading hide disabled" data-installation-loading>
			<b>
				<span><?php echo JText::_('Loading'); ?></span>
				<span>
					<b class="ui loader"></b>
				</span>
			</b>
		</a>

		<a href="javascript:void(0);" class="col-cell primary hide" data-installation-retry>
			<b>
				<span><?php echo JText::_('Retry'); ?></span>
			</b>
		</a>
	</div>
</div>
<?php } ?>

<?php if ($active == 'complete') { ?>
<div class="container">
	<div class="navi row-table"<?php echo $unsyncedPrivacyCount ? ' style="border-color: #ebccd1"' : ''; ?>>
		<?php if ($unsyncedPrivacyCount) { ?>

			<div style="margin-bottom: 0;padding: 15px 24px;font-size: 12px; background-color: #f2dede; color: #FC595B;">
				<div class="row-table">
					<div class="col-cell cell-tight">
						<i class="app-alert__icon fa fa-bolt"></i>
					</div>
					
					<div class="col-cell alert-message">
						There is an important improvement made to speed up EasySocial in this release. You will need to run the maintenance script
					</div>

					<div class="col-cell cell-tight" style="text-align: right;">
						<a href="/administrator/index.php?option=com_easysocial&amp;view=maintenance&amp;layout=privacy" class="btn btn-danger" style="color:#fff;">
							<b style="color:#FFF;">Run Maintenance Script &rarr;</b>
						</a>
					</div>
				</div>
			</div>


		<?php } else { ?>
			<a class="col-cell primary" href="<?php echo JURI::root();?>index.php?option=com_easysocial" target="_blank">
				<b><span><?php echo JText::_('Launch Frontend');?></span></b>
			</a>
			<a class="col-cell primary" href="<?php echo JURI::root();?>administrator/index.php?option=com_easysocial">
				<b><span><?php echo JText::_('Launch Backend');?></span></b>
			</a>
		<?php } ?>
	</div>
</div>
<?php } ?>
