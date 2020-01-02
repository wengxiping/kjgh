<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

JHtml::_('formbehavior.chosen', 'select');

$listOrder	= $this->state->filter_order;
$listDirn	= $this->state->filter_order_Dir;

$customOptions = array(
	'filtersHidden'       => true,
	'defaultLimit'        => JFactory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#filter_full_ordering'
);
JHtml::_('searchtools.form', '#adminForm', $customOptions);
?>
<form action="index.php?option=com_eventbooking&view=themes" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('EB_FILTER_SEARCH_THEMES_DESC');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('EB_SEARCH_THEMES_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<?php
                    echo $this->lists['filter_state'];
                    echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped" id="pluginList">
			<thead>
			<tr>
				<th width="2%" class="center">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('searchtools.sort',  JText::_('EB_NAME'), 'tbl.name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th class="title" width="20%">
					<?php echo JHtml::_('searchtools.sort', JText::_('EB_TITLE'), 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('searchtools.sort', JText::_('EB_AUTHOR') , 'tbl.author', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title center">
					<?php echo JHtml::_('searchtools.sort', JText::_('EB_AUTHOR_EMAIL'), 'tbl.email', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title center">
					<?php echo JHtml::_('searchtools.sort', JText::_('EB_DEFAULT') , 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title center">
					<?php echo JHtml::_('searchtools.sort', JText::_('EB_ID') , 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$bootstrapHelper = EventbookingHelperHtml::getAdminBootstrapHelper();
			$iconPublish = $bootstrapHelper->getClassMapping('icon-publish');
			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = JRoute::_('index.php?option=com_eventbooking&view=theme&id=' . $row->id);
				$checked   = JHtml::_('grid.id', $i, $row->id);
				$published = JHtml::_('jgrid.published', $row->published, $i);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?>
						</a>
					</td>
					<td>
						<?php echo $row->title; ?>
					</td>
					<td>
						<?php echo $row->author; ?>
					</td>
					<td class="center">
						<?php echo $row->author_email;?>
					</td>
					<td class="center">
                        <?php
                            if ($row->published)
                            {
                            ?>
                                <a class="tbody-icon"><span class="<?php echo $iconPublish; ?>"></span></a>
                            <?php
                            }
                            else
                            {
                                echo $published;
                            }
                        ?>
					</td>
					<td class="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
		<table class="adminform" style="margin-top: 50px;">
			<tr>
				<td>
					<fieldset class="adminform">
						<legend><?php echo JText::_('EB_INSTALL_THEME'); ?></legend>
						<table>
							<tr>
								<td>
									<input type="file" name="theme_package" id="theme_package" size="50" class="inputbox" /> <input type="button" class="button" value="<?php echo JText::_('EB_INSTALL'); ?>" onclick="installPlugin();" />
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" id="filter_full_ordering" name="filter_full_ordering" value="" />

	<script type="text/javascript">
		function installPlugin()
		{
			var form = document.adminForm ;
			if (form.theme_package.value =="")
			{
				alert("<?php echo JText::_('EB_CHOOSE_THEME'); ?>");
				return ;	
			}
			form.task.value = 'install';
			form.submit();
		}
	</script>
	<?php echo JHtml::_( 'form.token' ); ?>
</form>