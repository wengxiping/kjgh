<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.filesystem.file');

JHtml::_('behavior.tooltip');

$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
?>
<form action="<?php echo 'index.php?option=com_invitex&view=urlinvites'; ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> invites">
			<?php
			// JHtmlsidebar for menu.
			if (!empty( $this->sidebar))
			{
			?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar;?>
				</div>
				<div id="j-main-container" class="span10">
					<?php
					// Search tools bar
					echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			}
			else
			{
			?>
				<div id="j-main-container">
					<?php
					// Search tools bar
					echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
					?>
			<?php
			}

			if (empty($this->items))
			{
			?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			}
			else
			{
			?>
				<table class="table table-striped" width="100%">
					<thead>
						<tr>
							<th width="33%">
								<?php echo JHtml::_('grid.sort', 'COM_INVITEX_URL_INVITEE_INVITER_NAME', 'inviter_id', $listDirn, $listOrder);?>
							</th>
							<th width="33%" >
								<?php echo JHtml::_('grid.sort', 'COM_INVITEX_URL_INVITEE_INVITEE_NAME', 'name', $listDirn, $listOrder);?>
							</th>
							<th width="33%">
								<?php echo JHtml::_('grid.sort', 'COM_INVITEX_INVITEE_EMAIL', 'email', $listDirn, $listOrder);?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							if (!empty($this->items))
							{
								foreach ($this->items as $item)
								{
									?>
									<tr>
										<td>
											<?php
											$inviter = JFactory::getUser($item->inviter_id)->name;
											echo $this->escape($inviter);
											?>
										</td>
										<td><?php echo $item->name;?></td>
										<td><?php echo $item->email;?></td>
									</tr>
								<?php
								}
							}
							?>
					</tbody>
				</table>
				<div class="pagination">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			<?php
			}
			?>
	<input type="hidden" name="option" value="com_invitex" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
