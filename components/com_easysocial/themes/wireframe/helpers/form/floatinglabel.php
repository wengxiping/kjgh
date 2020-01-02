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
<div class="<?php echo $static ? '' : 'o-form-group';?> o-form-group--float <?php echo $static ? 'is-focused' : '';?>">
	<label class="o-control-label" for="<?php echo $id;?>"><?php echo $label;?></label>
	
	<?php echo $this->html('form.' . $type, $name, $id, $value, array('class' => 'o-form-control ' . $inputClass, 'attr' => 'autocomplete="off" ' . $inputAttributes)); ?>
</div>