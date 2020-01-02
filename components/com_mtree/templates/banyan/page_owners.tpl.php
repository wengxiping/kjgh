<div class="mt-listing-owners mt-template-<?php echo $this->template; ?> cat-id-<?php echo $this->cat_id ;?>">

	<h1 class="contentheading"><?php echo JText::_( 'COM_MTREE_LISTING_OWNERS' ) ?></h1>

	<?php
	if( $this->pageNav->total == 0 ) {

		?>
		<p class="mt-no-results-message"><?php echo JText::_( 'COM_MTREE_THERE_ARE_CURRENTLY_NO_LISTING_OWNERS' ) ?></p>
		<?php

	} else {
		?>
		<div class="mt-pages-links">
			<span class="xlistings"><?php echo $this->pageNav->getResultsCounter(); ?></span>
		</div><?php

		if($this->pageNav->total > 0) {

			$i = 0;
			foreach ($this->owners AS $owner) {
			$i++;
			$this->owner = $owner;
			?>
			<div class="row-fluid owner-profile">
				<?php
				// Show Owner Profile Picture
				if( $this->profilepicture_enabled )
				{
					?>
					<div class="span2">
						<?php
						$profilepicture = new ProfilePicture($this->owner->id);

						echo '<a href="' . $owner->url . '">';
						if( $profilepicture->exists() )
						{
							echo '<img src="'.$profilepicture->getURL(PROFILEPICTURE_SIZE_200).'" alt="'.$this->owner->username.'" width="100px" height="100px" />';
						}
						else
						{
							echo '<img src="'.$profilepicture->getFillerURL(PROFILEPICTURE_SIZE_200).'" alt="'.$this->owner->username.'" />';
						}
						echo '</a>';
						?>

					</div>
					<?php
				}
				?>
				<div class="span10 mt-listings-owners-details">
					<?php
					echo '<a class="mt-owner-name" href="' . $owner->url . '">' . $owner->name . '</a>';
					echo '<div>';

					echo '<a href="' . $owner->listingsUrl . '">';
					echo JText::sprintf( 'COM_MTREE_X_LISTINGS', $owner->total_listings );
					echo '</a>';

					if($this->mtconf['show_review'])
					{
						echo ' &middot; ';
						echo '<a href="' . $owner->reviewsUrl . '">';
						echo JText::sprintf('COM_MTREE_X_REVIEWS', $owner->total_reviews);
						echo '</a>';
					}
					?>
					</div>
				</div>
			</div>
			<?php
			}

			if( $this->pageNav->total > $this->pageNav->limit ) { ?>
				<div class="pagination">
					<p class="counter pull-right">
						<?php echo $this->pageNav->getPagesCounter(); ?>
					</p>
					<?php echo $this->pageNav->getPagesLinks(); ?>
				</div>
				<?php
			}
		}
	}
	?>
</div>