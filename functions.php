<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
date_default_timezone_set('Asia/Shanghai');
require get_template_directory() . '/inc/inc.php';

   
//登录页面的LOGO链接为首页链接
add_filter('login_headerurl',function() {return get_bloginfo('url');});
//登陆界面logo的title为博客副标题
add_filter('login_headertext',function() {return get_bloginfo( 'description' );});

//WordPress 5.0+移除 block-library CSS
add_action( 'wp_enqueue_scripts', 'fanly_remove_block_library_css', 100 );
function fanly_remove_block_library_css() {
	wp_dequeue_style( 'wp-block-library' );
}

/**
 * 1. 注册自定义图片尺寸
 *
 * 注册一个宽度为 300px 的自定义尺寸，高度设为极大值以保持比例。
 */
add_action( 'after_setup_theme', 'custom_image_sizes_setup' );
function custom_image_sizes_setup() {
    add_image_size( 'custom-mobile-thumb', 300, 9999 ); // 名称 'custom-mobile-thumb', 宽 300, 高不限
}

/**
 * 2. 过滤 WordPress 生成的中间图片尺寸
 *
 * 根据原始图片宽度，决定只生成 'custom-mobile-thumb' 或不生成任何中间尺寸。
 */
add_filter( 'intermediate_image_sizes_advanced', 'filter_intermediate_image_sizes', 10, 2 );
function filter_intermediate_image_sizes( $sizes, $metadata ) {

    // 获取图像的宽度 (通常是上传后可能被WordPress缩放过的'scaled'版本宽度)
    $original_width = isset( $metadata['width'] ) ? $metadata['width'] : 0;

    // 创建一个空数组，用来存放我们最终要生成的尺寸
    $sizes_to_generate = array();

    // 条件判断：只有原图宽度大于 1200px 时
    if ( $original_width > 1200 ) {
        // 检查我们注册的 'custom-mobile-thumb' 是否在待处理列表里
        if ( isset( $sizes['custom-mobile-thumb'] ) ) {
            // 如果在，把它加入到我们要生成的列表里
            $sizes_to_generate['custom-mobile-thumb'] = $sizes['custom-mobile-thumb'];
        }
    }
    // 对于宽度 <= 1200px 的图片，或者宽度 > 1200px 但 'custom-mobile-thumb' 因故不存在的情况，
    // $sizes_to_generate 数组将是空的。

    // 返回我们处理过的尺寸列表，WordPress 将只生成这个列表中的尺寸。
    // 如果返回空数组，则不生成任何中间尺寸。
    return $sizes_to_generate;
}

/**
 * 3. 为 <html> 标签添加 Open Graph prefix 属性
 */
add_filter( 'language_attributes', 'add_opengraph_prefix' );
function add_opengraph_prefix( $output ) {
    // 检查是否已经在输出中（虽然不太可能）
    if (strpos($output, 'prefix=') === false) {
        $output .= ' prefix="og: https://ogp.me/ns#"';
    }
    return $output;
}
