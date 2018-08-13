<?php
extract(shortcode_atts(array(
	'background_id'	=> 0,
	'title'	=> 'Coming soon - Sitename',
	'description'	=> 'Tagline or description',
), (array)get_option('offline_box_display')));

?><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title><?php echo $title;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<style type="text/css">
		/*<![CDATA[*/
			.clearfix::after{
				display: block;
				content: '';
				clear: both;
				height: 0px;
			}
			body {
				background: #fff<?php echo ($background_id>0?" url('".wp_get_attachment_image_url($background_id,'full')."') center center no-repeat; background-size: 100% auto; background-size: cover; ":'');?>;
				color: #000;
				font-size: 0.9em;
				font-family: sans-serif,helvetica;
				margin: 0;
				padding: 0;
				font-size: 14px;
			}
			a{
				text-decoration: none;
				color: #fff;
			}
			h1 {
				margin: 0;
				padding: 0 0 50px;
				color: #f00;
				font-weight: normal;
				font-size: 20px;
			}
			h1 strong {
				font-weight: bold;
			}
			.offline-box{
				text-align: center;
				padding: 20px;
				width: 360px;
				max-height: 100px;
				position: absolute;
				background: #fff ;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				margin: auto;
				-webkit-border-radius: 5px;
				border-radius: 5px;
				-webkit-box-shadow: 0px 1px 5px #999;
				box-shadow: 0px 1px 5px #999;
			}
			.blogauthor{
				position: absolute;
				bottom: 20px;
				left: 0;
				width: 100%;
				text-align: center;
			}
			.hide{
				display: none;
			}
			
			/*]]>*/
		</style>
	</head>
	<body>
		<div class="offline-box clearfix">
			<h1><strong><?php echo $title;?></strong></h1>
			<div class="blogdescription"><?php echo $description;?></div>
		</div>
		<div class="blogauthor"><a href="http://photoboxone.com/" target="_blank" title="Photo Box plugin">PB One</a></div>
	</body>
</html>