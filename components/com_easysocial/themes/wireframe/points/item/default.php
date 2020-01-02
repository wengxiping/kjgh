<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container">
	<div class="es-content">

		<div class="es-stage es-island">

			<div class="es-stage__curtain es-bleed--top">

				<h3 class="es-stage__title">
					 <?php echo $point->get('title'); ?>
				</h3>
				<div class="es-stage__desc">
					 <?php echo $point->get( 'description' ); ?>
				</div>
				<div class="es-stage__actor">
					<div class="es-stage__actor-img es-stage__actor-img--rounded">
						<div class="es-point-badge <?php echo $point->points < 0 ? ' es-point-badge--alert' : '';?><?php echo $point->points > 10 ? ' es-point-badge--success' : '';?>">
							<?php echo $point->points;?>
						</div>
					</div>
				</div>
			</div>

			<div class="es-stage__audience">
				
				<div class="es-stage__audience-title">
					<b><?php echo JText::_('COM_EASYSOCIAL_POINTS_ACHIEVERS');?>:</b>
					<span><?php echo $point->getTotalAchievers();?></span>
				</div>

				<div class="es-stage__audience-result">
					<div class=" <?php echo !$achievers ? 'is-empty' : '';?>">

						<?php if ($achievers) { ?>
						<ul class="g-list-inline t-text--center">
							<?php foreach ($achievers as $user) { ?>
							<li class="t-lg-mb--md t-lg-mr--lg">
								<?php echo $this->html('avatar.user', $user); ?>
							</li>
							<?php } ?>
						</ul>
						<?php } ?>

						<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_POINTS_NO_ACHIEVERS_YET', 'fa-users'); ?>

					</div>
				</div>

			</div>
		</div>

	</div>	
</div>