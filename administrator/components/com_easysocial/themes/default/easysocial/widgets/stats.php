<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="panel">
	<div class="panel-body dash-summary">
		<section class="dash-version is-loading" data-version-status>
			<div class="row-table">
				<div class="col-cell cell-icon cell-tight">
					<i class="fa fa-thumbs-down"></i>
					<i class="fa fa-thumbs-up"></i>
					<b class="o-loader"></b>
				</div>

				<div class="col-cell">
					<h4 class="heading-outdated text-danger"><?php echo JText::_('COM_EASYSOCIAL_VERSION_OUTDATED_VERSION_INFO');?></h4>
					<h4 class="heading-updated"><?php echo JText::_('COM_EASYSOCIAL_VERSION_HEADER_UP_TO_DATE');?></h4>
					<h4 class="heading-loading"><?php echo JText::_('COM_EASYSOCIAL_CHECKING_VERSIONS');?></h4>
					<div class="version-installed hide" data-version-installed>
						<?php echo JText::_('COM_EASYSOCIAL_VERSION_INSTALLED_VERSION');?>: <span data-current-version></span>
						<span class="version-latest text-success">&nbsp; <?php echo JText::_('COM_EASYSOCIAL_VERSION_LATEST_VERSION');?>: <span data-latest-version></span></span>
						</div>
						</div>

				<div class="col-cell cell-btn cell-tight">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&controller=system&task=upgrade');?>" class="btn btn-default"><?php echo JText::_('COM_EASYSOCIAL_GET_UPDATES_BUTTON');?></a>
				</div>
			</div>
		</section>

		<section class="dash-apps-version <?php echo $appUpdates ? 'is-outdated' : 'is-updated';?>" data-apps-version-status>
			<div class="row-table">
				<div class="col-cell cell-icon cell-tight">
					<i class="fa fa-sync-alt"></i>
					<i class="fa fa-lightbulb"></i>
					<b class="o-loader"></b>
				</div>

				<div class="col-cell">
					<h4 class="heading-outdated">
						<?php echo JText::sprintf('COM_EASYSOCIAL_APPS_REQUIRING_UPDATES', $appUpdates); ?>
					</h4>
					<h4 class="heading-updated">
						<?php echo JText::_('COM_EASYSOCIAL_APPS_UP_TO_DATE');?>
					</h4>
				</div>

				<div class="col-cell cell-btn cell-tight">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=apps&filter=outdated');?>" class="btn btn-default"><?php echo JText::_('COM_EASYSOCIAL_VIEW_APPS_BUTTON');?></a>
				</div>
			</div>
		</section>

		<section class="dash-stat">
			<div class="text-center clearfix">
				<div class="dash-stat-item">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=users');?>">
						<b><?php echo $totalUsers; ?></b>
						<div><?php echo JText::_('COM_EASYSOCIAL_USERS');?></div>
					</a>
				</div>

				<div class="dash-stat-item">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=groups');?>">
						<b><?php echo $totalGroups;?></b>
						<div><?php echo JText::_('COM_EASYSOCIAL_GROUPS');?></div>
					</a>
				</div>

				<div class="dash-stat-item">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=events');?>">
						<b><?php echo $totalEvents;?></b>
						<div><?php echo JText::_('COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_EVENTS');?></div>
					</a>
				</div>

				<div class="dash-stat-item">
					<b><?php echo $totalOnline;?></b>
					<div><?php echo JText::_('COM_EASYSOCIAL_ONLINE');?></div>
				</div>

				<div class="dash-stat-item">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=albums');?>">
						<b><?php echo $totalAlbums;?></b>
						<div><?php echo JText::_('COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_ALBUMS');?></div>
					</a>
				</div>

				<div class="dash-stat-item">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=audios');?>">
						<b><?php echo $totalAudios;?></b>
						<div><?php echo JText::_('COM_ES_WIDGETS_STATS_TOTAL_AUDIO');?></div>
					</a>
				</div>

				<div class="dash-stat-item">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=videos');?>">
						<b><?php echo $totalVideos;?></b>
						<div><?php echo JText::_('COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_VIDEOS');?></div>
					</a>
				</div>

				<div class="dash-stat-item">
					<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=reports');?>">
						<b><?php echo $totalReports;?></b>
						<div><?php echo JText::_('COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_REPORTS');?></div>
					</a>
				</div>
			</div>
		</section>

		<section class="dash-social">
			<strong>Stay Updated</strong>
			<div>
				<i class="fab fa-facebook"></i>
				<span>
					<a href="https://facebook.com/StackIdeas" target="_blank" class="text-inherit">Like us on Facebook</a>
				</span>
				</div>
			<div>
				<i class="fab fa-twitter"></i>
				<span>
					<a href="https://twitter.com/StackIdeas" target="_blank" class="text-inherit">Follow us on Twitter</a>
				</span>
			</div>
			<div>
				<i class="fa fa-book"></i>
				<span>
					<a href="https://stackideas.com/docs/easysocial/" target="_blank" class="text-inherit">View Product Documentation</a>
				</span>
		</div>
		</section>
	</div>
</div>
