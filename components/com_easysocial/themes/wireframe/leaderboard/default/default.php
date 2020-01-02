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
<div class="es-container">
	<div class="es-content">

		<div class="es-stage es-island">

			<div class="es-stage__curtain es-stage__curtain--off">

				<h3 class="es-stage__title">
					<?php echo JText::_('COM_EASYSOCIAL_POINTS_LEADERBOARD');?>
				</h3>
				<div class="es-stage__desc">
					<?php echo JText::_('COM_EASYSOCIAL_POINTS_LEADERBOARD_DESC'); ?><br>
					<a href="<?php echo ESR::points();?>"><?php echo JText::_('COM_EASYSOCIAL_EARN_MORE_POINTS');?></a>
				</div>
				

			</div>

			<div class="es-stage__audience">
				
				<?php echo $this->render('module', 'es-leaderboard-before-contents'); ?>

				<div class="es-stage__audience-result es-bleed--middle">
					<table class="es-leaderboard">
						<thead>
							<tr>
								<th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_RANK');?></th>
								<th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_USER');?></th>
								<th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_POINTS');?></th>
							</tr>
						</thead>
						<tbody>
							<?php $i = 1; ?>
							<?php foreach ($users as $user) { ?>
							<tr>
								<td>
									<div class="es-leader-badge es-leader-badge--<?php echo $i;?>">
										<span><?php echo $i;?></span>
									</div>
								</td>
								<td>
									<div class="o-flag" >
										<div class="o-flag__image">
											<?php echo $this->html('avatar.user', $user); ?>
										</div>
										<div class="o-flag__body">
											<?php echo $this->html('html.user', $user); ?>
										</div>
									</div>
								</td>
								<td>
									<span class="es-leaderboard__points"><?php echo $user->getPoints();?></span>
									<span><?php echo JText::_('COM_EASYSOCIAL_POINTS');?></span>
								</td>
							</tr>
							<?php $i++; ?>
							<?php } ?>
						</tbody>
					</table>
				</div>

			</div>
		</div>

		<?php echo $this->render('module' , 'es-leaderboard-after-contents'); ?>

		
	</div>
</div>