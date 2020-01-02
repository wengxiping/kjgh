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
<div class="pp-result-container">
	<form action="https://payment.quickpay.net" method="post" data-pp-quickpay-form>

		<?php echo $this->html('html.redirection'); ?>

		<?php foreach ($payload as $key => $value) { ?>
			<?php echo $this->html('form.hidden', $key, $value); ?>
		<?php } ?>

		<?php echo $this->html('form.hidden', 'checksum', $checksum); ?>
	</form>
</div>