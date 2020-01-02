<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row">
	<div class="col-md-6">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EVENTS_SETTINGS_ENABLE_EVENTS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.enabled', $this->config->get('events.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EVENTS_SETTINGS_ENABLE_ICAL_EVENTS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.ical', $this->config->get('events.ical')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EVENTS_SETTINGS_ALLOW_MEMBERS_INVITE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.invite.allowmembers', $this->config->get('events.invite.allowmembers')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_SETTINGS_ALLOW_INVITE_NON_FRIENDS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.invite.nonfriends', $this->config->get('events.invite.nonfriends')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EVENTS_SETTINGS_START_OF_WEEK'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'events.startofweek', $this->config->get('events.startofweek'), array(
							array('value' => 1, 'text' => JText::_('MONDAY')),
							array('value' => 2, 'text' => JText::_('TUESDAY')),
							array('value' => 3, 'text' => JTEXT::_('WEDNESDAY')),
							array('value' => 4, 'text' => JTEXT::_('THURSDAY')),
							array('value' => 5, 'text' => JTEXT::_('FRIDAY')),
							array('value' => 6, 'text' => JTEXT::_('SATURDAY')),
							array('value' => 0, 'text' => JTEXT::_('SUNDAY'))
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EVENTS_SETTINGS_RECURRING_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'events.recurringlimit', $this->config->get('events.recurringlimit'), '', array('class' => 'input-short')); ?>
						<?php echo JText::_('COM_EASYSOCIAL_EVENTS'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_FEED_INCLUD_ADMIN'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.feed.includeadmin', $this->config->get('events.feed.includeadmin')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_EVENT_REMINDER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.reminder.enabled', $this->config->get('events.reminder.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_SOCIAL_SHARING_PRIVATE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.sharing.showprivate', $this->config->get('events.sharing.showprivate')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_EVENTS_SETTINGS_NEARBY_EVENTS_RADIUS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'events.nearby.radius', $this->config->get('events.nearby.radius'), array(
							array('value' => 10, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 10)),
							array('value' => 25, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 25)),
							array('value' => 50, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 50)),
							array('value' => 100, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 100)),
							array('value' => 200, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 200)),
							array('value' => 300, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 300)),
							array('value' => 400, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 400)),
							array('value' => 500, 'text' => JText::sprintf('COM_ES_MILE_RADIUS', 500))
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_EVENTS_SETTINGS_TAG_NONFRIENDS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'events.tag.nonfriends', $this->config->get('events.tag.nonfriends')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
