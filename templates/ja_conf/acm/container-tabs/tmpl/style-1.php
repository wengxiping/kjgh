<?php
	$items_position = $helper->get('position');
	$mods = JModuleHelper::getModules($items_position);
	$tabsHeading 					= $helper->get('tabs-heading');
?>

<div class="acm-container-tabs" id="mod-<?php echo $module->id ?>">
	<?php if($tabsHeading || $module->showtitle) : ?>
  	<div class="section-title">
	    <?php if($tabsHeading) : ?>
	    <div class="title-intro">
	      <?php echo $tabsHeading; ?>
	    </div>
	    <?php endif; ?>

	    <?php if($module->showtitle): ?>
				<h3 class="title-lead h1"><?php echo $module->title ?></h3>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="container-tabs-nav">
		<!-- BEGIN: TAB NAV -->
		<ul class="nav nav-tabs" role="tablist">
			<?php
			$i = 0;
			foreach ($mods as $mod):
				?>
				<li class="<?php if ($i < 1) echo "active"; ?>">
					<a href="#mod-<?php echo $mod->id ?>" role="tab"
						 data-toggle="tab"><?php echo $mod->title ?></a>
				</li>
				<?php
				$i++;
			endforeach
			?>

		</ul>
		<!-- END: TAB NAV -->
	</div>

	<!-- BEGIN: TAB PANES -->
	<div class="tab-content">
		<?php
		echo $helper->renderModules($items_position,
			array(
				'style'=>'ACMContainerItems',
				'active'=>0,
				'tag'=>'div',
				'class'=>'tab-pane fade'
			))
		?>
	</div>
	<!-- END: TAB PANES -->
</div>

<script>
	jQuery(document).ready(function(){
		jQuery('#mod-<?php echo $module->id; ?>').find('li a').each(function(){
			$realid = jQuery(this).attr('href');
			$_realid = $realid+'_<?php echo $module->id ?>';
			jQuery('#mod-<?php echo $module->id; ?>').find('div'+$realid).attr('id', $_realid.replace('#',''));
			jQuery(this).attr('href', $_realid);
		});
	});
</script>