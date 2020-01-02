<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
	.mod_mt_owners.tiles {
		overflow:hidden;
		margin:0;
	}
	#<?php echo $uniqid; ?> li {
		margin-bottom: 2px;
        padding: 2px 0 2px 0px;
        list-style: none;
        float: left;

        <?php
		if( !empty($tile_width) && $tile_width > 0 ) {
			echo 'width: '.$tile_width.";\n";
		}
		if( $tiles_flow == 'vertical' ) {
			echo "clear: both;\n";
		}
	?>}
	#<?php echo $uniqid; ?> li a img {
		width: <?php echo $image_size; ?>;
		height: <?php echo $image_size; ?>;
    }
	#<?php echo $uniqid; ?>.mod_mt_owners.tiles .mod-mt-owners-name-and-data {
		text-align: <?php echo $name_and_data_alignment; ?>;
	}
	#<?php echo $uniqid; ?>.mod_mt_owners.tiles .name {
		font-weight: bold;
		display:block;
	}
	#<?php echo $uniqid; ?>.mod_mt_owners.tiles li a.owner-thumb {
		width: <?php echo $image_size; ?>;
		vertical-align:top;
		float:left;
		border:1px solid #ddd;
		margin-right:1em;
		background-color:#e1e6fa;
		padding:2px;
		margin-bottom:.5em;
	}
</style>
<ul id="<?php echo $uniqid; ?>" class="mod_mt_owners tiles">
	<?php
	global $mtconf;

	$i = 0;
	if ( is_array($owners) ) {
		foreach( $owners AS $o ) {
			echo '<li'.(($i==0)?' class="first"':'').'>';

			// Image
			if( $show_images )
			{
				$profilepicture = new ProfilePicture($o->id);

				echo '<a class="owner-thumb" href="' . $o->url . '">';
				if( $profilepicture->exists() )
				{
					echo '<img src="'.$profilepicture->getURL(PROFILEPICTURE_SIZE_200).'" alt="'.$o->username.'" width="100px" height="100px" />';
				}
				else
				{
					echo '<img src="'.$profilepicture->getFillerURL(PROFILEPICTURE_SIZE_200).'" alt="'.$o->username.'" width="100px" height="100px" />';
				}
				echo '</a>';

			}

			if( $show_name || $show_listings || $show_reviews )
			{
				echo '<div class="mod-mt-owners-name-and-data">';
				if( $show_name ) {
					echo '<a class="name" href="' . $o->url . '">';
					echo $o->name;
					echo '</a>';
				}

				if( $show_listings ) {
					echo '<a class="mt-owner-listings" href="' . $o->listingsUrl . '">';
					echo JText::sprintf( 'MOD_MT_OWNERS_X_LISTINGS', $o->total_listings);
					echo '</a>';
				}

				if( $show_listings && $show_reviews ) {
					echo JText::_( 'MOD_MT_OWNERS_RELATED_DATA_SEPARATOR');
				}

				if( $show_reviews ) {
					echo '<a class="mt-owner-reviews" href="' . $o->reviewsUrl . '">';
					echo JText::sprintf( 'MOD_MT_OWNERS_X_REVIEWS', $o->total_reviews);
					echo '</a>';
				}
				echo '</div>';
			}

			echo '</li>';
			$i++;
		}
	}

	if ( $show_more ) {
		echo '<li class="showmore">';
		echo '<a href="';
		echo $show_more_link;
		echo '">';
		echo $caption_showmore . '</a></li>';
	}
?></ul>