<!DOCTYPE HTML>
<!--[if IEMobile 7 ]><html class="no-js iem7" manifest="default.appcache?v=1"><![endif]--> 
<!--[if lt IE 7 ]><html class="no-js ie6" <?php language_attributes(); ?>><![endif]--> 
<!--[if IE 7 ]><html class="no-js ie7" <?php language_attributes(); ?>><![endif]--> 
<!--[if IE 8 ]><html class="no-js ie8" <?php language_attributes(); ?>><![endif]--> 
<!--[if (gte IE 9)|(gt IEMobile 7)|!(IEMobile)|!(IE)]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
	<head>
		<title><?php wp_title(' |', true, 'right'); ?></title>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
	  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<script>document.documentElement.className = document.documentElement.className.replace(/(\s|^)no-js(\s|$)/, '$1js$2');</script><?php
		/*
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon.ico"/>
		*/
		?><?php wp_head();
	?> 
	</head>
<body <?php body_class(); ?>>
