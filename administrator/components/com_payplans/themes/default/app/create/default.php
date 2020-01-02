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
<div class="pp-appstore">
	<?php foreach ($apps as $app) { ?>
	<div class="pp-appstore__item">
		<div class="pp-appstore-item">
			<div class="pp-appstore-item__hd">
				<h3 class="pp-appstore-item__title"><?php echo JText::_($app->name);?></h3>
			</div>
			<div class="pp-appstore-item__bd">
				<div class="pp-appstore-item__desc" style="min-height: 100px;">
					<?php echo $app->description;?>
				</div>
			</div>
			<div class="pp-appstore-item__ft">
				<div class="pp-appstore-item__action" >
					<a href="<?php echo JRoute::_('index.php?option=com_payplans&task=app.createInstance&element=' . $app->element . '&view=' . $view . '&layout=' . $layout);?>" class="btn btn-lg btn-pp-primary">
						<?php echo JText::_('COM_PP_SELECT_APP_BUTTON');?>
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>