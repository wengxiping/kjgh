EasySocial.require()
.script("site/albums/browser")
.done(function($){

	var moreAlbumsStartLimit = "<?php echo $startlimit; ?>";

	$("[data-album-browser=<?php echo $uuid; ?>]")
		.addController("EasySocial.Controller.Albums.Browser", {
			"uid": "<?php echo $lib->uid;?>",
			"type": "<?php echo $lib->type; ?>"
		});

	$('[data-album-showall]').on('click', function() {

		var button = $(this);

		EasySocial.ajax('site/views/albums/showMoreAlbums', {
			"totalalbums": "<?php echo $totalAlbums; ?>",
			"startlimit": moreAlbumsStartLimit,
			"userAlbumOwnerId": "<?php echo $lib->uid; ?>",
			"albumType": "<?php echo $lib->type; ?>",
			"albumId": "<?php echo $id; ?>"

		}).done(function(contents, nextlimit) {

			// append the rest of the albums item
			$('[data-album-list-item-container-regular]').append(contents);

			if (nextlimit > 0) {
				moreAlbumsStartLimit = nextlimit;
			} else {
				// hide the view all button
				button.hide();
			}

		});
	});
});
