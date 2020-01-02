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
<div>
	<div class="o-checkbox">
		<input type="checkbox" id="stream-permissions-admin" value="admin" name="stream_permissions[]" <?php if (!empty($value) && in_array('admin', $value)) { ?>checked="checked"<?php } ?> />
		<label for="stream-permissions-admin"><?php echo JText::_('COM_EASYSOCIAL_APP_GROUP_PERMISSIONS_ADMINS'); ?></label>
	</div>
	
	<div class="o-checkbox">
		<input type="checkbox" id="stream-permissions-everyone" value="member" name="stream_permissions[]" data-es-stream-permission-member <?php if (!empty($value) && in_array('member', $value)) { ?>checked="checked"<?php } ?> />
		<label for="stream-permissions-everyone"><?php echo JText::_('COM_EASYSOCIAL_APP_GROUP_PERMISSIONS_MEMBERS'); ?></label>
	</div>

	<div data-es-stream-permission-profile <?php echo !empty($value) && in_array('member', $value) ? '' : 'hidden'; ?>>
		<div class="o-form-group">
			<div class="o-select-group o-select-group--inline">
				<select name="permission_type[]" class="o-form-control" data-member-type>
					<option value="all" <?php echo !empty($type) && in_array('all', $type) ? 'selected="selected"' : ''; ?>>
						<?php echo JText::_('COM_EASYSOCIAL_APP_GROUP_PERMISSIONS_ALL_MEMBERS'); ?>
					</option>
					<option value="selected" <?php echo !empty($type) && in_array('selected', $type) ? 'selected="selected"' : ''; ?>>
						<?php echo JText::_('COM_EASYSOCIAL_APP_GROUP_PERMISSIONS_SELECTED_MEMBERS_BY_PROFILE_TYPES'); ?>
						</option>
					<option value="selectedUsers" <?php echo !empty($type) && in_array('selectedUsers', $type) ? 'selected="selected"' : ''; ?>>
						<?php echo JText::_('COM_ES_APP_GROUP_PERMISSIONS_SELECTED_MEMBERS_ONLY'); ?>
					</option>
				</select>
				<label for="" class="o-select-group__drop"></label>
			</div>
		</div>

		<div class="o-form-group <?php echo !empty($type) && in_array('selected', $type) ? '' : 't-hidden'; ?>" data-es-stream-profile-type>
			<select name="permission_profiles[]" multiple class="o-form-control">
				<?php foreach ($profiles as $profile) { ?>
				<option value="<?php echo $profile->id ?>" <?php echo !empty($profileType) && in_array($profile->id, $profileType) ? 'selected="selected"' : ''; ?>>		<?php echo $profile->title; ?>
				</option>
				<?php } ?>
			</select>
		</div>

		<div class="o-form-group <?php echo !empty($type) && in_array('selectedUsers', $type) ? '' : 't-hidden'; ?>" data-es-stream-selected_members>
			<div class="o-control-input">
				<div class="textboxlist controls disabled" data-friends-suggest>
 					<?php if ($members) { ?>
						<?php foreach ($members as $user) { ?>
						<div class="textboxlist-item" data-id="<?php echo $user->id;?>" data-title="<?php echo $this->html('string.escape', $user->getName() );?>" data-textboxlist-item>
							<span class="textboxlist-itemContent" data-textboxlist-itemContent>
								<img src="<?php echo $user->getAvatar(); ?>" width="16" height="16" data-suggest-avatar="">
								<?php echo $this->html( 'string.escape' , $user->getName() );?>
								<input type="hidden" value="<?php echo $user->getName(); ?>" data-suggest-title="">
								<input type="hidden" name="permission_users[]" value="<?php echo $user->id;?>" data-suggest-id />
							</span>
							<div class="textboxlist-itemRemoveButton" data-textboxlist-itemremovebutton=""><i class="fa fa-times"></i></div>
						</div>
						<?php } ?>
					<?php } ?>
					<input type="text" autocomplete="off" disabled class="participants textboxlist-textField" data-textboxlist-textField placeholder="<?php echo !$members ? JText::_('COM_EASYSOCIAL_FRIENDS_LIST_FORM_USERS_PLACEHOLDER') : ""; ?>" />
				</div>

				<div class="help-block">
					<?php echo JText::_('COM_ES_GROUP_MEMBERS_LIST_INFO');?>
				</div>
			</div>
		</div>
	</div>
</div>