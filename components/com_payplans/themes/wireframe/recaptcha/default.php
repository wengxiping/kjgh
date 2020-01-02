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
<div class="pp-recaptcha">
	<?php if (!$invisible) { ?>
		<div class="pp-recaptcha-wrapper">
			<div id="recaptcha_<?php echo $uid;?>" data-recaptcha-item></div>
		</div>
	<?php } ?>

	<?php if ($invisible) { ?>
		<div class="g-recaptcha" data-sitekey="<?php echo $key;?>" data-badge="bottomleft" data-size="invisible" data-pp-recaptcha-invisible></div>
		<input type="hidden" name="g-recaptcha-response" value="" data-pp-recaptcha-response />
	<?php } ?>
</div>