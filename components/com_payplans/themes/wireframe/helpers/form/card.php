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
<div class="card-js" data-capture-name="true" data-card-<?php echo $uuid;?>>
	<?php if ($inputNames->name) { ?>
	<input class="name" name="<?php echo $inputNames->name;?>" placeholder="<?php echo JText::_('COM_PP_NAME_ON_CARD');?>" value="<?php echo $inputNames->nameValue;?>" />
	<?php } ?>

	<input class="card-number" name="<?php echo $inputNames->card;?>" placeholder="<?php echo JText::_('COM_PP_CARD_NUMBER');?>" value="<?php echo $inputNames->cardValue;?>" />
	<input class="expiry-month" name="<?php echo $inputNames->expireMonth;?>" value="<?php echo $inputNames->expireMonthValue;?>" />
	<input class="expiry-year" name="<?php echo $inputNames->expireYear;?>" value="<?php echo $inputNames->expireYearValue;?>" />
	<input class="cvc" name="<?php echo $inputNames->code;?>" placeholder="<?php echo JText::_('COM_PP_CVV_OR_CCV');?>" value="<?php echo $inputNames->codeValue;?>" />
</div>