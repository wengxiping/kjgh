<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

$doc    = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/events_table.css");

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$clearfixClass   = $bootstrapHelper->getClassMapping('clearfix');

?>

<div class='main-container'>
	<div>
		<p>行业活动</p>
	</div>
	<div class='phone-container'>
        <div class='phone-container-one-ul'>
        <?php foreach ($categories as $key=>$categoryA){
        if (!$config->show_empty_cat && !count($categoryA->events))
        {
            continue ;
        }
        ?>
		<button data-id="<?php echo $key?>" class="<?php echo ($key==0)?'selected-button':''?>"><?php echo $categoryA->name;?></button>
        <?php }?>
        </div>
	</div>
	<div class='nav-container'>
		<ul class='nav-ul'>
            <?php foreach ($categories as $keyB=>$categoryB){
            if (!$config->show_empty_cat && !count($categoryB->events))
            {
                continue ;
            }
            ?>
			<li data-id="<?php echo $keyB?>">
				<a class='nav-link-a' href="javascript:;"><?php echo $categoryB->name;?><span> (<?php echo count($categoryB->events);?>)</span></a>
				<p></p>
			</li>
            <?php }; ?>
		</ul>
	</div>
	<div id="eb-categories">
		<?php
		foreach ($categories as $key=>$category)
		{
			if (!$config->show_empty_cat && !count($category->events))
			{
				continue ;
			}
			?>
			<div id="categories-list<?php echo $key;?>" class="row-fluid <?php echo $clearfixClass; ?> ">
				<?php
					if($category->description)
					{
					?>
						<div class="<?php echo $clearfixClass; ?>"><?php echo $category->description;?></div>
					<?php
					}

					if (count($category->events))
					{
						$user = JFactory::getUser();
						$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

						echo EventbookingHelperHtml::loadCommonLayout('common/events_table.php', array('items' => $category->events, 'config' => $config, 'Itemid' => $Itemid, 'nullDate' => JFactory::getDbo()->getNullDate(), 'ssl' => (int) $config->use_https, 'viewLevels' => $user->getAuthorisedViewLevels(), 'categoryId' => $category->id, 'bootstrapHelper' => $bootstrapHelper));
					}
				?>
			</div>
		<?php
		}
		?>
	</div>
</div>
<style>
    #eb-categories>div{
        display: none;
    }
    #eb-categories>div:first-child{
        display: block;
    }
</style>
<script>
	jQuery('.nav-ul').children().eq(0).children('p').addClass('active-p')
	jQuery('.nav-ul>li').click(
		function(){
		    var data_id = jQuery(this).attr('data-id');
		    var obj = jQuery('#eb-categories .row-fluid.clearfix');
             for (let i=0;i<obj.length;i++){
                 if(data_id==i){//选中
                     jQuery(obj[i]).css('display','block');
                 }else{
                     jQuery(obj[i]).css('display','none');
                 }
             }
		    console.log(jQuery(this).attr('data-id'))
			jQuery(this).children('p').addClass('active-p')
			jQuery(this).siblings().children('p').removeClass('active-p');
		}
	);

	jQuery('.phone-container-one-ul>button').click(function(){
        var data_id = jQuery(this).attr('data-id');
        jQuery('.phone-container-one-ul>button').each(function(index,item){
           if(data_id == index){
                jQuery(item).addClass('selected-button');
           } else{
                jQuery(item).removeClass('selected-button');
           }
        });
        jQuery("#eb-categories>div").each(function(index,item){
            if(data_id == index){
                jQuery(item).css("display","block");
            } else{
                jQuery(item).css("display","none");
            }
        })

    })
</script>
