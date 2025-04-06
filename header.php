<?php
/*
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2021-02-21 21:26:02
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-30 19:49:22
 * @FilePath: /WebStack/header.php
 * @Description: 
 */ 
if ( ! defined( 'ABSPATH' ) ) { exit; } 
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

<!-- Preload Core CSS -->
<link rel="preload" href="<?php echo get_theme_file_uri('css/nav.css'); ?>" as="style">
<link rel="preload" href="<?php echo get_theme_file_uri('css/font-awesome.min.css'); ?>" as="style">

<!-- Preload Core Font -->
<link rel="preload" href="<?php echo get_theme_file_uri('fonts/fontawesome-webfont.woff2?v=4.7.0'); ?>" as="font" type="font/woff2" crossorigin>

<meta name="theme-color" content="#2C2E2F" />

<?php wp_head(); ?>
</head> 
 <body <?php body_class('page-body '.io_get_option('theme_mode')) ?>>
    <div class="page-container">
      