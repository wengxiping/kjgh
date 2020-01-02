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
<div class="text-center">
	<div style="margin-top: 80px;">
		<i class="eb-complete-icon icon-signup"></i>
	</div>

	<h3 style="margin-top: 50px;">Installation Completed</h3>
	<br />

	<?php if (!$unsyncedPrivacyCount) { ?>
		<p>Congratulations! EasySocial has been successfully installed on your site and you may start using it.</p>

		<br />

		<a class="btn btn-social facebook" href="https://facebook.com/StackIdeas" target="_blank">Like us on Facebook</a>

		<a class="btn btn-social twitter" href="https://twitter.com/Stackideas" target="_blank">Follow us on Twitter</a>
	<?php } ?>

	<?php if ($unsyncedPrivacyCount) { ?>
		<p>
			EasySocial has been successfully updated on your site.<br /><br />
			There are important changes to improve the performance of EasySocial and <br />
			you will need to run the maintenance script to complete the installation process.
		</p>
	<?php } ?>
</div>