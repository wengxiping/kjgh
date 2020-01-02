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
<div class="es-view-page-as" data-postas-base data-clusterid="<?php echo $clusterId; ?>" data-return-url="<?php echo base64_encode(ESR::current()); ?>">
	
	<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-bs-toggle="dropdown" data-postas-toggle data-original-title="<?php echo JText::_('COM_ES_VIEW_PAGE_AS'); ?> <?php echo $actor->getName(); ?>" data-es-provide="tooltip" data-placement="top">
		<div class="o-avatar o-avatar--xs" data-postas-avatar data-name="<?php echo $actor->getName();?>"> 
			<img src="<?php echo $actor->getAvatar(SOCIAL_AVATAR_MEDIUM);?>" alt="<?php echo $this->html('string.escape', $actor->getName());?>" width="32" height="32" />
		</div>
		<i class="i-chevron i-chevron--down" data-postas-icon></i>
	</button>

	<ul class="dropdown-menu dropdown-menu-right dropdown-menu--post-as" data-postas-menu>
		<li data-item data-value="user" class="<?php echo $actor->getType() == 'user' ? 'is-active' : ''; ?>" >
			<a href="javascript:void(0);">
				<span class="o-media">
					<span class="o-media__image">
						<span class="o-avatar o-avatar--sm" data-postas-avatar data-name="<?php echo $items['user']->getName();?>">
							<img src="<?php echo $items['user']->getAvatar(SOCIAL_AVATAR_MEDIUM);?>" alt="<?php echo $this->html('string.escape', $items['user']->getName());?>" width="32" height="32" />
						</span>
					</span>
					<span class="o-media__body o-media__body--text-overflow">
						<?php echo $items['user']->getName(); ?>
					</span>
				</span>
			</a>
		</li>

		<li data-item data-value="page" class="<?php echo $actor->getType() == 'page' ? 'is-active' : ''; ?>" >
			<a href="javascript:void(0);">
				<span class="o-media">
					<span class="o-media__image">
						<span class="o-avatar o-avatar--sm" data-postas-avatar data-name="<?php echo $items['page']->getName();?>">
							<img src="<?php echo $items['page']->getAvatar(SOCIAL_AVATAR_MEDIUM);?>" alt="<?php echo $this->html('string.escape', $items['page']->getName());?>" width="32" height="32" />
						</span>
					</span>
					<span class="o-media__body o-media__body--text-overflow">
						<?php echo $items['page']->getName(); ?>	
					</span>
				</span>
			</a>
		</li>
	</ul>
	<input type="hidden" name="postas" autocomplete="off"  value="<?php echo $actor->getType(); ?>" data-postas-hidden />
</div>