PayPlans.require()
.script('admin/vendors/chart')
.done(function($) {

	function renderSalesChart(salesFigure) {
		$('[data-dashboard-content-tab]').removeClass('is-loading');

		var salesDatasets = [];
		var salesDataTooltips = [];
		var dateLabels = [];

		if (salesFigure) {
			$.each(salesFigure, function(key, value) {
				dateLabels.push(key);
				salesDatasets.push(value.total_1);

				salesDataTooltips[key] = value;
			});
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
						var tooltipData = salesDataTooltips[tooltipItem[0].xLabel];
						return tooltipData.tooltip_title;
					},
					label: function(tooltipItem, data) {
						var tooltipData = salesDataTooltips[tooltipItem.xLabel];
						return tooltipData.tooltip_text;
					}
				}
			}
		};

		new Chart('chart-revenue', {
			type: 'line',
			data: {
				labels: dateLabels,
				datasets: [{
					backgroundColor: "rgba(220,237,200, 0.2)",
					borderColor: 'rgba(51,105,30, 0.6)',
					borderWidth: 1.3,
					data: salesDatasets,
					fill: 'start'
				}]
			},
			options: Chart.helpers.merge(options, {
				legend: {
					display: false
				},
				title: {
					display: false
				}
			})
		});
	}

	function renderPlansChart(plansFigure) {
		var wrapper = $('[data-dashboard-plans-tab]');
		var canvas = $('#canvas-holder');

		wrapper.removeClass('is-loading');

		if (!plansFigure) {
			canvas.addClass('t-hidden');
			wrapper.addClass('is-empty');
			return;
		}

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

	PayPlans.ajax('admin/controllers/dashboard/renderChart')
	.done(function(chartData) {
		renderSalesChart(chartData.chartFigure);
		renderPlansChart(chartData.plansFigure);
	});
});