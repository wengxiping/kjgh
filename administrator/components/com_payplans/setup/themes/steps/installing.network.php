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

$license = JRequest::getVar('license');
?>
<form name="installation" method="post" data-installation-form>

	<p class="section-desc">
		<?php echo JText::_('COM_PP_INSTALLATION_INSTALLING_DESC');?>
	</p>

	<div class="alert alert-success" data-installation-completed style="display: none;">
		<?php echo JText::_('COM_PP_INSTALLATION_INSTALLING_COMPLETED'); ?>
	</div>

	<div data-install-progress>
		<ol class="install-logs list-reset" data-progress-logs="">
			<li class="active" data-progress-download>
				<div class="progress-icon">
					<i class="icon-radio-unchecked"></i>
				</div>
				<div class="split__title"><?php echo JText::_('COM_PP_INSTALLATION_INSTALLING_DOWNLOADING_FILES');?></div>
				<span class="progress-state text-info"><?php echo JText::_('COM_PP_INSTALLATION_DOWNLOADING');?></span>
			</li>

			<?php include(dirname(__FILE__) . '/installing.steps.php'); ?>
		</ol>
	</div>

	<input type="hidden" name="option" value="com_payplans" />
	<input type="hidden" name="active" value="<?php echo $active; ?>" />
	<input type="hidden" name="source" data-source />

	<?php if ($reinstall) { ?>
	<input type="hidden" name="reinstall" value="1" />
	<?php } ?>

	<?php if ($update) { ?>
	<input type="hidden" name="update" value="1" />
	<?php } ?>

</form>

<script type="text/javascript">
jQuery(document).ready( function(){

	<?php if ($reinstall) { ?>
		pp.ajaxUrl = "<?php echo JURI::root();?>administrator/index.php?option=com_payplans&ajax=1&reinstall=1&license=<?php echo $license;?>";
	<?php } elseif($update){ ?>
		pp.ajaxUrl = "<?php echo JURI::root();?>administrator/index.php?option=com_payplans&ajax=1&update=1&license=<?php echo $license;?>";
	<?php } else { ?>
		pp.ajaxUrl = "<?php echo JURI::root();?>administrator/index.php?option=com_payplans&ajax=1&license=<?php echo $license;?>";
	<?php } ?>

	// Immediately proceed with installation
	pp.installation.download();
});
</script>
