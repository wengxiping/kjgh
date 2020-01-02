PayPlans.require()
.script('admin/vendors/moment', 'admin/vendors/daterangepicker')
.done(function($) {

	var moment = $.moment;
	var start = moment("<?php echo $start; ?>");
	var end = moment("<?php echo $end; ?>");
	var wrapper = $('[data-pp-date-range-<?php echo $uid;?>]');

	function update(start, end) {
		var display = wrapper.find('[data-pp-date-range-display]');
		display.html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
	}


	wrapper.daterangepicker({
		locale: {
			format: 'DD-MM-YYYY'
		},
		"applyClass" : 'btn-pp-primary-o',
		"cancelClass" : 'btn-pp-default-o',
		"startDate": <?php echo $start ? 'start' : 'moment()';?>,
		"endDate": <?php echo $end ? 'end' : 'moment()';?>,
		"opens": 'right',
		ranges: {
		   '<?php echo JText::_('COM_PP_TODAY');?>': [moment(), moment()],
		   '<?php echo JText::_('COM_PP_YESTERDAY');?>': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		   '<?php echo JText::_('COM_PP_LAST_7_DAYS');?>': [moment().subtract(6, 'days'), moment()],
		   '<?php echo JText::_('COM_PP_LAST_30_DAYS');?>': [moment().subtract(29, 'days'), moment()],
		   '<?php echo JText::_('COM_PP_THIS_MONTH');?>': [moment().startOf('month'), moment().endOf('month')],
		   '<?php echo JText::_('COM_PP_LAST_MONTH');?>': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	}, update);

	<?php if ($start && $end) { ?>
		update(start, end);
	<?php } else { ?>

		// Default to use today object from moment
		update(moment(), moment());
	<?php } ?>

	wrapper.on('apply.daterangepicker', function(event, picker) {
		var start = picker.startDate.format('DD-MM-YYYY');
		var end = picker.endDate.format('DD-MM-YYYY');

		$('[data-pp-date-start]').val(start);
		$('[data-pp-date-end]').val(end);
	});

	$('[data-pp-date-range-reset]').on('click', function() {
		$('[data-pp-date-start]').val('');
		$('[data-pp-date-end]').val('');
	});
});