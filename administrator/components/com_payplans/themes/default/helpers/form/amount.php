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

<?php if ($currencyBeforeAfter == 'before') { ?>
	<span class="pp-currency"><?php echo $currency; ?></span>&nbsp;
	<span class="pp-amount"><?php echo $amount; ?></span>
<?php } else { ?>
	<span class="pp-amount"><?php echo $amount; ?></span>&nbsp;
	<span class="pp-currency"><?php echo $currency; ?></span>
<?php } ?>