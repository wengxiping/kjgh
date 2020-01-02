<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.filesystem.file');

JHtml::_('behavior.tooltip');

$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$input = JFactory::getApplication()->input;
$cid   = $input->get( 'cid', '', 'ARRAY');
$sortFields = $this->getSortFields();
?>
<form		action="<?php echo JRoute::_('index.php?option=com_invitex&view=invites'); ?>"
 method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> invites">
		<?php
	   // JHtmlsidebar for menu.
	   if(JVERSION >= '3.0'):
			   if (!empty( $this->sidebar)) : ?>
					   <div id="j-sidebar-container" class="span2">
							   <?php echo $this->sidebar; ?>
					   </div>
					   <div id="j-main-container" class="span10">
							   <?php
									 // Search tools bar
									 echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
							   ?>
			   <?php else : ?>
					   <div id="j-main-container">
							   <?php
									   // Search tools bar
									   echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
							   ?>
			   <?php endif;
	   endif;
       ?>


		<?php if (JVERSION < '3.0') : ?>

			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_INVITES'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_INVITES'); ?>" />
				</div>

				<div class="btn-group pull-left">
					<button type="submit" class="btn hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
						<i class="icon-search"></i>
					</button>
					<button type="button" class="btn hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
					onclick="document.id('filter_search').value='';this.form.submit();">
						<i class="icon-remove"></i>
					</button>
				</div>

<!--
					<div class="btn-group pull-right hidden-phone hidden-tablet">
						<label for="directionTable" class="element-invisible">
							<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
						</label>
						<select name="directionTable" id="directionTable"
							class="input-medium" onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
							<option value="asc"
								<?php
									if ($listDirn == 'asc')
									{
										echo 'selected="selected"';
									}
								?>>
									<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
							</option>
							<option value="desc"
								<?php
								if ($listDirn == 'desc')
								{
									echo 'selected="selected"';
								}
								?>>
									<?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
							</option>
						</select>
					</div>

					<div class="btn-group pull-right hidden-phone hidden-tablet">
						<label for="sortTable" class="element-invisible">
							<?php echo JText::_('JGLOBAL_SORT_BY'); ?>
						</label>
						<select name="sortTable" id="sortTable" class="input-medium"
							onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
							<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
						</select>
					</div>
-->

				<div class="btn-group pull-right hidden-phone hidden-tablet">
					<?php

					if (!empty($this->inviters))
					{
						$options[] = JHtml::_('select.option', 0, JText::_( 'SELECT_USER' ));

						if (count($this->inviters)>=1)
						{
							$filter_inviter = $this->state->get('filter.inviter');
							$default = !empty($filter_inviter) ? $filter_inviter : 0;
							foreach($this->inviters as $key=>$value)
							{

								$options[] = JHtml::_('select.option', $key,$value['name']);
							}

							echo $this->dropdown = JHtml::_('select.genericlist', $options, 'filter_inviter', 'class="input-medium" size="1" onchange="document.adminForm.submit();" ', 'value', 'text', $default);
						}
					}

					?>
				</div>

				<div class="btn-group pull-right hidden-phone hidden-tablet">
					<?php

					if (!empty($this->providers))
					{
						$options=array();
						$options[] = JHtml::_('select.option', 0, JText::_( 'SELECT_PROVIDER' ));

						if (count($this->providers)>=1)
						{
							$provider_email = $this->state->get('filter.provider_email');
							$default = !empty($provider_email) ? $provider_email : 0;

							foreach($this->providers as $key=>$value)
							{
								$provider_email = "";
								$provider_email = strtolower($value['provider_email']);
								$provider_email=str_replace("plug_techjoomlaapi_","",$provider_email);
								$provider_email=str_replace("send_","",$provider_email);
								$provider_email=ucwords($provider_email);

								if($provider_email=="Js_messaging")
								{
									$provider_email=JText::_( 'Messaging' );

								}

								$options[] = JHtml::_('select.option', $key,$provider_email);
							}

							echo $this->dropdown = JHtml::_('select.genericlist', $options, 'filter_provider_email', 'class="input-medium" size="1" onchange="document.adminForm.submit();" ', 'value', 'text', $default);
						}
					}


					?>
				</div>
				</div>


				<div id="filter-bar" class="btn-toolbar">
					<div class="btn-group pull-right hidden-phone hidden-tablet">
					<?php
					$accepted_status = $this->state->get('filter.accepted_status');
					echo JHtml::_('select.genericlist', $this->sstatus, "filter_accepted_status", 'class="input-medium" size="1" onchange="document.adminForm.submit();" name="accepted_status"', "value", "text", $this->state->get('filter.accepted_status'));
					?>
				</div>

				<div id="filter-bar" class="btn-toolbar">
					<div class="btn-group pull-right hidden-phone hidden-tablet">
					<?php
					$sent_status = $this->state->get('filter.sent_status');
					echo JHtml::_('select.genericlist', $this->sent_status, "filter_sent_status", 'class="input-medium" size="1" onchange="document.adminForm.submit();" name="sent_status"', "value", "text", $this->state->get('filter.sent_status'));
					?>
				</div>
				</div>
			</div>

			<div class="clearfix"> </div>
				<?php endif; ?>

			<?php if (empty($this->items)) : ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			else : ?>
		<table class="table table-striped" width="100%">
			<thead>
				<tr>
					<th width="1%" class="nowrap  hidden-phone">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th width="15%">
						<?php echo JHtml::_('grid.sort', 'COM_INVITEX_INVITER', 'inviter_name', $listDirn, $listOrder);?>
					</th>
					<th  width="15%" ><?php
					echo JHtml::_('grid.sort', 'COM_INVITEX_INVITEE_NAME', 'invitee_name', $listDirn, $listOrder);
					?></th>
					<th ><?php	echo JHtml::_('grid.sort', 'COM_INVITEX_INVITEE_EMAIL', 'invitee_email', $listDirn, $listOrder);	?></td>

					<th width="10%" ><?php echo JHTML::tooltip(JText::_('COM_INVITEX_PROVIDER'), JText::_('COM_INVITEX_PROVIDER'), '', JText::_('COM_INVITEX_PROVIDER'));?></td>
					<th width="15%" class="nowrap  hidden-phone hidden-tablet">
						<?php echo JHtml::_('grid.sort', 'COM_INVITEX_EXPIRES', 'expires', $listDirn, $listOrder); ?>
					</th>
					<th   width="3%" class="  hidden-phone"><?php echo JText::_('COM_INVITEX_SENT'); ?></td>
					<th  width="3%"  class="  hidden-phone"><?php echo JText::_('CLICKED'); ?></td>
					<th   width="3%" class="  hidden-phone"><?php echo JText::_('ACCEPTED'); ?></td>
					<th width="1%">
						<?php echo JHtml::_('grid.sort', 'COM_INVITEX_ID', 'imp_id', $listDirn, $listOrder);?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php

				$k = 0;

				if (!empty($this->items))
				{
					$n = count($this->items);

					for ($i=0; $i < $n; $i++)
					{
						$zone_type = '';
						$item = $this->items[$i];
				?>


				<tr class="<?php echo 'row'.$k; ?>">
					<td class="nowrap  hidden-phone" width="1%">
					<?php echo JHtml::_('grid.id', $i, $item->imp_id); ?>
					</td>

					<td   ><?php if($item->inviter_name) echo $item->inviter_name;else echo $item->guest;?></td>
					<td  ><?php echo $item->invitee_name;?></td>
					<td   ><?php echo $item->invitee_email;?></td>
					<!--
					<td class="nowrap  hidden-phone"  align=""><?php echo $item->message;?></td>
					-->
					<td  align="">
					<?php
					$item->provider_email=strtolower($item->provider_email);
					$item->provider_email=str_replace("plug_techjoomlaapi_","",$item->provider_email);
					$item->provider_email=str_replace("send_","",$item->provider_email);
					$item->provider_email=$item->provider_email;

					if($item->provider_email=='js_messaging')
					{
						$item->provider_email = JText::_('COM_INVITEX_MESSAGING');

					}

					echo $item->provider_email;
					?>
					</td>
					<td   ><?php

					echo JHTML::Date($this->escape($item->expires), JText::_( 'COM_INVITEX_DATE_FORMAT_TO_SHOW' ))
					?></td>
					<td   class="center" ><?php if($item->sent) echo "Yes"; else echo "No";?></td>
					<td class="center">
						<?php echo $this->escape($item->click_count); ?>
					</td>
					<td class="center">
					<?php
						$accepted=0;
						if($item->invitee_id && $item->friend_count!=0)
							$accepted=1;
						if($accepted==1) echo "Yes"; else echo "No";

					?>
					</td>
					<td class="center"><?php echo $item->imp_id;?></td>
					<input type="hidden" name="imports[<?php echo $item->imp_id;?>]" value="<?php echo $item->import_id; ?>">
				</tr>
					<?php
						if ($k%2!=1)
						{
							$k++;
						}
						else
						{
							$k = 0;
						}
					}
				} // End if products.
				?>
			</tbody>




		</table>


	<div class="pagination">
		<?php echo $this->pagination->getListFooter(); ?>
	</div>

	<?php endif; ?>

			<input type="hidden" name="option" value="com_invitex" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

			<?php echo JHtml::_( 'form.token' ); ?>
	</div>
</form>
