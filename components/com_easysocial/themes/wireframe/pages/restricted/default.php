<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-restricted es-page-restricted">

	<?php if ($node instanceof SocialUser) { ?>
	<?php echo $this->html('cover.user', $node, 'pages'); ?>
	<?php } else { ?>
	<?php echo $this->html('cover.page', $node); ?>
	<?php } ?>

	<?php echo $this->html('html.restricted', $label, $text); ?>
</div>