<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no">
		<?php echo JText::_("PLG_PP_SOCIALDISCOUNT_TITLE");?>
	</div>

	<div class="o-card__body">
		<p class="t-lg-mb--lg"><?php echo JText::_("PLG_PP_SOCIALDISCOUNT_DESCRIPTION"); ?></p>
		
		<div class="pp-social-discount">
			<?php foreach ($output as $buttonHtml) { ?>
				<div class="pp-social-discount__item">
					<?php echo $buttonHtml; ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>