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
<div class="o-onoffswitch" data-pp-toggler>
	<input name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="o-onoffswitch__checkbox" <?php echo $value == 1 ? 'checked' : ''; ?> value="1" data-toggler-checkbox type="checkbox"  <?php echo $attributes;?> />
	<label class="o-onoffswitch__label" for="<?php echo $id; ?>"></label>
	<input name="<?php echo $name; ?>" value="<?php echo $value; ?>" type="hidden" />
</div>