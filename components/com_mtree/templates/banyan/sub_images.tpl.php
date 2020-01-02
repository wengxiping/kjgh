<script type="text/javascript" src="media/com_mtree/js/flexslider/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="media/com_mtree/js/magnific-popup/jquery.magnific-popup.min.js"></script>
<?php
if ($this->config->getTemParam('skipFirstImage','0') == 1) {
	array_shift($this->images);
}

if (
	is_array($this->images)
	&&
	!empty($this->images)
	):

	if( $config['details_view']['image_gallery']['max_width'] ) {
?>
<style>
	.slider-for .slick-slide.slick-active img {
		width: 100%;
	}
</style>
<?php } ?>
<div class="row-fluid">
	<div class="images"><?php
		if(isset($showImageSectionTitle) && $showImageSectionTitle) { ?>
		<div class="title"><?php echo JText::_( 'COM_MTREE_IMAGES' ); ?> (<?php
			if ($this->config->getTemParam('skipFirstImage','0') == 1) {
				echo ($this->total_images-1);
			} else {
				echo $this->total_images;
			}
		 ?>)</div><?php }

		$totalImages = count($this->images);

		if( $totalImages == 1 ) {
			$image = $this->images[0];
		?>
		<ul class="mt-thumbnails-only-one">
			<li <?php
			if( $config['details_view']['only_one_image']['max_width'] ) {
				echo 'style="width:100%"';
			}
			?>>
			<img src="<?php

			echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_medium_image'] . $image->filename;

			?>" alt="<?php echo $image->filename; ?>" <?php

			if( $config['details_view']['only_one_image']['max_width'] ) {
				echo 'style="width:100%"';
			}

			?>/>
			</li>
		</ul>

		<?php
		} else
		{
			?>
			<div id="mainslider" class="flexslider">
				<ul class="slides">
				<?php
				$i           = 0;
				foreach ($this->images AS $image):
					?>
					<li>
						<a href="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_original_image'] . $image->filename; ?>" class="original-image">
						<img src="<?php
						echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_original_image'] . $image->filename;
						?>" alt="<?php echo $image->filename; ?>"/>
						</a>
					</li>
					<?php
					$i++;
				endforeach;
				?>
					</ul>
			</div>
			<?php
		}

		if($totalImages > 1) { ?>

			<?php if ($this->config->getTemParam('showThumbnailSliderInDetailsView','1') == 1) { ?>

		<div id="carousel" class="flexslider">
		<ul class="slides">
		<?php
			$i = 0;
			foreach ($this->images AS $image):
			?>
			<li>
				<img src="<?php
					echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_small_image'] . $image->filename;
	            ?>" alt="<?php echo $image->filename; ?>"
				     altstyle="width:120px" />
			</li>
			<?php
				$i++;
			endforeach;
			?>
		</ul>
		</div>
			<?php } ?>
		<script type="text/javascript">
				jQuery(function () {

					jQuery('#mainslider').flexslider({
					animation: "none",
					controlNav: false,
					animationLoop: true,
					slideshow: false,
					sync: "#carousel",
						prevText: "",
						nextText: ""

					});

				jQuery('#carousel').flexslider({
					animation: "slide",
					controlNav: false,
					animateHeight: true,
					directionNav: true,
					animationLoop: true,
					slideshow: true,
					slideshowSpeed: 7000,
					animationDuration: 600,
					itemWidth: 120,
					itemMargin: 10,
					asNavFor: '#mainslider',
					prevText: "",
					nextText: ""
				});

				jQuery('.original-image').magnificPopup({
					type: 'image',
					gallery:{
						enabled:true
					}
				});
			});

		</script>
		<?php } ?>

	</div>
</div>

<?php endif; ?>
