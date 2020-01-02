<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$item = $displayData['item'];
$aparams = $displayData['params'];
$params = $item->params;
$positions = $aparams->get('block_position', 0);

$useDefList =
	($aparams->get('show_modify_date') ||
		$aparams->get('show_publish_date') ||
		$aparams->get('show_create_date') ||
		$aparams->get('show_hits') ||
		$aparams->get('show_category') ||
		$aparams->get('show_parent_category') ||
		$aparams->get('show_author'));
$aparams->set('show_category',1);

$tplparams = JFactory::getApplication()->getTemplate(true)->params;

$ads_modules = 'event-aside';
$attrs = array('style'=>'t3xhtml');
$result = null;
$renderer = JFactory::getDocument()->loadRenderer('modules');
$modules = $renderer->render($ads_modules, $attrs, $result);

$buy_link = $params->get('ctm_buy_link');
?>

<div class="equal-height">
<!-- Event Main -->
<div class="col event-main">

	<div class="magazine-item-main">

		<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>

		<?php if ($useDefList && in_array($positions, array(0, 2))) : ?>
			<aside class="article-aside article-aside-full">
				<?php echo JLayoutHelper::render('joomla.content.info_block.magazine_block', array('item' => $item, 'params' => $aparams, 'position' => 'above')); ?>
			</aside>
		<?php endif; ?>

		<div class="magazine-item-media">
			<?php echo JLayoutHelper::render('joomla.content.image.intro', $displayData); ?>
			<?php $title = $item->category_title; ?>
		</div>
		
		<?php if (!$aparams->get('show_intro', 1)) : ?>
			<?php echo $item->event->afterDisplayTitle; ?>
		<?php endif; ?>
		<?php echo $item->event->beforeDisplayContent; ?>

		<?php if ($useDefList && in_array($positions, array(1, 2))) : ?>
			<aside class="article-aside">
				<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $aparams, 'position' => 'below')); ?>
			</aside>
		<?php endif; ?>

		<?php if ($aparams->get('show_intro', 0)) : ?>
			<blockquote class="article-intro" itemprop="description">
				<?php echo $item->introtext; ?>
			</blockquote>
		<?php endif; ?>
		<section class="article-content" itemprop="description">
			<?php echo JLayoutHelper::render('joomla.content.info_block.topic', array('item' => $item)); ?>

			<?php if (isset ($item->toc)) : ?>
				<?php echo $item->toc; ?>
			<?php endif; ?>
			<?php echo $item->text; ?>

			<?php if ($params->get('show_tags', 1) && !empty($item->tags)) : ?>
				<?php echo JLayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
			<?php endif; ?>
		</section>

		<?php echo $item->event->afterDisplayContent; ?>
	</div>

</div>
<!-- // Event Main -->

<!-- Event Sidebar -->
<aside class="col event-aside">

	<?php if($item->params->get('ctm_logo')): ?>
	<div class="t3-module module event-logo">
		<div class="module-inner">
			<div class="module-ct" itemprop="image">
				<img src="<?php echo $item->params->get('ctm_logo'); ?>" alt="Event logo" >
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="t3-module module event-location">
		<div class="module-inner">

			<h3 class="module-title">
				<span itemprop="location"><?php echo JText::_('TPL_WHEN_WHERE'); ?></span>
			</h3>

			<div class="module-ct">

				<address itemprop="address">
					<strong><?php echo $item->params->get('ctm_venue'); ?></strong><br />
                    <?php
                        $config = JFactory::getConfig();
                        $tz = new DateTimeZone($config->get('offset'));
                        $date_start = new JDate($params->get('ctm_start',''));
                        $date_end = new JDate($params->get('ctm_end',''));
                    ?>
                    Start Time: <?php echo $date_start->format(JText::_('DATE_FORMAT_LC3')); ?><br /> 
                    End Time: <?php echo $date_end->format(JText::_('DATE_FORMAT_LC3')); ?> <br />
					<?php echo $item->params->get('ctm_addr1'); ?><br />
					<?php echo $item->params->get('ctm_addr2'); ?>
				</address>

				<?php if($item->params->get('ctm_latitude') && $item->params->get('ctm_longitude')): ?>
					<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
					<script type="text/javascript">
						function initialize() {
						  var jaLatLng = { lat: <?php echo (float) $item->params->get('ctm_latitude'); ?>, lng: <?php echo (float) $item->params->get('ctm_longitude'); ?>};
						  var map = new google.maps.Map(document.getElementById('ja-event-map'),{
			                 zoom: 15,
                             center: jaLatLng
						  });

                          // Add a marker at the center of the map
                          addMarker(jaLatLng, map);
                        }
                        
                        function addMarker(location, map){
                            var marker = new google.maps.Marker({
                                position: location,
                                map: map,
                                title: '<?php echo $item->params->get('ctm_addr1'); ?>'
                            })
                        }
						google.maps.event.addDomListener(window, 'load', initialize);
					</script>
					<div id="ja-event-map" style="max-width: 100%; height: 200px;"></div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
	$speakers = $item->params->get('ctm_speakers', array());
	$sid = isset($speakers['sid']) ? (array) $speakers['sid'] : array();
	if(count($sid) > 1 || !empty($sid[0])):
	?>
	<div class="t3-module module event-speakers">
		<div class="module-inner">

			<h3 class="module-title">
				<span><?php echo JText::_('TPL_SPEAKERS'); ?></span>
			</h3>

			<div class="module-ct" itemprop="performer">
			<?php
			foreach($speakers['sid'] as $speaker) {
				echo JLayoutHelper::render('joomla.content.info_block.contact', array('contact_id' => $speaker));
			}
			?>
			</div>

		</div>
	</div>
	<?php endif; ?>

	<?php
	$sponsors = $item->params->get('ctm_sponsors', array());
	$sid = isset($sponsors['sid']) ? (array) $sponsors['sid'] : array();
	if(count($sid) > 1 || !empty($sid[0])):
	?>
	<div class="t3-module module event-sponsors">
		<div class="module-inner">

			<h3 class="module-title">
				<span><?php echo JText::_('TPL_SPONSORS'); ?></span>
			</h3>

			<div class="module-ct">
			<?php
			foreach($sponsors['sid'] as $sponsor) {
				echo JLayoutHelper::render('joomla.content.info_block.contact', array('contact_id' => $sponsor));
			}
			?>
			</div>

		</div>
	</div>
	<?php endif; ?>

	<?php
	$tickets = $item->params->get('ctm_tickets', array());
	$tid = isset($tickets['class']) ? (array) $tickets['class'] : array();
    
	if(count($tid) > 1 || !empty($tid[0]) || $tickets['desc'][0] != ''):
	?>
	<div class="t3-module module event-tickets">
		<div class="module-inner">

			<h3 class="module-title">
				<span><?php echo JText::_('TPL_TICKETS'); ?></span>
			</h3>

			<div class="module-ct">
				<table class="table">
					<thead>
					<tr>
						<th><?php echo JText::_('Type'); ?></th>
						<th><?php echo JText::_('Price'); ?></th>
						<th><?php echo JText::_('Sale End'); ?></th>
					</tr>
					</thead>
					<tbody>

					<?php foreach($tickets['class'] as  $index => $ticket_type): ?>
						<tr itemprop="offers">
							<td>
								<?php echo $ticket_type; ?>
								<?php if($tickets['desc'][$index]): ?>
									<span class="fa fa-question-circle hasTooltip"  title="<?php echo htmlspecialchars($tickets['desc'][$index]); ?>"></span>
								<?php endif; ?>
							</td>
							<td itemprop="price"><?php echo $tickets['price'][$index]; ?></td>
							<td itemprop="offerCount"><?php echo $tickets['end'][$index]; ?></td>
						</tr>
					<?php endforeach; ?>

					</tbody>
				</table>
			</div>

		</div>
	</div>
	<?php endif; ?>

	<?php if (!empty($buy_link)) : ?>
		<div class="module buy-link">
			<div class="module-inner">

				<div class="module-ct">
					<a class="btn btn-primary btn-lg" href="<?php echo $buy_link; ?>" title="<?php echo JText::_('TPL_BUY_NOW'); ?>"><?php echo JText::_('TPL_BUY_NOW'); ?></a>
				</div>
			</div>
		</div>
			
	<?php endif; ?>
	
	<?php if ($modules) : ?>
		<?php echo $modules ?>
	<?php endif ?>

</aside>
<!-- // Event Sidebar -->
</div>