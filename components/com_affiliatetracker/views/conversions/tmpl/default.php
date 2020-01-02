<?php // no direct access

/*------------------------------------------------------------------------
# com_invoices - Invoices for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2012 JoomlaFinances.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaFinances.com
# Technical Support:	Forum - http://www.JoomlaFinances.com/forum
-------------------------------------------------------------------------*/

//no direct access
defined('_JEXEC') or die('Restricted access.');
$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/contribution.css");

$itemid = $this->params->get('itemid');
if($itemid != "") $itemid = "&Itemid=" . $itemid;

?>

<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="invitex_title">
		<p>佣金的赚取记录</p>
	</div>

    <form action="<?php echo JRoute::_("index.php?option=com_affiliatetracker&view=conversions".$itemid); ?>" method="get" name="adminForm" id="adminForm" class="navbar-form pull-left form-horizontal">
        <div style='margin-top:26px' class='search-container'>
            <div>
    <!--			<input type="text" class="search-input" placeholder="筛选时间（起始日期）">-->
    <!--			<input type="text" class="search-input" placeholder="筛选时间（选择终始日期）" style='margin-left:10px'>-->

                <?php echo JHTML::calendar($this->lists['date_in'], "date_in", "date_in", "%Y-%m-%d", array("class" => "search-input", "placeholder" => '筛选时间（起始日期）')); ?>

                <?php echo JHTML::calendar($this->lists['date_out'], "date_out", "date_out", "%Y-%m-%d", array("class" => "search-input", "placeholder" => '筛选时间（选择终始日期）')); ?>


            <span>
                <button type="submit" style="border: none;width: 40px;height: 36px" data-original-title="<?php echo JText::_('SEARCH'); ?>"> <img src="components/com_jblance/images/search/search.png" alt="" class='search-img'></button>
            </span>
            </div>
            <div>
                <select name="account_id" id="account_id" class="chzn-select ">
                    <option value=""><?php echo JText::_( 'ALL_ACCOUNTS' ); ?></option>
                    <?php
                    for ($i=0, $n=count( $this->accounts );$i < $n; $i++)	{
                        $row =$this->accounts[$i];
                        $selected = "";
                        if($row->id == $this->lists['account_id']) $selected = "selected";?>
                        <option <?php echo $selected;?> value="<?php echo $row->id;?>"><?php echo $row->account_name;?></option>
                    <?php } ?>
                </select>
                <select name="type_id" id="type_id" class="chzn-select ">
                    <option value=""><?php echo JText::_( 'ALL_CONVERSIONS' ); ?></option>
                    <?php
                    for ($i=0, $n=count( $this->types );$i < $n; $i++)	{
                        $row =$this->types[$i];
                        $selected = "";
                        if($row->id == $this->lists['type_id']) $selected = "selected";?>
                        <option <?php echo $selected;?> value="<?php echo $row->id;?>"><?php echo $row->name;?></option>
                    <?php } ?>
                </select>
    <!--			<select placeholder='所有联盟会员' class='search-select'>-->
    <!--				<option value=""></option>-->
    <!--				<option value="">是</option>-->
    <!--				<option value="">否</option>-->
    <!--			</select>-->
    <!--			<select placeholder='成员转介绍' class='search-select'>-->
    <!--				<option value=""></option>-->
    <!--				<option value="">名称</option>-->
    <!--				<option value="">邮寄</option>-->
    <!--			</select>-->
            </div>
        </div>
        <div style='margin-top:20px'>
            <table class='invite-table' border="1px solid #ccc">
                <thead>
                    <tr class='invite-table-tr'>
                        <th>序号</th>
                        <th>账户</th>
                        <th>创建日期</th>
                        <th>会员贡献</th>
                        <th>价值</th>
                        <th>佣金</th>
                    </tr>
                </thead>
                <tbody>

                  <?php
                  $k = 0;
                  $total_comission = 0 ;
                  $total = 0 ;
                  for ($i=0, $n=count( $this->items ); $i < $n; $i++)	{
                  $row =$this->items[$i];
                  $checked 	= JHTML::_('grid.id',   $i, $row->id );
                  $link_account 		= JRoute::_( 'index.php?option=com_affiliatetracker&view=conversions&account_id='. $row->atid .$itemid);
                  $link_type 		= JRoute::_( 'index.php?option=com_affiliatetracker&view=conversions&type_id='. $row->component.','.$row->type .$itemid);

                  $total_comission += $row->comission ;
                  $total += $row->value ;

                  ?>

                    <tr class='invite-tbody-tr'>
                        <td>1</td>
                        <td>“话事”1000卖家与服务商免费入驻隆重招商活动</td>
                        <td>1308019344@qq.com</td>
                        <td>是</td>
                        <td>2020-01-01</td>
                        <td>12</td>
                    </tr>
                 <?php
                      $k = 1 - $k;
                  }?>

                </tbody>
                <tbody>
                    <tr class='invite-tbody-tr'>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo AffiliateHelper::format($total); ?></td>
                        <td><?php echo AffiliateHelper::format($total_comission); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
        $modules = JModuleHelper::getModules("at_bottom");
        $document	=JFactory::getDocument();
        $renderer	= $document->loadRenderer('module');
        $attribs 	= array();
        $attribs['style'] = 'xhtml';
        foreach ( @$modules as $mod )
        {
            echo $renderer->render($mod, $attribs);
        }
        ?>
        <input type="hidden" name="option" value="com_affiliatetracker" />
        <input type="hidden" name="view" value="conversions" />
        <input type="hidden" name="Itemid" value="<?php echo $this->params->get('itemid'); ?>" />

        <input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

        <div class="pagination" align="center"> <?php echo $this->pagination->getListFooter(); ?> </div>
    </form>
</div>


<!--    以上新构建的样式和页面 end   -->

<?php echo AffiliateHelper::nav_tabs(); ?>

<?php
      $intro = new stdClass();

      $intro->text = $this->params->get('textconversions');

      $dispatcher = JDispatcher::getInstance();
      $plug_params = new JRegistry('');

      JPluginHelper::importPlugin('content');
      $results = $dispatcher->trigger('onContentPrepare', array ('com_affiliatetracker.accounts', &$intro, &$plug_params, 0));

      echo $intro->text;
      ?>
<div class="navbar">
<div class="navbar-inner">
<form action="<?php echo JRoute::_("index.php?option=com_affiliatetracker&view=conversions".$itemid); ?>" method="get" name="adminForm" id="adminForm" class="navbar-form pull-left form-horizontal">

  <?php echo JHTML::calendar($this->lists['date_in'], "date_in", "date_in", "%Y-%m-%d", array("class" => "input-small", "placeholder" => JText::_( 'FROM' ))); ?>

  <?php echo JHTML::calendar($this->lists['date_out'], "date_out", "date_out", "%Y-%m-%d", array("class" => "input-small", "placeholder" => JText::_( 'TO' ))); ?>


    <select name="account_id" id="account_id" class="chzn-select ">
      <option value=""><?php echo JText::_( 'ALL_ACCOUNTS' ); ?></option>
      <?php
			for ($i=0, $n=count( $this->accounts );$i < $n; $i++)	{
			$row =$this->accounts[$i];
			$selected = "";
			if($row->id == $this->lists['account_id']) $selected = "selected";?>
      <option <?php echo $selected;?> value="<?php echo $row->id;?>"><?php echo $row->account_name;?></option>
      <?php } ?>
    </select>
    <select name="type_id" id="type_id" class="chzn-select ">
      <option value=""><?php echo JText::_( 'ALL_CONVERSIONS' ); ?></option>
      <?php
			for ($i=0, $n=count( $this->types );$i < $n; $i++)	{
			$row =$this->types[$i];
			$selected = "";
			if($row->id == $this->lists['type_id']) $selected = "selected";?>
      <option <?php echo $selected;?> value="<?php echo $row->id;?>"><?php echo $row->name;?></option>
      <?php } ?>
    </select>
    <button type="submit" class="btn btn-inverse" data-original-title="<?php echo JText::_('SEARCH'); ?>"><?php echo JText::_('FILTER_RESULTS'); ?></button>

</div></div>

  <div class="row-fluid at_module_wrapper" >
    <?php
$modules = JModuleHelper::getModules("at_conversions");
$document	=JFactory::getDocument();
$renderer	= $document->loadRenderer('module');
$attribs 	= array();
$attribs['style'] = 'xhtml';
foreach ( @$modules as $mod )
{
	echo $renderer->render($mod, $attribs);
}
?>
  </div>

  <?php echo AffiliateHelper::time_options(); ?>
  <div class="at_totals">

  <div class="row-fluid">
    <div class="span6 text-center">
      <div class="big_number"><?php echo $this->conversions; ?></div>
      <span class="label label-info"><?php echo JText::_('CONVERSIONS'); ?></span><br />
      <span class="muted"><?php echo JText::_('LAST_'.$this->timespan.'_DAYS'); ?></span> </div>
    <div class="span6 text-center">
      <div class="big_number"><?php echo AffiliateHelper::format($this->comission_value); ?></div>
      <span class="label label-success"><?php echo JText::_('COMISSION'); ?></span><br />
      <span class="muted"><?php echo JText::_('LAST_'.$this->timespan.'_DAYS'); ?></span> </div>
  </div>
  </div>
  <br  />
  <table class="table table-striped">
    <thead>
      <tr>
        <th colspan="2"> <?php echo JHTML::_( 'grid.sort', 'ACCOUNT', 'at.atid', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
        <th class="hidden-phone"> <?php echo JHTML::_( 'grid.sort', 'DATE_CREATED', 'at.date_created', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
        <th colspan="2"> <?php echo JHTML::_( 'grid.sort', 'CONVERSION', 'at.name', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
        <th class="hidden-phone hidden"> <?php echo JHTML::_( 'grid.sort', 'ITEM', 'at.extended_name', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
        <th class=" hidden-phone text-right"> <?php echo JHTML::_( 'grid.sort', 'VALUE', 'at.value', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
        <th class=" text-right"> <?php echo JHTML::_( 'grid.sort', 'COMISSION', 'at.comission', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
        <!--th class="hidden-phone width55"> <?php echo JHTML::_( 'grid.sort', 'USER', 'at.user_id', $this->lists['order_Dir'], $this->lists['order']); ?> </th-->
      </tr>
    </thead>
    <?php
	$k = 0;
	$total_comission = 0 ;
	$total = 0 ;

	for ($i=0, $n=count( $this->items ); $i < $n; $i++)	{
		$row =$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link_account 		= JRoute::_( 'index.php?option=com_affiliatetracker&view=conversions&account_id='. $row->atid .$itemid);
		$link_type 		= JRoute::_( 'index.php?option=com_affiliatetracker&view=conversions&type_id='. $row->component.','.$row->type .$itemid);

		$total_comission += $row->comission ;
		$total += $row->value ;

		?>
    <tr class="<?php echo "row$k"; ?>">
      <td><?php echo $row->account_name; ?></td>
      <td width="24"><a href="<?php echo $link_account; ?>" data-original-title="<?php echo JText::_('CLICK_FILTER_ACCOUNT'); ?>" rel="tooltip"><i class="icon-share-alt hide-icon"></i></a></td>
      <td class="hidden-phone"><?php echo JHTML::_('date', $row->date_created, JText::_('DATE_FORMAT_LC3')); ?></td>
      <td class=""><?php echo $row->name; ?></td>
      <td width="24"><a href="<?php echo $link_type; ?>" data-original-title="<?php echo JText::_('CLICK_FILTER_TYPE'); ?>" rel="tooltip"><i class="icon-share-alt hide-icon"></i></a></td>
      <td class="hidden-phone hidden"><?php echo $row->extended_name; ?></td>
      <td align="right" class="text-right hidden-phone"><?php echo AffiliateHelper::format($row->value); ?></td>
      <td align="right" class="text-right "><?php echo AffiliateHelper::format($row->comission); ?></td>
      <!--td class="hidden-phone"><?php echo $row->username . " [".$row->user_id."]"; ?></td-->
    </tr>
    <?php
		$k = 1 - $k;
	}
	?>
    <tfoot>
      <tr class="totals">
        <td class=""></td>
        <td class=""></td>
        <td class="hidden-phone"></td>
        <td class=""></td>
        <td class=""></td>
        <td class="hidden-phone hidden"></td>
        <td align="right " class="hidden-phone text-right"><?php echo AffiliateHelper::format($total); ?></td>
        <td align="right" class="text-right"><?php echo AffiliateHelper::format($total_comission); ?></td>
        <!--td colspan="1" class="hidden-phone"></td-->
      </tr>
    </tfoot>
  </table>
  <br />
  <?php
$modules = JModuleHelper::getModules("at_bottom");
$document	=JFactory::getDocument();
$renderer	= $document->loadRenderer('module');
$attribs 	= array();
$attribs['style'] = 'xhtml';
foreach ( @$modules as $mod )
{
	echo $renderer->render($mod, $attribs);
}
?>
  <input type="hidden" name="option" value="com_affiliatetracker" />
  <input type="hidden" name="view" value="conversions" />
  <input type="hidden" name="Itemid" value="<?php echo $this->params->get('itemid'); ?>" />

  <input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists['order']; ?>" />
  <input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

  <div class="pagination" align="center"> <?php echo $this->pagination->getListFooter(); ?> </div>
</form>
<div align="center"><?php echo AffiliateHelper::showATFooter(); ?></div>
