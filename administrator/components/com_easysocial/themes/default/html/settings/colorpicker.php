<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="form-group <?php echo $wrapperClass;?>" <?php echo $wrapperAttributes;?>>
	<?php echo $this->html('panel.label', $title); ?>

	<div class="col-md-7">
		<?php echo $this->html('form.colorpicker', $name, $this->config->get($name), $reset); ?>
	</div>
</div>