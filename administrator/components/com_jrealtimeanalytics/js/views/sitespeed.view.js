/**
 * JS APP interface view, build interface data programmatically
 * 
 * @package JREALTIMEANALYTICS::SITESPEED::administrator::components::com_jrealtimeanalytics
 * @subpackage js
 * @subpackage views
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 or later, see license.txt
 */
//'use strict';
(function($) {
	var ViewSitespeed = function(options) {
		/**
		 * Tree structure for interface building
		 * 
		 * @access private
		 * @var Object
		 */
		var DOMTreeInterface = {
			leftContainer : {
				elementType : 'div',
				id : 'placeholder_leftcontainer',
				classname : 'span7 lefttext_stats',
				childs : {
					gaugeMeter : {
						elementType : 'div',
						id : 'placeholder_gaugemeter',
						'data-bind' : '{jr-gaugemeter}'
					},
					starsContainer : {
						elementType : 'div',
						id : 'placeholder_stars_container',
						'data-bind' : '{jr-stars_container}'
					},
					textLoadTimeStats : {
						elementType : 'div',
						id : 'placeholder_text_loadtime',
						'data-bind' : '{jr-text_loadtime}',
						classname : 'statslabel',
						childs : {
							iconSecondsLabel : {
								elementType : 'span',
								classname : 'statslabel_value icon-clock'
							},
							textSecondsLabel : {
								'data-bind' : '{jr-text_seconds}',
								elementType : 'span',
								classname : 'statslabel_text'
							}
						}
					}
				}
			},
			rightContainer : {
				elementType : 'div',
				id : 'placeholder_rightcontainer',
				classname : 'span5 rightgraph_stats',
				childs : {
					textReport : {
						elementType : 'div',
						id : 'placeholder_text_report',
						classname : 'hasPopover',
						'data-bind' : '{jr-text_report}',
						childs : {
							popoverContainer : {
								elementType : 'div',
								classname : 'popover right in',
								childs : {
									popoverArrowDiv : {
										elementType : 'div',
										classname : 'arrow',
									},
									popoverH3Title : {
										elementType : 'h3',
										classname : 'popover-title',
										'data-bind' : '{jr-text_report_title}'
									},
									popoverContentDiv : {
										elementType : 'div',
										classname : 'popover-content',
										'data-bind' : '{jr-text_report_review}'
									}
								}
							}
						}
					}
				}
			}
		};

		/**
		 * Recursive function to build a view interface from a POJO, compile
		 * data-binding for data model MVVM
		 * 
		 * @access private
		 * @return Void
		 */
		var buildInterface = function(parent, childs) {
			// Inject DOM elements for view interface
			$.each(childs, function(index, element) {
				// Append main parent container
				var newElement = $('<' + element.elementType + '/>', {
					id : element.id,
					'data-bind' : element['data-bind'],
					class : element.classname
				});
				parent.append(newElement);

				if (element.childs) {
					buildInterface(newElement, element.childs);
				}
			});
		}

		/**
		 * Manage Responsive view for Popover also on window resize
		 * 
		 * @access private
		 * @return Void
		 */
		var repositionResponsiveElements = function() {
			var windowWidth = $(window).width();

			// Breakpoint 1299px pre-sublinearization widgets
			if (windowWidth < 1299) {
				$('#' + DOMTreeInterface.rightContainer.childs.textReport.id + ' > div').removeClass('right').addClass('bottom');
			} else {
				$('#' + DOMTreeInterface.rightContainer.childs.textReport.id + ' > div').removeClass('bottom').addClass('right');
			}
		}

		/**
		 * Manage DOM view, inject elems
		 * 
		 * @access public
		 * @param Object
		 *            data
		 * @return void
		 */
		this.renderStats = function(data) {
			// Reset always view before render data
			$('#' + options.container).children().remove();
			$('#' + DOMTreeInterface.leftContainer.id).remove();
			$('#' + DOMTreeInterface.rightContainer.id).remove();

			// Get data bound element
			var dataBindElement = $('* [data-bind="{' + options.databind + '}"]');

			buildInterface(dataBindElement, DOMTreeInterface);

			// Assign timing value to text label and presentation colors
			$('#' + DOMTreeInterface.leftContainer.childs.textLoadTimeStats.id).addClass(data.labelcolor);
			$('*[data-bind="' + DOMTreeInterface.leftContainer.childs.textLoadTimeStats.childs.textSecondsLabel['data-bind'] + '"]').text(data.timeSpeed.label);

			// New instance of gauge meter with test value assigned to scale
			$('*[data-bind="' + DOMTreeInterface.leftContainer.childs.gaugeMeter['data-bind'] + '"]').jqxGauge({
				ranges : [ {
					startValue : 0,
					endValue : 3,
					style : {
						fill : '#1AA148',
						stroke : '#1AA148'
					},
					startDistance : '5%',
					endDistance : '5%',
					endWidth : 8,
					startWidth : 5
				}, {
					startValue : 3,
					endValue : 6,
					style : {
						fill : '#f6de54',
						stroke : '#f6de54'
					},
					startDistance : '5%',
					endDistance : '5%',
					endWidth : 10,
					startWidth : 8
				}, {
					startValue : 6,
					endValue : 9,
					style : {
						fill : '#F9942D',
						stroke : '#F9942D'
					},
					startDistance : '5%',
					endDistance : '5%',
					endWidth : 12,
					startWidth : 10
				}, {
					startValue : 9,
					endValue : 12,
					style : {
						fill : '#db5016',
						stroke : '#db5016'
					},
					startDistance : '5%',
					endDistance : '5%',
					endWidth : 14,
					startWidth : 12
				}, {
					startValue : 12,
					endValue : 15,
					style : {
						fill : '#d02841',
						stroke : '#d02841'
					},
					startDistance : '5%',
					endDistance : '5%',
					endWidth : 16,
					startWidth : 14
				} ],
				cap : {
					radius : 0.04
				},
				caption : {
					offset : [ 0, -25 ],
					value : 'Site speed test',
					position : 'bottom'
				},
				min : 0,
				max : 15,
				width : 270,
				height : 270,
				style : {
					stroke : '#ffffff',
					'stroke-width' : '1px',
					fill : '#ffffff'
				},
				animationDuration : 500,
				colorScheme : 'scheme04',
				labels : {
					visible : true,
					position : 'outside',
					interval : 1
				},
				ticksMinor : {
					interval : 0.5,
					size : '5%'
				},
				ticksMajor : {
					interval : 1,
					size : '10%'
				},
				border : { size: '10%', style: { stroke: '#cccccc'}, visible: true, showGradient: false },
				value : parseFloat(data.timeSpeed.floatvalue)
			});

			// New instance of stars vote, calculate diff value for stars rating
			var intTimeSpeed = parseInt(data.timeSpeed.floatvalue);
			if (intTimeSpeed > 15) {
				// Min value, at least always 1 star enabled
				var starScore = 0.5;
			} else {
				var maxSubtractValue = parseFloat(data.timeSpeed.floatvalue) / 1.5;
				var starScore = 5 - maxSubtractValue;
				if (starScore < 0.25) {
					starScore = 0.5;
				}
			}

			// Data binding for rating stars view
			$('*[data-bind="' + DOMTreeInterface.leftContainer.childs.starsContainer['data-bind'] + '"]').raty({
				path : jrealtimeBaseURI + '/administrator/components/com_jrealtimeanalytics/js/libraries/raty/images',
				size : 32,
				half : true,
				readOnly : true,
				hints : [ '', '', '', '', '' ],
				score : starScore
			});

			// Data binding for textual review comment
			$('*[data-bind="' + DOMTreeInterface.rightContainer.childs.textReport.childs.popoverContainer.childs.popoverH3Title['data-bind'] + '"]').html(COM_JREALTIME_SITESPEED_POPUP_TITLE);
			$('*[data-bind="' + DOMTreeInterface.rightContainer.childs.textReport.childs.popoverContainer.childs.popoverContentDiv['data-bind'] + '"]').html(data.review);

			// Start repositioning responsive elements and bind to window resize
			repositionResponsiveElements();
			$(window).on('resize', function() {
				repositionResponsiveElements();
			});
		}

		/**
		 * Show inject the loading waiter showed during test execution
		 * 
		 * @access public
		 * @return Void
		 */
		this.showWaiter = function(mainContainerID) {
			// Get div popover container width to center waiter
			var mainContainer = $('#' + mainContainerID);
			var containerWidth = mainContainer.width() / 2;
			$(mainContainer).prepend('<img/>').children('img').attr('src', jrealtimeBaseURI + 'administrator/components/com_jrealtimeanalytics/images/loading.gif').css({
				'position' : 'absolute',
				'margin' : '50px ' + parseInt(containerWidth - 32) + 'px',
				'width' : '64px',
				'z-index' : '99999'
			});
		}
	}

	// Make it global to instantiate from global scope
	window.JRealtimeViewSitespeed = ViewSitespeed;
})(jQuery);