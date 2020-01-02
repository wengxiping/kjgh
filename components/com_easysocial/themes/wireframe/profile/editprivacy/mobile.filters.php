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
<div class="es-mobile-filter" data-es-mobile-filters>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY', 'privacy', true, 'fa fa-eye'); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_BLOCKED_USERS', 'blocked', false, 'fa fa-lock', false, array('data-filter-item="blocked"')); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_OTHER_LINKS', 'others', false, 'fa fa-link', true); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-mobile-filter__bd" data-es-events-filters>
		<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="privacy">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php $i = 0; ?>
						<?php  foreach ($privacy as $group) {  ?>
							<?php echo $this->html('mobile.filterTab', $group->title, 'javascript:void(0)', ($i == 0 && !$activeTab) || ($activeTab == $group->element), array('data-filter-item="'.$group->element.'"')); ?>
							<?php $i++; ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group" data-es-swiper-group data-type="others">
			<div class="dl-menu-wrapper t-hidden">
				<div class="es-list">
					<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'edit')), 'linkTitle' => JText::_('COM_EASYSOCIAL_TOOLBAR_EDIT_PROFILE'))); ?>

					<?php if ($this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editNotifications')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_ALERTS'))); ?>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
