<?php
/*
 * @Theme Name:WebStack
 * @Theme URI:https://www.iotheme.cn/
 * @Author: iowen
 * @Author URI: https://www.iowen.cn/
 * @Date: 2019-02-22 21:26:02
 * @LastEditors: iowen
 * @LastEditTime: 2024-07-30 17:31:38
 * @FilePath: /WebStack/templates/header-banner.php
 * @Description: 
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }  ?>
<nav class="navbar user-info-navbar" role="navigation">
    <div class="navbar-content">
      <ul class="user-info-menu list-inline list-unstyled">
        <li class="hidden-xs">
            <a href="#" data-toggle="sidebar">
                <i class="fa fa-bars"></i>
            </a>
        </li>
        <?php
        if ( has_nav_menu( 'nav_top' ) ) {
            wp_nav_menu( array(
                'theme_location' => 'nav_top',
                'menu_class'     => 'list-inline list-unstyled top-nav-menu',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'fallback_cb'    => false,
            ) );
        }
        ?>
      </ul>
      <ul class="user-info-menu list-inline list-unstyled">
        <li class="hidden-sm hidden-xs">
            <a href="https://github.com/kxnotes/WebStack" target="_blank"><i class="fa fa-github"></i> GitHub</a>
        </li>
      </ul>
    </div>
</nav>