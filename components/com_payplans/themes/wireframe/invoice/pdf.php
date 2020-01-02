<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>PDF Invoice</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<style type="text/css">
html {
	font-size: 100%;
	-webkit-text-size-adjust: 100%;
	-ms-text-size-adjust: 100%;
	font-family: sans-serif;
}
body {
	font-size: 16px;
	/*line-height: 1.2;*/
	color: #444;
	padding: 1em;
	font-family: <?php echo $this->config->get('pdf_font'); ?>; 
}
html, body {
	margin: 0;
	padding: 0;
	height: 100%;
}
a {
	text-decoration: none;
	color: #007bff;
}
#pp {
	font-size: 1em;
	/*line-height: 1.5;*/
}
#pp *,
#pp *:before,
#pp *:after {
	-webkit-box-sizing: border-box;
	box-sizing: border-box; 
	margin: 0;
	padding: 0;
}

#pp .o-avatar {
	display: inline-block;
	border-radius: 2px;
	vertical-align: middle;
	overflow: hidden;
	-webkit-border-radius: 2px;
	padding: 0;
	margin: 0px;
	width: 3.25em;
	height: 3.25em;
	line-height: 3.25em;
	font-size: 1em; }
	#pp .o-avatar img {
		display: block;
		max-width: none;
		width: 100%;
		height: 100%;
		image-rendering: optimizeQuality;
		border-radius: 2px; }
#pp .o-avatar--lg {
	width: 4.8em;
	height: 4.8em;
	line-height: 4.8em; }
#pp .o-avatar-rounded {
	border-radius: 50%; }
		#pp .o-avatar-rounded img {
			border-radius: 50%; }
	
#pp .pp-checkout-container {
	display: -webkit-box;
	display: -ms-flexbox;
	display: flex;
	-webkit-box-orient: vertical;
	-webkit-box-direction: normal;
		-ms-flex-flow: column;
			flex-flow: column;
	height: 100%;
	margin: 0 auto;
	position: relative;
	background-color: #fff;
	overflow: hidden;
	overflow-y: auto; }
	#pp .pp-checkout-container__form {
		width: 100%;
		height: 100%; }
	#pp .pp-checkout-container__hd {
		text-align: center;
		padding: 1.2em;
		background-color: #f5f5f5; }
	#pp .pp-checkout-container__title {
		font-size: 1.65em;
		margin-top: 1.8em;
		margin-bottom: 1.8em; }

	#pp .pp-invoice-container {
		position: relative;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		margin: 1.2em auto;
		-webkit-box-orient: vertical;
		-webkit-box-direction: normal;
			-ms-flex-direction: column;
				flex-direction: column;
		min-width: 0;
		word-wrap: break-word;
		background-color: #fff;
		background-clip: border-box;
		border-radius: 0.2em;
		max-width: 600px; }
		#pp .pp-invoice-container__hd, #pp .pp-invoice-container__bd, #pp .pp-invoice-container__ft {
			-webkit-box-flex: 1;
				-ms-flex: 1 1 auto;
					flex: 1 1 auto;
			padding: 1.2em; }
	#pp .pp-invoice-menu__hd {
		font-size: 0.8em;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		margin-bottom: 1.2em; }
	#pp .pp-invoice-menu__hd-id {
		font-weight: bold; }
	#pp .pp-invoice-menu__hd-right {
		margin-left: auto;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		 }
	#pp .pp-invoice-table {
		width: 100%; }
		#pp .pp-invoice-table th {
			text-align: left;
			vertical-align: top; }
		#pp .pp-invoice-table th, #pp .pp-invoice-table td {
			vertical-align: top;
			padding: 1.2em 0;
			padding-right: 0.6em; }
		#pp .pp-invoice-table > tbody > tr > th,
		#pp .pp-invoice-table > tbody > tr > td {
			border-top: 1px solid #e1e1e1; }
	#pp .pp-invoice-logo {
		float: right;
	}
	#pp .pp-invoice-logo img {
		max-width: 200px;
		max-height: 80px;
	}

	#pp .o-card {
		position: relative;
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-orient: vertical;
		-webkit-box-direction: normal;
			-ms-flex-direction: column;
				flex-direction: column;
		min-width: 0;
		word-wrap: break-word;
		background-color: #fff;
		background-clip: border-box;
		border: 1px solid #e1e1e1;
		border-radius: 0.2em; }
		#pp .o-card > hr {
			margin-right: 0;
			margin-left: 0; }
	#pp .o-card__body {
		-webkit-box-flex: 1;
			-ms-flex: 1 1 auto;
				flex: 1 1 auto;
		padding: 1.2em; }
	#pp .o-card__title {
		font-weight: bold;
		font-size: 1.25em;
		margin-bottom: 0.6em;
		line-height: 1; }
	#pp .o-card__subtitle {
		margin-bottom: 0.6em; }
	#pp .o-card__desc {
		font-size: 1em;
		margin-bottom: 0.6em; }
	#pp .o-card__text:last-child {
		margin-bottom: 0; }
	#pp .o-card-link + .o-card-link {
		margin-left: 1.2em; }
	#pp .o-card__header {
		padding: 0.6em 1.2em;
		margin-bottom: 0;
		background-color: #f5f5f5;
		border-bottom: 1px solid #e1e1e1;
		font-weight: bold; }
		#pp .o-card__header + .list-group .list-group-item:first-child {
			border-top: 0; }
	#pp .o-card__footer {
		padding: 0.6em 1.2em;
		background-color: #f5f5f5;
		border-top: 1px solid #e1e1e1; }
		#pp .o-card__footer:last-child {
			border-radius: 0.2em; }
	#pp .o-card--shadow {
		-webkit-box-shadow: 0 0.2em 1.2em rgba(0, 0, 0, 0.15);
				box-shadow: 0 0.2em 1.2em rgba(0, 0, 0, 0.15); }
	
	#pp .o-label {
	  display: inline-block;
	  padding: .2em .6em .2em;
	  font-size: 1em;
	  font-weight: bold;
	  line-height: 1;
	  color: #fff;
	  text-align: center;
	  white-space: nowrap;
	  vertical-align: middle;
	  border-radius: .25em; }
	  #pp .o-label + .o-label {
		margin-left: 0.15em; }
	  #pp .o-label:empty {
		display: none; }
	  .btn #pp .o-label {
		position: relative;
		top: -1px; }
	  #pp .o-label--lg {
		padding: .25em .75em .25em;
		font-size: 110%;
		border-radius: .75em; }
	#pp .o-label--default,
	#pp .o-label--inverse {
	  background-color: #f5f5f5 !important; }
	  #pp .o-label--default[href]:hover, #pp .o-label--default[href]:focus,
	  #pp .o-label--inverse[href]:hover,
	  #pp .o-label--inverse[href]:focus {
		background-color: gainsboro !important; }
	#pp .o-label--primary {
	  background-color: #5EA4F2 !important; }
	  #pp .o-label--primary[href]:hover, #pp .o-label--primary[href]:focus {
		background-color: #2f89ee !important; }
	#pp .o-label--success {
	  background-color: #54C063 !important; }
	  #pp .o-label--success[href]:hover, #pp .o-label--success[href]:focus {
		background-color: #3da44b !important; }
	#pp .o-label--info {
	  background-color: #5BC0DE !important; }
	  #pp .o-label--info[href]:hover, #pp .o-label--info[href]:focus {
		background-color: #31b0d5 !important; }
	#pp .o-label--warning {
	  background-color: #EC971F !important; }
	  #pp .o-label--warning[href]:hover, #pp .o-label--warning[href]:focus {
		background-color: #c77c11 !important; }
	#pp .o-label--danger,
	#pp .o-label--important {
	  background-color: #d9534f !important; }
	  #pp .o-label--danger[href]:hover, #pp .o-label--danger[href]:focus,
	  #pp .o-label--important[href]:hover,
	  #pp .o-label--important[href]:focus {
		background-color: #c9302c !important; }
	#pp .o-label--default-o {
	  background-color: white !important;
	  color: #f5f5f5 !important;
	  border: 1px solid #f5f5f5 !important; }
	  #pp .o-label--default-o[href]:hover, #pp .o-label--default-o[href]:focus {
		background-color: white !important; }
	#pp .o-label--clean-o {
	  background-color: #fff;
	  color: #888;
	  border: 1px solid #888; }
	#pp .o-label--primary-o {
	  background-color: white !important;
	  color: #5EA4F2 !important;
	  border: 1px solid #5EA4F2 !important; }
	  #pp .o-label--primary-o[href]:hover, #pp .o-label--primary-o[href]:focus {
		background-color: white !important; }
	#pp .o-label--success-o {
	  background-color: #f1faf2 !important;
	  color: #54C063 !important;
	  border: 1px solid #54C063 !important; }
	  #pp .o-label--success-o[href]:hover, #pp .o-label--success-o[href]:focus {
		background-color: #f1faf2 !important; }
	#pp .o-label--info-o {
	  background-color: white !important;
	  color: #5BC0DE !important;
	  border: 1px solid #5BC0DE !important; }
	  #pp .o-label--info-o[href]:hover, #pp .o-label--info-o[href]:focus {
		background-color: white !important; }
	#pp .o-label--warning-o {
	  background-color: #fdf3e4 !important;
	  color: #EC971F !important;
	  border: 1px solid #EC971F !important; }
	  #pp .o-label--warning-o[href]:hover, #pp .o-label--warning-o[href]:focus {
		background-color: #fdf3e4 !important; }
	#pp .o-label--danger-o {
	  background-color: white !important;
	  color: #d9534f !important;
	  border: 1px solid #d9534f !important; }
	  #pp .o-label--danger-o[href]:hover, #pp .o-label--danger-o[href]:focus {
		background-color: white !important; }
		
	#pp .t-text--primary {
		color: #5EA4F2 !important; }
	#pp a.t-text--primary:hover {
		color: #2f89ee !important; }
	#pp .t-text--success {
		color: #4FC25F !important; }
	#pp a.t-text--success:hover {
		color: #39a548 !important; }
	#pp .t-text--info {
		color: #31708f !important; }
	#pp a.t-text--info:hover {
		color: #245269 !important; }
	#pp .t-text--warning {
		color: #8a6d3b !important; }
	#pp a.t-text--warning:hover {
		color: #66512c !important; }
	#pp .t-text--danger {
		color: #FC595B !important; }
	#pp a.t-text--danger:hover {
		color: #fb272a !important; }

	#pp .t-lg-text--right,
	#pp .t-text--right {
		text-align: right !important; }
	#pp .t-va--top {
		vertical-align: top !important; }
	#pp .t-va--middle {
		vertical-align: middle !important; }
	#pp .t-va--bottom {
		vertical-align: bottom !important; }

	


</style>
</head>
<body>
	<div id="pp">
		<?php echo $contents; ?>
	</div>
</body>
</html>