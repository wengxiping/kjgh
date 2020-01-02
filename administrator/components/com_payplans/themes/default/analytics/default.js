PayPlans.require()
.script('admin/vendors/chart')
.done(function($) {

	function renderChartGraph(chartFigure, chartTitle, type) {
		var wrapper = $('[data-pp-analytics-chart]');
		wrapper.removeClass('is-loading');

		var data = [];
		var data2 = [];
		var datasetsTooltips = [];
		var dateLabels = [];

		var total = 0;

		if (chartFigure) {
			$.each(chartFigure, function(key, value) {
				dateLabels.push(key);
				data.push(value.total_1);

				datasetsTooltips[key] = value;
				total++;

				if (type == 'growth') {
					data2.push(value.total_2);
				}
			});
		}

		if (total == 1) {
			data.splice(0, 0, NaN);
			data.splice(2, 0, NaN);

			dateLabels.splice(0, 0, NaN);
			dateLabels.splice(2, 0, NaN);

			datasetsTooltips.splice(0, 0, NaN);
			datasetsTooltips.splice(2, 0, NaN);
		}

		var options = {
			maintainAspectRatio: false,
			spanGaps: false,
			elements: {
				line: {
					tension: 0.000001
				}
			},
			scales: {
				yAxes: [{
					gridLines: {
						drawBorder: true,
						color: '#f4f4f4',
						zeroLineColor: '#f4f4f4'
					},
					ticks: {
						min: 0,
						beginAtZero: true
					}
				}],
				xAxes: [{
					gridLines: {
						drawBorder: false,
						zeroLineColor: '#f4f4f4',
						display: false
					}
				}]
			},
			tooltips: {
				xPadding: 16,
				yPadding: 10,
				cornerRadius: 0,
				titleMarginBottom: 10,
				callbacks: {
					title: function(tooltipItem, data) {
						var tooltipData = datasetsTooltips[tooltipItem[0].xLabel];
						return tooltipData.tooltip_title;
					},
					label: function(tooltipItem, data) {
						var tooltipData = datasetsTooltips[tooltipItem.xLabel];
						return tooltipData.tooltip_text;
					}
				}
			}
		};

		datasets = [{
					backgroundColor: "rgba(220,237,200, 0.2)",
					borderColor: 'rgba(51,105,30, 0.6)',
					borderWidth: 1.3,
					data: data,
					fill: 'start'
				}]

		if (data2) {
			var dataset2 = {
					backgroundColor: "rgba(239,154,154, 0.2)",
					borderColor: 'rgba(211,47,47, 0.6)',
					borderWidth: 1.3,
					data: data2,
					fill: 'start'
				}

			datasets.push(dataset2);
		}

		window.myLine = new Chart('chart-revenue', {
			type: 'line',
			data: {
				labels: dateLabels,
				datasets: datasets
			},
			options: Chart.helpers.merge(options, {
				legend: {
					display: false
				},
				title: {
					display: true,
					text: chartTitle
				}
			})
		});
	}

	function renderPlans(plansFigure, chartTitle) {
		var wrapper = $('[data-pp-analytics-plan]');
		wrapper.removeClass('is-loading');

		planData = [];
		planColor = [];
		planDataTooltips = [];
		planLabels = [];

		if (plansFigure) {
			var index = 0;

			$.each(plansFigure, function(key, value) {
				planLabels.push(value.shortTitle);
				planData.push(value.total_2);
				planColor.push(value.background_color);

				planDataTooltips[index] = value;
				index++;
			});
		} else {
			wrapper.addClass('is-empty');
			wrapper.find('#canvas-holder').addClass('t-hidden');
			return;
		}

		var config = {
			type: 'doughnut',
			data: {
				labels: planLabels,
				datasets: [{
					data: planData,
					backgroundColor: planColor
				}]
			},
			options: {
				responsive: true,
				legend: {
					position: 'right',
					labels: {
						boxWidth: 16
					}
				},
				title: {
					display: false,
					text: chartTitle
				},
				animation: {
					animateScale: true,
					animateRotate: true
				},
				tooltips: {
					xPadding: 16,
					yPadding: 10,
					cornerRadius: 0,
					titleMarginBottom: 10,
					callbacks: {
						title: function(tooltipItem) {
							var tooltipData = planDataTooltips[tooltipItem[0].index];
							return tooltipData.title;
						},
						label: function(tooltipItem, data) {
							var tooltipData = planDataTooltips[tooltipItem.index];
							return tooltipData.tooltip_text;
						}
					}
				}
			}
		};

		var ctx = document.getElementById('chart-area').getContext('2d');
		window.myDoughnut = new Chart(ctx, config);
	}

	function renderChart() {
		$('[data-analytics-chart-plans]').addClass('is-loading').removeClass('is-empty');
		$('[data-analytics-chart-revenue]').addClass('is-loading');

		// Destroy previous chart if exists
		if (window.myLine) {
			window.myLine.destroy();
		}

		if (window.myDoughnut) {
			window.myDoughnut.destroy();
		}

		$('[data-chart-listings]').html('');

		PayPlans.ajax('admin/controllers/analytics/renderChart', {
			"duration" : "<?php echo $duration; ?>",
			"type": "<?php echo $type; ?>",
			"customStartDate": "<?php echo $customStartDate; ?>",
			"customEndDate": "<?php echo $customEndDate; ?>",
			"dummyData" : "<?php echo $dummyData; ?>"
		}).done(function(chartData) {

			var type = '<?php echo $type; ?>';

			renderChartGraph(chartData.chartFigure, chartData.chartTitle, type);
			renderPlans(chartData.plansFigure, type);

			$('[data-pp-analytics-chart-label]').html(chartData.chartFigureLabel);
			$('[data-pp-analytics-plan-label]').html(chartData.plansFigureLabel);
			$('[data-chart-listings]').html(chartData.listings)
		});
	}

	function startRebuild(current, totalDays, rebuildLimit, form) {
		PayPlans.ajax('admin/controllers/analytics/rebuildStat', {
			'current' : current,
			'totalDays' : totalDays,
			'rebuildLimit' : rebuildLimit
		}).done(function(message) {
			current = current + rebuildLimit;

			var percentage = current / totalDays * 100;
			form.progressBar().css('width', percentage + '%');
			form.progressCounter().html(message);

			if (percentage > 99) {
				rebuildComplete(form);
			} else {
				startRebuild(current, totalDays, rebuildLimit, form);
			}
		});
	}

	function rebuildComplete(form) {
		form.progressInfo().addClass('t-hidden');
		form.startButton().addClass('t-hidden');
		form.finishInfo().removeClass('t-hidden');

		// Override close button
		form.cancelButton().on('click', function() {
			renderChart();
		});
	}

	function rebuildStat() {

		// Render confirmation dialog
		PayPlans.dialog({
			"content": PayPlans.ajax('admin/views/analytics/confirmRebuildStat'),
			"bindings": {
				"{startButton} click": function() {
					var totalDays = parseInt(this.form().find('[data-total-days]').val());
					var rebuildLimit = parseInt(this.form().find('[data-rebuild-limit]').val());

					var progressBar = this.progressBar();
					this.confirmationInfo().addClass('t-hidden');
					this.progressWrapper().removeClass('t-hidden');

					var current = 0;

					startRebuild(current, totalDays, rebuildLimit, this);
				}
			}
		});
	}

	renderChart();

	function showSalesTooltip(activeElements) {
		window.myLine.tooltip._active = activeElements != undefined ? activeElements : [];
		window.myLine.tooltip.update(true);
		window.myLine.draw();
	}

	$(document).on('mouseover.pp.analytics', '[data-chart-list]', function() {
		var index = $(this).data('index');
		var requestedElem = window.myLine.getDatasetMeta(0).data[index];

		showSalesTooltip([requestedElem]);
	});

	$(document).on('mouseleave.pp.analytics', '[data-chart-list]', function() {
		showSalesTooltip();
	});

	$.Joomla('submitbutton', function(task) {

		if (task == 'updateStat') {
			renderChart();
		}

		if (task == 'rebuildStat') {
			rebuildStat();
		}
	});
});