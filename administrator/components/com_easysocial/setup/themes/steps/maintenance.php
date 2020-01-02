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
<script type="text/javascript">
$(document).ready(function(){
	es.maintenance.init();
});
</script>
<form name="installation" data-installation-form>

	<p>We will need to update existing site Users and ensure that the previous Users are synchronized properly with EasySocial.</p>

	<div class="" style="margin-top: 30px;" data-users-progress>
		<div class="install-progress" style="border-bottom: 1px #ddd;">
			<div class="row-table">
				<div class="col-cell">
					<div class="hide" data-progress-complete-message>Users synchronization completed.</div>
					<div data-progress-active-message="">Synchronizing users...</div>
				</div>
				<div class="col-cell cell-result text-right">
					<div class="progress-result" data-progress-bar-result="">0%</div>
				</div>
			</div>

			<div class="progress">
				<div class="progress-bar progress-bar-info progress-bar-striped" data-progress-bar="" style="width: 0%;"></div>
			</div>
		</div>
	</div>


	<div class="" style="margin-top: 30px;" data-profiles-progress>
		<div class="install-progress" style="border-bottom: 1px #ddd;">
			<div class="row-table">
				<div class="col-cell">
					<div class="hide" data-progress-complete-message>Users synchronization completed</div>
					<div data-progress-active-message="">Synchronizing users that don't have a profile.</div>
				</div>
				<div class="col-cell cell-result text-right">
					<div class="progress-result" data-progress-bar-result="">0%</div>
				</div>
			</div>

			<div class="progress">
				<div class="progress-bar progress-bar-info progress-bar-striped" data-progress-bar="" style="width: 0%;"></div>
			</div>
		</div>
	</div>


	<div class="" style="margin-top: 30px;" data-sync-progress>

		<div class="install-progress" style="border-bottom: 1px #ddd;">

			<div class="row-table">
				<div class="col-cell">
					<div class="hide" data-sync-progress-complete-message><?php echo JText::_('Execution of maintenance scripts completed.');?></div>
					<div data-sync-progress-active-message=""><?php echo JText::_('Running Maintenance Scripts');?></div>
				</div>
				<div class="col-cell cell-result text-right">
					<div class="progress-result" data-progress-bar-result="">0%</div>
				</div>
			</div>

			<div class="progress">
				<div class="progress-bar progress-bar-info progress-bar-striped" data-progress-bar="" style="width: 0%;"></div>
			</div>

			<ol class="install-logs list-reset" data-progress-logs="">
				<li class="pending" data-progress-execscript>
					<div class="notes">
						<ul style="list-unstyled" data-progress-execscript-items>
						</ul>
					</div>
				</li>
			</ol>
		</div>
	</div>


	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="active" value="complete" />
</form>
