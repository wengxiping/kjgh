EasySocial
.require()
.script('site/vendors/jquery.raty')
.done(function($){
	var ratings = $('[data-es-ratings-stars]');
    ratings.raty({
        score: ratings.data('score')});

    $('[data-reviews-save-button]').on('click', function(event) {

			// Supply all the necessary info to the caller
			var form = $('[data-es-review-form]');

			var score = $('[data-es-review-score]');
			var title = $('[data-es-review-title]');
			var message = $('[data-es-review-message]');

			// Remove error class
			score.removeClass("has-error");
			title.removeClass("has-error");
			message.removeClass("has-error");

			var hasError = false;

			if (score.find("input[name='score']").val() == '') {
				score.addClass("has-error");
				
				hasError = true;
			}

			if (title.find("input").val() == '') {
				title.addClass("has-error");
				
				hasError = true;
			}

			if (message.find("textarea").val() == '') {
				message.addClass("has-error");
				
				hasError = true;
			}

			if (hasError) {
				return false;
			}

			form.submit();

		});

});
