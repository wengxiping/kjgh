<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-convo-messages__item<?php echo $conversation->getUserClassName($message); ?>" data-es-message>
	<div class="o-media o-media--top es-convo-messages__item-content">
		<div class="o-media__image">
			<?php echo $this->html('avatar.user', $message->getCreator()); ?>
		</div>
		<div class="o-media__body">
			<div class="es-convo-messages__item-message">
				<div class="es-user-name t-lg-mb--sm">
					<?php echo $this->html('html.user', $message->getCreator()); ?>
				</div>
				<div class="es-convo-text t-lg-mb--sm">
					<?php echo $message->getContents(); ?>
				</div>
				<div>
					<?php echo $this->output('site/conversations/message/location', array('location' => $message->getLocation())); ?>
				</div>
				<div>
					<?php echo $this->output('site/conversations/message/attachment', array('attachments' => $message->getAttachments())); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="es-convo-messages__time">
		<?php echo $message->getRepliedDate(); ?>
	</div>
</div>