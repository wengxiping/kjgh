<?php
/**
* @version    SVN: <svn_id>
* @package    InviteX
* @author     Techjoomla <extensions@techjoomla.com>
* @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
* @license    GNU General Public License version 2 or later.
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/invite.css");

jimport('joomla.filesystem.folder');
$session = JFactory::getSession();
$document=JFactory::getDocument();
$itemid = $this->itemid;
$onload_redirect=JRoute::_('index.php?option=com_invitex&view=invites&Itemid='.$itemid,false);

JHtml::_('behavior.tooltip');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$input = JFactory::getApplication()->input;
$cid   = $input->get( 'cid', '', 'ARRAY');
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	techjoomla.jQuery(document).ready(function() {
		techjoomla.jQuery("#limit").removeClass('inputbox');
		techjoomla.jQuery("#limit").removeClass('input-mini');
		techjoomla.jQuery("#limit").removeAttr('size');
		techjoomla.jQuery("#filter_invite_status").removeAttr('size');
	});
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;

		if (order !== '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="invitex_title">
		<p>您邀请的用户统计</p>
	</div>
	<div style='margin-top:26px;display: none;' class='search-container'>
		<div>
			<input type="text" class="search-input" placeholder="查找通过被邀请人姓名或邮箱">
				<img src="components/com_jblance/images/search/search.png" alt="" class='search-img'>
		</div>
		<div>
			<select placeholder='是否注册' class='search-select'>
				<option value=""></option>
				<option value="">是</option>
				<option value="">否</option>
			</select>
			<select placeholder='名称/邮寄' class='search-select'>
				<option value=""></option>
				<option value="">名称</option>
				<option value="">邮寄</option>
			</select>
		</div>
	</div>
	<div style='margin-top:20px;display: none'>
		<table class='invite-table' border="1px solid #ccc">
			<thead>
				<tr class='invite-table-tr'>
					<th>序号</th>
					<th>名称</th>
					<th>电子邮件/联系电话</th>
					<th>是否注册</th>
					<th>有效期</th>
					<th>点击</th>
				</tr>
			</thead>
			<tbody>
				<tr class='invite-tbody-tr'>
					<td>1</td>
					<td>“话事”1000卖家与服务商免费入驻隆重招商活动</td>
					<td>1308019344@qq.com</td>
					<td>是</td>
					<td>2020-01-01</td>
					<td>12</td>
				</tr>
			</tbody>
			<tbody>
				<tr class='invite-tbody-tr'>
					<td>1</td>
					<td>“话事”1000卖家与服务商免费入驻隆重招商活动</td>
					<td>1308019344@qq.com</td>
					<td>是</td>
					<td>2020-01-01</td>
					<td>12</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!-- end of invitex_title div -->
	<!-- 设计稿没有，暂时隐藏 -->
	<div style='display:none'>
		<div class="text-right <?php echo (JVERSION < '3.0') ? 'invitex_skip':'';?>">
			<button class="btn btn-default" onclick='window.location="<?php echo $onload_redirect?>"'><?php echo JText::_('BACK_TO_INVITEX');?></button>
			<br><br>
		</div>
	</div>
	<!-- 设计稿没有，暂时隐藏 -->
	<form style='' action='' method=post name="adminForm" id="adminForm">
		<div class="clearfix">&nbsp;</div>
		<div id="filter-bar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_STATS'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip form-control"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_STATS'); ?>" />
			</div>
			<div class="btn-group pull-left clearfix">
				<button type="submit" class="btn btn-default hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
				<i class="glyphicon glyphicon-search"></i>
				</button>
				<button type="button" class="btn btn-default hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
					onclick="document.id('filter_search').value='';this.form.submit();">
				<i class="glyphicon glyphicon-remove"></i>
				</button>
			</div>
			<?php if (JVERSION >= '3.0') : ?>
			<div class="btn-group select pull-right hidden-xs invitex-margin-left-5px">
				<label for="limit" class="element-invisible">
				<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-xs hidden-sm invitex-margin-left-5px">
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
			<div class="btn-group pull-right hidden-xs hidden-sm invitex-margin-left-5px">
				<label for="sortTable" class="element-invisible">
				<?php echo JText::_('JGLOBAL_SORT_BY'); ?>
				</label>
				<select name="sortTable" id="sortTable" class="input-medium"
					onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class=" btn-group pull-right hidden-xs hidden-sm invitex-margin-left-5px">
			<?php
				echo $this->invite_status;
				?>
		</div>
		<?php endif; ?>
		<div class="clearfix">&nbsp;</div>
		<div class="clearfix">&nbsp;</div>
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php
			else : ?>
		<div class="clearfix">&nbsp;</div>
		<table class="table-condensed table-bordered table-responsive table-striped" width="100%">
			<tr>
				<th class="text-left wordsbreak hidden-xs hidden-sm"><?php echo JHTML::_( 'grid.sort', 'NAMES' , 'iie.invitee_name', $listDirn, $listOrder); ?></th>
				<th class="text-left wordsbreak"><?php echo JHTML::_( 'grid.sort',  'COM_INVITEX_EMAILS_CONTACT_NUMBER', 'iie.invitee_email', $listDirn, $listOrder); ?></th>
				<th class="text-left wordsbreak"><?php echo JText::_('REG') ?></th>
				<th class="text-left wordsbreak hidden-xs hidden-sm"><?php echo JText::_('EXP_DATE') ?></th>
				<th class="wordsbreak text-center"><?php echo JText::_('CLICKED') ?></th>
			</tr>
			<?php
				if($this->items)
				{
						foreach ( $this->items as $row )
						{
							$expiry	=   "";
							if($row->expires)
							{
								 if(JVERSION >= '1.6.0')
									$expiry=    JHTML::Date($row->expires, "F - j - Y");
								else
									$expiry=    JHTML::Date($row->expires, "%B - %d - %Y");
							}
							?>
			<tr>
				<td class="wordsbreak hidden-xs hidden-sm"><?php echo $row->invitee_name;?></td>
				<td class="wordsbreak"><?php echo $row->invitee_email;?></td>
				<td class="wordsbreak"><?php echo $row->accepted; ?></td>
				<td class="wordsbreak hidden-xs hidden-sm"><?php echo $expiry; ?></td>
				<td class="wordsbreak text-center"><?php echo $row->click_count; ?></td>
			</tr>
			<?php
				}
				}
				?>
		</table>
		<?php if (JVERSION >= '3.0'): ?>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php else: ?>
		<div class="pager">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>
		<?php endif; ?>
		<?php endif; ?>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="option"  value="com_invitex" />
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="view" id="view" value="stats" />
		<input type="hidden" id="filter_order" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" id="filter_order_Dir" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="boxchecked" value="" />
	</form>
</div>
<?php
	$path=$this->invhelperObj->getViewpath('invites','default_footer');
	include $path;
	?>
