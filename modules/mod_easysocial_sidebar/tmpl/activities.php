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
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar data-es-activities-filters>
		<?php echo $this->lib->render('module', 'es-activities-sidebar-top', 'site/activities/sidebar.module.wrapper'); ?>

		<?php echo $this->lib->render('widgets', SOCIAL_TYPE_USER, 'activities', 'sidebarTop'); ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_ACTIVITY_SIDEBAR_FILTER'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item <?php echo $active == 'all' ? ' active' : '';?>"
						data-sidebar-item data-type="all">
						<a href="<?php echo FRoute::activities();?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_ALL_ACTIVITIES');?>">
							<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_ALL_ACTIVITIES');?>
						</a>
					</li>
					<li class="o-tabs__item <?php echo $active == 'hidden' ? ' active' : '';?>"
						data-sidebar-item data-type="hidden">
						<a href="<?php echo FRoute::activities(array('type' => 'hidden'));?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTIVITIES');?>">
							<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTIVITIES');?>
						</a>
					</li>

					<li class="o-tabs__item <?php echo $active == 'hiddenapp' ? ' active' : '';?>"
						data-sidebar-item data-type="hiddenapp">
						<a href="<?php echo FRoute::activities(array('type' => 'hiddenapp'));?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS');?>">
							<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS');?>
						</a>
					</li>

					<li class="o-tabs__item <?php echo $active == 'hiddenactor' ? ' active' : '';?>"
						data-sidebar-item
						data-type="hiddenactor">
						<a href="<?php echo FRoute::activities(array('type' => 'hiddenactor'));?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS');?>">
							<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS');?>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<?php echo $this->lib->render('widgets', SOCIAL_TYPE_USER, 'activities', 'sidebarMiddle'); ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_NOTIFICATIONS_GROUP_OTHERS'); ?>

			<div class="es-side-widget__bd">
				<?php if ($apps) { ?>
					<ul class="o-tabs o-tabs--stacked" data-activity-apps>
						<?php foreach ($apps as $app) { ?>
							<li class="o-tabs__item <?php echo $app->element == $active ? ' active' : '';?>"
								data-sidebar-item data-type="<?php echo $app->element; ?>">
								<a href="<?php echo FRoute::activities(array('type' => $app->element));?>" class="o-tabs__link">
									<?php echo JText::_($app->title); ?>
								</a>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<div class="t-text--muted"><?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_NO_APPS'); ?></div>
				<?php } ?>
			</div>
		</div>

		<?php echo $this->lib->render('widgets', SOCIAL_TYPE_USER, 'activities', 'sidebarBottom'); ?>

		<?php echo $this->lib->render('module', 'es-activities-sidebar-bottom', 'site/activities/sidebar.module.wrapper'); ?>
	</div>
</div>

<script type="text/javascript">
EasySocial
.require()
.script('site/activities/filter')
.done(function($){
	$('body').addController(EasySocial.Controller.Activities.Filter);
});
</script>
