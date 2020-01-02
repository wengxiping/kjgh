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
$license = $input->get('license', '', 'default');
?>
<script type="text/javascript">
jQuery(document).ready( function(){
	<?php if ($reinstall) { ?>
		es.ajaxUrl = "<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&ajax=1&reinstall=1&license=<?php echo $license;?>";
	<?php } elseif($update){ ?>
		es.ajaxUrl = "<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&ajax=1&update=1&license=<?php echo $license;?>";
	<?php } else { ?>
		es.ajaxUrl = "<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&ajax=1&license=<?php echo $license;?>";
	<?php } ?>

	// Immediately proceed with installation
	es.installation.download();
});
</script>

<form name="installation" method="post" data-installation-form>

	<p class="section-desc">
		We are now performing the installation of EasySocial on the site. This process may take a little while depending on the Internet connectivity of your server. While we are at it, you should get some coffee ...
	</p>

	<div class="alert alert-success" data-installation-completed style="display: none;">
		Installation completed successfully. Please click on the Next Step button to proceed.
	</div>

	<div data-install-progress>
		<ol class="install-logs list-reset" data-progress-logs="">
			<li class="active" data-progress-download>
				<div class="progress-icon">
					<i class="icon-radio-unchecked"></i>
				</div>
				<div class="split__title">Downloading Installation Files...</div>
				<span class="progress-state text-info">Downloading</span>
			</li>

			<?php include(dirname(__FILE__) . '/installing.steps.php'); ?>
		</ol>
	</div>

	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="active" value="<?php echo $active; ?>" />
	<input type="hidden" name="source" data-source />

	<?php if ($reinstall) { ?>
	<input type="hidden" name="reinstall" value="1" />
	<?php } ?>

	<?php if ($update) { ?>
	<input type="hidden" name="update" value="1" />
	<?php } ?>

</form>
