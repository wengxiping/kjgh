<?php
/**
* @package   yoo_master2
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// get theme configuration
include($this['path']->path('layouts:theme.config.php'));

?>
<!DOCTYPE HTML>
<html lang="<?php echo $this['config']->get('language'); ?>" dir="<?php echo $this['config']->get('direction'); ?>"  data-config='<?php echo $this['config']->get('body_config','{}'); ?>'>

<head>
<?php echo $this['template']->render('head'); ?>
</head>

<body class="<?php echo $this['config']->get('body_classes'); ?>">

    <?php if ($this['widgets']->count('logo + logo-small + menu + offcanvas + toolbar-l + toolbar-r')) : ?>

        <div class="tm-navbar-container">

            <?php if ($this['widgets']->count('toolbar-l + toolbar-r')) : ?>
            <div class="tm-toolbar uk-clearfix uk-hidden-small">
                <div class="uk-container uk-container-center">

                    <?php if ($this['widgets']->count('toolbar-l')) : ?>
                    <div class="uk-float-left"><?php echo $this['widgets']->render('toolbar-l'); ?></div>
                    <?php endif; ?>

                    <?php if ($this['widgets']->count('toolbar-r')) : ?>
                    <div class="uk-float-right"><?php echo $this['widgets']->render('toolbar-r'); ?></div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endif; ?>

            <?php echo $this['template']->render('header.'.$this['config']->get('navigation_style', 'default').''); ?>

        </div>
    <?php endif; ?>
    
	<?php if ($this['widgets']->count('top-hero')) : ?>
        <div id="tm-top-hero" class="tm-hero-container uk-position-relative <?php echo $block_classes['top-hero']; ?>" <?php echo implode(' ', array($styles['block.top-hero'], $block_attributes['top-hero'])); ?>>
            <section class="<?php echo $grid_classes['top-hero']; echo $display_classes['top-hero']; ?> uk-flex uk-flex-middle" data-uk-grid-margin><?php echo $this['widgets']->render('top-hero', array('layout'=>$this['config']->get('grid.top-hero.layout'))); ?></section>
        </div>
        
	<?php endif; ?>

	<div class="uk-container uk-container-center">

		<?php if ($this['widgets']->count('top-a')) : ?>
		<section id="tm-top-a" class="<?php echo $grid_classes['top-a']; echo $display_classes['top-a']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('top-a', array('layout'=>$this['config']->get('grid.top-a.layout'))); ?></section>
		<?php endif; ?>

		<?php if ($this['widgets']->count('top-b')) : ?>
		<section id="tm-top-b" class="<?php echo $grid_classes['top-b']; echo $display_classes['top-b']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('top-b', array('layout'=>$this['config']->get('grid.top-b.layout'))); ?></section>
		<?php endif; ?>

		<?php if ($this['widgets']->count('main-top + main-bottom + sidebar-a + sidebar-b') || $this['config']->get('system_output', true)) : ?>
		<div id="tm-middle" class="tm-middle uk-grid" data-uk-grid-match data-uk-grid-margin>

			<?php if ($this['widgets']->count('main-top + main-bottom') || $this['config']->get('system_output', true)) : ?>
			<div class="<?php echo $columns['main']['class'] ?>">

				<?php if ($this['widgets']->count('main-top')) : ?>
				<section id="tm-main-top" class="<?php echo $grid_classes['main-top']; echo $display_classes['main-top']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('main-top', array('layout'=>$this['config']->get('grid.main-top.layout'))); ?></section>
				<?php endif; ?>

				<?php if ($this['config']->get('system_output', true)) : ?>
				<main id="tm-content" class="tm-content">

					<?php if ($this['widgets']->count('breadcrumbs')) : ?>
					<?php echo $this['widgets']->render('breadcrumbs'); ?>
					<?php endif; ?>

					<?php echo $this['template']->render('content'); ?>

				</main>
				<?php endif; ?>

				<?php if ($this['widgets']->count('main-bottom')) : ?>
				<section id="tm-main-bottom" class="<?php echo $grid_classes['main-bottom']; echo $display_classes['main-bottom']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('main-bottom', array('layout'=>$this['config']->get('grid.main-bottom.layout'))); ?></section>
				<?php endif; ?>

			</div>
			<?php endif; ?>

            <?php foreach($columns as $name => &$column) : ?>
            <?php if ($name != 'main' && $this['widgets']->count($name)) : ?>
            <aside class="<?php echo $column['class'] ?>"><?php echo $this['widgets']->render($name) ?></aside>
            <?php endif ?>
            <?php endforeach ?>

		</div>
		<?php endif; ?>

		<?php if ($this['widgets']->count('bottom-a')) : ?>
		<section id="tm-bottom-a" class="<?php echo $grid_classes['bottom-a']; echo $display_classes['bottom-a']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('bottom-a', array('layout'=>$this['config']->get('grid.bottom-a.layout'))); ?></section>
		<?php endif; ?>

		<?php if ($this['widgets']->count('bottom-b')) : ?>
		<section id="tm-bottom-b" class="<?php echo $grid_classes['bottom-b']; echo $display_classes['bottom-b']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('bottom-b', array('layout'=>$this['config']->get('grid.bottom-b.layout'))); ?></section>
		<?php endif; ?>

	</div>
	
	<?php if ($this['widgets']->count('block-footer')) : ?>
    <div id="tm-block-footer" class="<?php echo $footer_background; echo $footer_padding; ?> uk-block tm-block-footer-bg uk-margin-top">
        <div class="uk-container uk-container-center">
            <section class=" <?php echo $grid_classes['block-footer']; echo $display_classes['block-footer']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('block-footer', array('layout'=>$this['config']->get('grid.block-footer.layout'))); ?></section>
        </div>
    </div>
    <?php endif; ?>
    
	<?php if ($this['widgets']->count('footer + debug') || $this['config']->get('warp_branding', true) || $this['config']->get('totop_scroller', true)) : ?>
	<footer id="tm-footer" class="tm-footer">

		<?php if ($this['config']->get('totop_scroller', true)) : ?>
		<a class="tm-totop-scroller" data-uk-smooth-scroll href="#"></a>
		<?php endif; ?>

		<?php
			echo $this['widgets']->render('footer');
			$this->output('warp_branding');
			echo $this['widgets']->render('debug');
		?>

	</footer>
	<?php endif; ?>

	<?php echo $this->render('footer'); ?>

	<?php if ($this['widgets']->count('offcanvas')) : ?>
	<div id="offcanvas" class="uk-offcanvas">
		<div class="uk-offcanvas-bar"><?php echo $this['widgets']->render('offcanvas'); ?></div>
	</div>
	<?php endif; ?>

</body>
</html>