<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$doc 	 = JFactory::getDocument();
$doc->addStyleSheet("components/com_jblance/css/customer/newsDetail.css");

$item = $this->item ;

EventbookingHelperData::prepareDisplayData([$item], @$item->main_category_id, $this->config, $this->Itemid);

$socialUrl = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')) . $item->url;

/* @var EventbookingHelperBootstrap $bootstrapHelper*/
$bootstrapHelper   = $this->bootstrapHelper;
$iconPencilClass   = $bootstrapHelper->getClassMapping('icon-pencil');
$iconOkClass       = $bootstrapHelper->getClassMapping('icon-ok');
$iconRemoveClass   = $bootstrapHelper->getClassMapping('icon-remove');
$iconDownloadClass = $bootstrapHelper->getClassMapping('icon-download');
$btnClass          = $bootstrapHelper->getClassMapping('btn');
$iconPrint         = $bootstrapHelper->getClassMapping('icon-print');
$clearfixClass     = $bootstrapHelper->getClassMapping('clearfix');
$return = base64_encode(JUri::getInstance()->toString());

$isMultipleDate = false;


if ($this->config->show_children_events_under_parent_event && $item->event_type == 1)
{
	$isMultipleDate = true;
}

$offset = JFactory::getConfig()->get('offset');

if ($this->showTaskBar)
{
	$layoutData = array(
		'item'              => $this->item,
		'config'            => $this->config,
		'isMultipleDate'    => $isMultipleDate,
		'canRegister'       => $item->can_register,
		'registrationOpen'  => $item->registration_open,
		'waitingList'       => $item->waiting_list,
		'return'            => $return,
		'showInviteFriend'  => true,
		'ssl'               => (int) $this->config->use_https,
		'Itemid'            => $this->Itemid,
		'btnClass'          => $btnClass,
		'iconOkClass'       => $iconOkClass,
		'iconRemoveClass'   => $iconRemoveClass,
		'iconDownloadClass' => $iconDownloadClass,
		'iconPencilClass'   => $iconPencilClass,
	);

	$registerButtons = EventbookingHelperHtml::loadCommonLayout('common/buttons.php', $layoutData);
}

if (!$this->config->get('show_group_rates', 1))
{
    $this->rowGroupRates = [];
}
?>
<div id="eb-event-page" class="eb-container eb-category-<?php echo $item->category_id; ?> eb-event<?php if ($item->featured) echo ' eb-featured-event'; ?>">
	<div class="eb-box-heading <?php echo $clearfixClass; ?>">
		<h1 class="eb-page-heading">
			<?php
			echo $item->title;

			if ($this->config->get('show_print_button', '1') === '1' && !$this->print)
			{
				$uri = clone JUri::getInstance();
				$uri->setVar('tmpl', 'component');
				$uri->setVar('print', '1');
			?>
			<!-- 打印功能没有先注释 -->
				<!-- <div id="pop-print" class="btn hidden-print">
					<a href="<?php echo $uri->toString();?> " rel="nofollow" target="_blank">
                        <span class="<?php echo $iconPrint; ?>"></span>
					</a>
				</div> -->
			<?php
			}
			?>
		</h1>
	</div>
	<div id="eb-event-details" class="eb-description">
		<?php
		// 设计图没有先注释
			// Facebook, twitter, Gplus share buttons
			// if ($this->config->show_fb_like_button)
			// {
			// 	echo $this->loadTemplate('share', ['socialUrl' => $socialUrl]);
			// }

			if ($this->showTaskBar && in_array($this->config->get('register_buttons_position', 0), array(1,2)))
			{
			?>
				<div class="eb-taskbar eb-register-buttons-top <?php echo $clearfixClass; ?>">
					<ul>
						<?php echo $registerButtons; ?>
					</ul>
				</div>
			<?php
			}
		?>

		<div class="eb-description-details <?php echo $clearfixClass; ?>">
			<?php
				$baseUri = JUri::base(true);

				if ($this->config->get('show_image_in_event_detail', 1) && $this->config->display_large_image && !empty($item->image_url))
				{
				?>
					<img src="<?php echo $item->image_url; ?>" class="eb-event-large-image img-polaroid"/>
				<?php
				}
				elseif ($this->config->get('show_image_in_event_detail', 1) && !empty($item->thumb_url))
				{
				?>
					<a href="<?php echo $item->image_url; ?>" class="eb-modal"><img src="<?php echo $item->thumb_url; ?>" class="eb-thumb-left" alt="<?php echo $item->title; ?>"/></a>
				<?php
				}
				// 设计图没有先注释
				// echo $item->description;
			?>
		</div>

        <div id="eb-event-info" class="<?php echo $clearfixClass . ' ' . $bootstrapHelper->getClassMapping('row-fluid'); ?>">
			<?php
			if (!empty($this->items))
			{
				echo EventbookingHelperHtml::loadCommonLayout('common/events_children.php', array('items' => $this->items, 'config' => $this->config, 'Itemid' => $this->Itemid, 'nullDate' => $this->nullDate, 'ssl' => (int) $this->config->use_https, 'viewLevels' => $this->viewLevels, 'categoryId' => $this->item->category_id, 'bootstrapHelper' => $this->bootstrapHelper));
			}
			else
			{
				$leftCssClass = 'span8';

				if (empty($this->rowGroupRates))
				{
					$leftCssClass = 'span12';
				}
			?>
				<div id="eb-event-info-left" class="<?php echo $bootstrapHelper->getClassMapping($leftCssClass); ?>">
					<h3 id="eb-event-properties-heading">
						<?php echo JText::_('EB_EVENT_PROPERTIES'); ?>
					</h3>
					<?php
					$layoutData = array(
						'item'           => $this->item,
						'config'         => $this->config,
						'location'       => $item->location,
						'showLocation'   => true,
						'isMultipleDate' => false,
						'nullDate'       => $this->nullDate,
						'Itemid'         => $this->Itemid,
					);

					echo EventbookingHelperHtml::loadCommonLayout('common/event_properties.php', $layoutData);
					?>
				</div>

				<?php
				if (count($this->rowGroupRates))
				{
					echo $this->loadTemplate('group_rates');
				}
			}
			?>
		</div>
		<div class="<?php echo $clearfixClass; ?>"></div>
		<div class="eb-container">
			<h3>活动内容</h3>
			<div style="padding:10px;border: 1px solid rgb(238,238,238); border-top: none; ">
				暂无内容
			</div>

		</div>
	<?php

	if ($this->config->show_location_info_in_event_details && $item->location && ($item->location->image || EventbookingHelper::isValidMessage($item->location->description)))
	{
		echo $this->loadTemplate('location', array('location' => $item->location));
	}

	foreach ($this->horizontalPlugins as $plugin)
    {
    ?>
        <h3 class="eb-horizntal-plugin-header"><?php echo $plugin['title']; ?></h3>
    <?php
        echo $plugin['form'];
    }

	if ($this->config->display_ticket_types && !empty($item->ticketTypes))
	{
		echo EventbookingHelperHtml::loadCommonLayout('common/tickettypes.php', array('ticketTypes' => $item->ticketTypes, 'config' => $this->config, 'event' => $item));
	?>
		<div class="<?php echo $clearfixClass; ?>"></div>
		
	<?php
	}

	if (!$item->can_register && $item->registration_type != 3 && $this->config->display_message_for_full_event && !$item->waiting_list && $item->registration_start_minutes >= 0 && empty($this->items))
	{
		if (@$item->user_registered)
		{
			$msg = JText::_('EB_YOU_REGISTERED_ALREADY');
		}
        elseif (!in_array($item->registration_access, $this->viewLevels))
		{
			if (JFactory::getUser()->id)
			{
				$msg = JText::_('EB_REGISTRATION_NOT_AVAILABLE_FOR_ACCOUNT');
			}
			else
			{
				$loginLink = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::getInstance()->toString()));
				$msg       = str_replace('[LOGIN_LINK]', $loginLink, JText::_('EB_LOGIN_TO_REGISTER'));
			}
		}
		else
		{
			$msg = JText::_('EB_NO_LONGER_ACCEPT_REGISTRATION');
		}
	?>
	
		<div class="text-info eb-notice-message"><?php echo $msg; ?></div>
		
		
	
	<?php
	}


	if ($this->showTaskBar && in_array($this->config->get('register_buttons_position', 0), array(0,2)))
	{
	?>
		<div class="eb-taskbar eb-register-buttons-bottom <?php echo $clearfixClass; ?>">
			<ul>
				<?php echo $registerButtons; ?>
			</ul>
		</div>
	<?php
	}

	if (count($this->plugins))
	{
		echo $this->loadTemplate('plugins');
	}
	// 不需要分享
	// if ($this->config->show_social_bookmark && !$this->print)
	// {
	// 	echo $this->loadTemplate('social_buttons', array('socialUrl' => $socialUrl));
	// }
?>
	</div>
</div>

<form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_eventbooking&Itemid=' . $this->Itemid); ?>" method="post">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>

<script language="javascript">
	function cancelRegistration(registrantId) {
		var form = document.adminForm ;
		if (confirm("<?php echo JText::_('EB_CANCEL_REGISTRATION_CONFIRM'); ?>")) {
			form.task.value = 'registrant.cancel' ;
			form.id.value = registrantId ;
			form.submit() ;
		}
	}
	<?php
	if ($this->print)
	{
	?>
		window.print();
	<?php
	}
?>
</script>
<?php
JFactory::getApplication()->triggerEvent('onDisplayEvents', [[$item]]);