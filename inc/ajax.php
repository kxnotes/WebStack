<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

//图片上传
add_action('wp_ajax_nopriv_img_upload', 'io_img_upload');  
add_action('wp_ajax_img_upload', 'io_img_upload');
function io_img_upload(){  
	$extArr = array("jpg", "png", "jpeg");
	$file = $_FILES['files'];
	if ( !empty( $file ) ) {
	    $wp_upload_dir = wp_upload_dir();                                     // 获取上传目录信息
	    $basename = $file['name'];
	    $baseext = pathinfo($basename, PATHINFO_EXTENSION);
	    $dataname = date("YmdHis_").substr(md5(time()), 0, 8) . '.' . $baseext;
	    $filename = $wp_upload_dir['path'] . '/' . $dataname;
	    rename( $file['tmp_name'], $filename );                               // 将上传的图片文件移动到上传目录
	    $attachment = array(
	        'guid'           => $wp_upload_dir['url'] . '/' . $dataname,      // 外部链接的 url
	        'post_mime_type' => $file['type'],                                // 文件 mime 类型
	        'post_title'     => preg_replace( '/\.[^.]+$/', '', $basename ),  // 附件标题，采用去除扩展名之后的文件名
	        'post_content'   => '',                                           // 文章内容，留空
	        'post_status'    => 'inherit'
	    );
	    $attach_id = wp_insert_attachment( $attachment, $filename );          // 插入附件信息
	    if($attach_id != 0){
	        require_once( ABSPATH . 'wp-admin/includes/image.php' );          // 确保包含此文件，因为wp_generate_attachment_metadata（）依赖于此文件。
	        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	        wp_update_attachment_metadata( $attach_id, $attach_data );        // 生成附件的元数据，并更新数据库记录。
	        print_r(json_encode(array('status'=>1,'msg'=>__('图片添加成功','i_theme'),'data'=>array('id'=>$attach_id,'src'=>wp_get_attachment_url( $attach_id ),'title'=>$basename))));
	        exit();
	    }else{
	        echo '{"status":4,"msg":"'.__('图片上传失败！','i_theme').'"}';
	        exit();
	    }
	} 
}

//删除图片
add_action('wp_ajax_nopriv_img_remove', 'io_img_remove');  
add_action('wp_ajax_img_remove', 'io_img_remove');
function io_img_remove(){    
	$attach_id = $_POST["id"];
	if( empty($attach_id) ){
		echo '{"status":3,"msg":"'.__('没有上传图像！','i_theme').'"}';
		exit;
	}
	if ( false === wp_delete_attachment( $attach_id ) )
		echo '{"status":4,"msg":"'.sprintf(__('图片 %s 删除失败！','i_theme'), $attach_id).'"}';
	else
		echo '{"status":1,"msg":"'.__('删除成功！','i_theme').'"}';
	exit; 
}

//提交文章
add_action('wp_ajax_nopriv_contribute_post', 'io_contribute');  
add_action('wp_ajax_contribute_post', 'io_contribute');
function io_contribute(){  
	$delay = 40; 
	if( isset($_COOKIE["tougao"]) && ( time() - $_COOKIE["tougao"] ) < $delay ){
		error('{"status":2,"msg":"'.sprintf(__('您投稿也太勤快了吧，%s秒后再试！','i_theme'), ($delay - ( time() - $_COOKIE["tougao"] )) ).'"}');
	} 
	
	//表单变量初始化
	$sites_link = isset( $_POST['tougao_sites_link'] ) ? trim(htmlspecialchars($_POST['tougao_sites_link'], ENT_QUOTES)) : '';
	$sites_sescribe = isset( $_POST['tougao_sites_sescribe'] ) ? trim(htmlspecialchars($_POST['tougao_sites_sescribe'], ENT_QUOTES)) : '';
	$title = isset( $_POST['tougao_title'] ) ? trim(htmlspecialchars($_POST['tougao_title'], ENT_QUOTES)) : '';
	$category = isset( $_POST['tougao_cat'] ) ? $_POST['tougao_cat'] : '0';
	$sites_ico = isset( $_POST['tougao_sites_ico'] ) ? trim(htmlspecialchars($_POST['tougao_sites_ico'], ENT_QUOTES)) : '';
	$wechat_qr = isset( $_POST['tougao_wechat_qr'] ) ? trim(htmlspecialchars($_POST['tougao_wechat_qr'], ENT_QUOTES)) : '';
	$content = isset( $_POST['tougao_content'] ) ? trim(htmlspecialchars($_POST['tougao_content'], ENT_QUOTES)) : '';
	
	// 表单项数据验证
	if ( $category == "0" ){
		error('{"status":4,"msg":"'.__('请选择分类。','i_theme').'"}');
	}
	if ( !empty(get_term_children($category, 'favorites'))){
		error('{"status":4,"msg":"'.__('不能选用父级分类目录。','i_theme').'"}');
	}
	if ( empty($sites_sescribe) || mb_strlen($sites_sescribe) > 50 ) {
		error('{"status":4,"msg":"'.__('网站描叙必须填写，且长度不得超过50字。','i_theme').'"}');
	}
	if ( empty($sites_link) && empty($wechat_qr) ){
		error('{"status":3,"msg":"'.__('网站链接和公众号二维码至少填一项。','i_theme').'"}');
	}
	elseif ( !empty($sites_link) && !preg_match('/http(s)?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $sites_link)) {
		error('{"status":4,"msg":"'.__('网站链接必须符合URL格式。','i_theme').'"}');
	}
	if ( empty($title) || mb_strlen($title) > 30 ) {
		error('{"status":4,"msg":"'.__('网站名称必须填写，且长度不得超过30字。','i_theme').'"}');
	}
	//if ( empty($content) || mb_strlen($content) > 10000 || mb_strlen($content) < 6) {
	//	error('{"status":4,"msg":"内容必须填写，且长度不得超过10000字，不得少于6字。"}');
	//}
	
	$tougao = array(
		'comment_status'   => 'closed',
		'ping_status'      => 'closed',
		//'post_author'      => 1,//用于投稿的用户ID
		'post_title'       => $title,
		'post_content'     => $content,
		'post_status'      => 'pending',
		'post_type'        => 'sites',
		//'tax_input'        => array( 'favorites' => array($category) ) //游客不可用
	);
	
	// 将文章插入数据库
	$status = wp_insert_post( $tougao );
	if ($status != 0){
		global $wpdb;
		add_post_meta($status, '_sites_sescribe', $sites_sescribe);
		add_post_meta($status, '_sites_link', $sites_link);
		add_post_meta($status, '_sites_order', '0');
		if( !empty($sites_ico))
			add_post_meta($status, '_thumbnail', $sites_ico); 
		if( !empty($wechat_qr))
			add_post_meta($status, '_wechat_qr', $wechat_qr); 
		wp_set_post_terms( $status, array($category), 'favorites'); //设置文章分类
		setcookie("tougao", time(), time()+$delay+10);
		error('{"status":1,"msg":"'.__('投稿成功！','i_theme').'"}');
	}else{
		error('{"status":4,"msg":"'.__('投稿失败！','i_theme').'"}');
	}
}
function error($ErrMsg) {
	echo $ErrMsg;
	exit;
} 

// --- 新增：获取 Favicon 的 AJAX 处理 ---
add_action('wp_ajax_fetch_site_favicon', 'io_ajax_get_favicon');

function io_ajax_get_favicon() {
    // 1. 安全校验 (Nonce 和权限)
    check_ajax_referer('fetch_favicon_nonce', 'nonce');
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $site_url = isset($_POST['site_url']) ? esc_url_raw(trim($_POST['site_url'])) : '';

    if (!$post_id || !current_user_can('edit_post', $post_id) || empty($site_url)) {
        wp_send_json_error(['message' => __('无效请求或权限不足。','i_theme')]);
        return; // 或 wp_die();
    }

    // 2. 调用核心函数获取 Favicon
    $result = io_get_favicon($site_url, $post_id);

    // 3. 处理结果并返回 JSON
    if (is_wp_error($result)) {
        // 如果核心函数返回错误
        wp_send_json_error(['message' => $result->get_error_message()]);
    } elseif ($result && is_string($result)) {
        // 核心函数成功返回了图标 URL
        $final_url = get_post_meta($post_id, '_final_url', true); // 获取保存的最终 URL
        wp_send_json_success([
            'message' => __('Favicon 获取成功!','i_theme'),
            'favicon_url' => $result, // 返回获取到的图标 URL
            'final_url' => $final_url // 可以把最终URL也返回给前端
        ]);
    } else {
        // 其他未预料的失败情况
        wp_send_json_error(['message' => __('获取 Favicon 时发生未知错误。','i_theme')]);
    }
     wp_die(); // 必须调用 wp_die() 来终止 AJAX 处理
}

/**
 * 核心函数：获取并处理网站 Favicon (增强版)
 *
 * @param string $url 初始 URL
 * @param int $post_id 关联的文章 ID
 * @return string|WP_Error 成功时返回 Favicon 的 URL，失败时返回 WP_Error 对象
 */
function io_get_favicon($url, $post_id) {
    // --- 0. 准备工作 ---
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $default_ico = get_theme_file_uri('/images/favicon.png');
    $final_url = '';
    $icon_url = false;
    $html_content = null; // Store HTML content to avoid multiple downloads
    $debug_info = []; // 添加调试信息收集
    
    // 检测系统代理
    $proxy_config = io_detect_system_proxy();
    if ($proxy_config) {
        $debug_info[] = "检测到系统代理: " . $proxy_config['type'] . "://" . $proxy_config['host'] . ":" . $proxy_config['port'];
    } else {
        $debug_info[] = "未检测到系统代理，使用直连";
    }

    // --- 1. URL 预处理 ---
    if (strpos($url, '://') === false) {
        $url = 'http://' . $url;
    }
    $debug_info[] = "初始 URL: " . $url;

    // --- 2. 使用 cURL 处理重定向并获取最终 URL ---
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // 增加最大重定向次数
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 增加超时时间
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36');
    curl_setopt($ch, CURLOPT_HEADER, false);
    // 添加更多选项来处理复杂重定向
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
        'Cache-Control: no-cache'
    ]);
    
    // 如果检测到代理，配置 cURL 使用代理
    if ($proxy_config) {
        io_configure_curl_proxy($ch, $proxy_config);
        $debug_info[] = "已配置 cURL 使用代理";
    }

    $content_or_header = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $redirect_count = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    $debug_info[] = "HTTP 状态码: " . $http_code;
    $debug_info[] = "重定向次数: " . $redirect_count;
    $debug_info[] = "最终 URL: " . $effective_url;
    if ($curl_errno) {
        $debug_info[] = "cURL 错误: " . $curl_error;
    }
    
    // 特殊处理：如果重定向成功但目标网站无法访问
    if ($curl_errno && $redirect_count > 0 && !empty($effective_url)) {
        $debug_info[] = "检测到重定向成功但目标网站无法访问，尝试使用重定向前的域名";
        
        // 尝试使用原始域名的favicon
        $original_parsed = parse_url($url);
        if ($original_parsed && isset($original_parsed['host'])) {
            $original_base = $original_parsed['scheme'] . '://' . $original_parsed['host'];
            $debug_info[] = "尝试原始域名: " . $original_base;
            
            $favicon_paths = ['/favicon.ico', '/favicon.png'];
            foreach ($favicon_paths as $path) {
                $try_url = $original_base . $path;
                $debug_info[] = "尝试原始域名 favicon: " . $try_url;
                $icon_data = io_download_icon_data_with_proxy($try_url, $proxy_config);
                if ($icon_data) {
                    $debug_info[] = "从原始域名成功获取 favicon";
                    $filename = basename($path);
                    $icon_url = io_save_icon_to_media_library($icon_data, $url, $filename);
                    if ($icon_url && !is_wp_error($icon_url)) {
                        update_post_meta($post_id, '_thumbnail', esc_url_raw($icon_url));
                        update_post_meta($post_id, '_final_url', esc_url_raw($effective_url)); // 仍记录重定向URL
                        update_post_meta($post_id, '_favicon_debug', implode('; ', $debug_info));
                        return $icon_url;
                    }
                }
            }
        }
        
        // 如果原始域名也失败，返回错误但包含重定向信息
        return new WP_Error('redirect_target_unreachable', 
            sprintf(__('重定向成功到 %s，但目标网站无法访问。原始网站也无可用图标。', 'i_theme'), $effective_url) . 
            ' Debug: ' . implode('; ', $debug_info)
        );
    }

    if ($curl_errno) {
        return new WP_Error('curl_error', sprintf(__('网络请求失败 (cURL Error: %s)。', 'i_theme'), $curl_error) . ' Debug: ' . implode('; ', $debug_info));
    }

    // Allow redirect codes, but fail on 4xx/5xx for the *final* URL
    if ($http_code >= 400) {
         // Retry getting content if the first request was just HEAD
         // If we already got content, this error is final.
         if ($content_or_header === '' || $content_or_header === false) {
              // Re-request without NOBODY if the first check failed
              $debug_info[] = "尝试重新请求";
              $ch = curl_init();
              // Set options again, but without NOBODY and HEADER=false
              curl_setopt($ch, CURLOPT_URL, $effective_url ?: $url); // Use effective_url if available
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Should land on final URL now
              curl_setopt($ch, CURLOPT_MAXREDIRS, 0);       // Don't redirect further
              curl_setopt($ch, CURLOPT_TIMEOUT, 20);
              curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
              curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36');

              $content_or_header = curl_exec($ch);
              $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Update http_code
              $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $effective_url; // Update effective_url
              curl_close($ch);
              
              $debug_info[] = "重新请求后 HTTP 状态码: " . $http_code;
              $debug_info[] = "重新请求后最终 URL: " . $effective_url;

              if ($http_code >= 400) {
                   return new WP_Error('http_error_final', sprintf(__('最终网址返回错误状态码: %d。', 'i_theme'), $http_code) . ' Debug: ' . implode('; ', $debug_info));
              }
         } else {
              // We had content from the first request, but it was an error page.
               return new WP_Error('http_error_initial', sprintf(__('网址返回错误状态码: %d。', 'i_theme'), $http_code) . ' Debug: ' . implode('; ', $debug_info));
         }
    }
     $html_content = $content_or_header; // Store the fetched HTML

    if (empty($effective_url)) {
        return new WP_Error('no_effective_url', __('无法确定最终有效网址。', 'i_theme') . ' Debug: ' . implode('; ', $debug_info));
    }
    $final_url = $effective_url;
    
    // 添加调试信息到 post meta
    update_post_meta($post_id, '_favicon_debug', implode('; ', $debug_info));
    update_post_meta($post_id, '_final_url', esc_url_raw($final_url));

    $parsed_url = parse_url($final_url);
    if (!$parsed_url || !isset($parsed_url['scheme']) || !isset($parsed_url['host'])) {
        return new WP_Error('invalid_final_url', __('最终网址格式无效。', 'i_theme') . ' Debug: ' . implode('; ', $debug_info));
    }
    $base_url = rtrim($parsed_url['scheme'] . '://' . $parsed_url['host'], '/'); // Ensure no trailing slash
    $debug_info[] = "基础 URL: " . $base_url;

    // --- 3. 尝试获取 Favicon (按优先级) ---

    // 定义要尝试的 Favicon 路径列表
    $favicon_paths = [
        '/favicon.ico',
        '/favicon.png',
        '/apple-touch-icon.png',
        '/apple-touch-icon-precomposed.png',
        // '/assets/favicon.ico', // Less common, add if needed
        // '/assets/favicon.png',
        // '/images/favicon.ico',
        // '/images/favicon.png',
    ];

    // 策略 1: 尝试根目录下的常见 Favicon 文件
    foreach ($favicon_paths as $path) {
        $try_url = $base_url . $path;
        $debug_info[] = "尝试 favicon 路径: " . $try_url;
        $icon_data = io_download_icon_data_with_proxy($try_url, $proxy_config);
        if ($icon_data) {
            $debug_info[] = "成功获取 favicon 数据: " . strlen($icon_data) . " bytes";
            $filename = basename($path);
            $icon_url = io_save_icon_to_media_library($icon_data, $final_url, $filename);
            if ($icon_url && !is_wp_error($icon_url)) {
                $debug_info[] = "成功保存到媒体库: " . $icon_url;
                update_post_meta($post_id, '_thumbnail', esc_url_raw($icon_url));
                update_post_meta($post_id, '_favicon_debug', implode('; ', $debug_info)); // 更新调试信息
                return $icon_url; // 成功
            } else {
                $debug_info[] = "保存到媒体库失败: " . (is_wp_error($icon_url) ? $icon_url->get_error_message() : '未知错误');
            }
        } else {
            $debug_info[] = "获取 favicon 数据失败";
        }
    }

    // 策略 2: 解析 HTML <link> 标签 (需要 HTML 内容)
    if (!$html_content) { // Only download if not already fetched
         $html_content = io_get_url_content($final_url); // Use helper function for safety
    }
    if ($html_content && !is_wp_error($html_content)) {
        $link_tags = io_parse_html_for_favicons($html_content); // Reuse existing function
        if (!empty($link_tags)) {
            // 尝试解析到的 <link> 标签 (可以添加优先级排序)
            foreach ($link_tags as $tag_url) {
                $absolute_tag_url = io_make_absolute_url($tag_url, $base_url, $final_url); // Reuse existing function
                if ($absolute_tag_url) {
                    $icon_data = io_download_icon_data_with_proxy($absolute_tag_url, $proxy_config);
                    if ($icon_data) {
                        $filename = basename(parse_url($absolute_tag_url, PHP_URL_PATH)) ?: 'favicon_from_link.png';
                        $icon_url = io_save_icon_to_media_library($icon_data, $final_url, $filename);
                        if ($icon_url && !is_wp_error($icon_url)) {
                            update_post_meta($post_id, '_thumbnail', esc_url_raw($icon_url));
                            return $icon_url; // 成功
                        }
                    }
                }
            }
        }
    }

    // 策略 3: 解析 HTML <meta property="og:image"> (Logo Fallback)
    if ($html_content && !is_wp_error($html_content)) {
        $og_image_url = io_parse_html_for_og_image($html_content);
        if ($og_image_url) {
            $absolute_og_url = io_make_absolute_url($og_image_url, $base_url, $final_url);
            if ($absolute_og_url) {
                $icon_data = io_download_icon_data_with_proxy($absolute_og_url, $proxy_config);
                if ($icon_data) {
                    $filename = basename(parse_url($absolute_og_url, PHP_URL_PATH)) ?: 'og_image_logo.png';
                    $icon_url = io_save_icon_to_media_library($icon_data, $final_url, $filename);
                    if ($icon_url && !is_wp_error($icon_url)) {
                        update_post_meta($post_id, '_thumbnail', esc_url_raw($icon_url));
                        return $icon_url; // 成功 (Logo Fallback)
                    }
                }
            }
        }
    }
    
    // 策略 4: 尝试根目录下的常见 Logo 文件 (Logo Fallback)
    $logo_paths = [
        '/logo.png',
        // '/images/logo.png', // Add more if needed
        // '/assets/logo.png',
    ];
    foreach ($logo_paths as $path) {
        $try_url = $base_url . $path;
        $icon_data = io_download_icon_data_with_proxy($try_url, $proxy_config);
        if ($icon_data) {
            $filename = basename($path);
            $icon_url = io_save_icon_to_media_library($icon_data, $final_url, $filename);
            if ($icon_url && !is_wp_error($icon_url)) {
                update_post_meta($post_id, '_thumbnail', esc_url_raw($icon_url));
                return $icon_url; // 成功 (Logo Fallback)
            }
        }
    }


    // --- 5. 获取彻底失败 ---
    // 如果所有策略都失败了
    $debug_info[] = "所有策略都失败";
    update_post_meta($post_id, '_favicon_debug', implode('; ', $debug_info)); // 保存最终调试信息
    // update_post_meta($post_id, '_thumbnail', $default_ico); // Optionally set to default on failure
    return new WP_Error('fetch_failed', __('未能通过任何策略找到或获取有效的图标/Logo。', 'i_theme') . ' Debug: ' . implode('; ', $debug_info));
}

/**
 * 辅助函数：解析 HTML 查找 Open Graph Image <meta> 标签
 */
function io_parse_html_for_og_image($html) {
    if (empty($html)) return false;
    if (preg_match('/<meta[^>]+property=[\'"]og:image[\'"][^>]+content=[\'"]([^\'"]+)[\'"]/i', $html, $matches)) {
        return trim($matches[1]);
    }
    return false;
}

/**
 * 辅助函数：下载 URL 内容
 */
function io_get_url_content($url) {
    $response = wp_remote_get($url, [
        'timeout' => 15,
        'redirection' => 0, // 因为我们已经处理了重定向
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36'
    ]);
    if (is_wp_error($response)) {
        return $response;
    }
    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code >= 400) {
        return new WP_Error('http_error_content', sprintf(__('请求内容时服务器返回错误 %d', 'i_theme'), $http_code));
    }
    return wp_remote_retrieve_body($response);
}

/**
 * 辅助函数：解析 HTML 查找 Favicon <link> 标签
 */
function io_parse_html_for_favicons($html) {
    $icons = [];
    if (empty($html)) return $icons;

    libxml_use_internal_errors(true); // 抑制解析错误
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    libxml_clear_errors();

    $links = $dom->getElementsByTagName('link');
    foreach ($links as $link) {
        $rel = strtolower($link->getAttribute('rel'));
        if (strpos($rel, 'icon') !== false || strpos($rel, 'apple-touch-icon') !== false) {
            $href = $link->getAttribute('href');
            if (!empty($href)) {
                $icons[] = $href;
            }
        }
    }
    return $icons;
}

/**
 * 辅助函数：下载图标数据
 */
function io_download_icon_data($icon_url) {
    $response = wp_remote_get($icon_url, [
        'timeout' => 15,
        'redirection' => 5, // 允许图标地址本身再重定向几次
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36'
    ]);

    if (is_wp_error($response)) {
        return false; // 下载失败
    }

    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code >= 400) {
        return false; // HTTP 错误
    }

    $content_type = wp_remote_retrieve_header($response, 'content-type');
    if (strpos($content_type, 'image') === false) {
         // 检查是否是 ICO 文件（Content-Type 可能不规范）
        $path_info = pathinfo(parse_url($icon_url, PHP_URL_PATH));
        if (!isset($path_info['extension']) || strtolower($path_info['extension']) !== 'ico'){
             return false; // 不是图片类型，也不是 .ico 文件
        }
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        return false; // 内容为空
    }

    return $body;
}

/**
 * 辅助函数：将图标数据保存到媒体库 (尝试转换为 PNG)
 */
function io_save_icon_to_media_library($icon_data, $source_url, $filename) {
    $upload_dir = wp_upload_dir();
    // 清理原始文件名，保留原始扩展名
    $filename = sanitize_file_name($filename); 
    
    // --- 保存文件到媒体库 ---
    // 使用原始（已清理的）文件名创建唯一文件名
    $unique_filename = wp_unique_filename($upload_dir['path'], $filename);
    $filepath = $upload_dir['path'] . '/' . $unique_filename;

    // 将原始图标数据写入文件
    $saved = file_put_contents($filepath, $icon_data);
    if ($saved === false) {
        return new WP_Error('file_save_error', __('无法保存临时图标文件。', 'i_theme'));
    }

    // 准备附件数据
    // 根据最终保存的文件名（含扩展名）自动检测文件类型
    $filetype = wp_check_filetype($unique_filename, null); 
    $mime_type = $filetype['type']; // 直接使用检测到的 MIME 类型

    // 使用通用描述
    $attachment_title = preg_replace('/\\.[^.]+$/', '', $unique_filename);
    $attachment_content = sprintf(__('Favicon for %s', 'i_theme'), esc_url($source_url)); 

    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . $unique_filename,
        'post_mime_type' => $mime_type, // 使用检测到的 MIME 类型
        'post_title'     => $attachment_title,
        'post_content'   => $attachment_content,
        'post_status'    => 'inherit'
    );

    // 将文件添加到媒体库
    $attach_id = wp_insert_attachment($attachment, $filepath);

    if (is_wp_error($attach_id)) {
        @unlink($filepath); // 删除临时文件
        return $attach_id;
    }

    // 确保 image.php 加载以生成元数据 (对于非图像文件，这可能不会生成缩略图等)
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // 返回完整尺寸的图标 URL
    return wp_get_attachment_url($attach_id);
}

/**
 * 辅助函数：将相对 URL 转换为绝对 URL
 */
function io_make_absolute_url($relative_url, $base_url, $source_url) {
    if (empty($relative_url)) return false;

    // 如果已经是绝对 URL
    if (parse_url($relative_url, PHP_URL_SCHEME) != '') {
        return $relative_url;
    }

    // 如果是协议相对 URL (//example.com/icon.png)
    if (substr($relative_url, 0, 2) === '//') {
        return parse_url($base_url, PHP_URL_SCHEME) . ':' . $relative_url;
    }

    // 如果是以 / 开头的根相对路径 (/icon.png)
    if (substr($relative_url, 0, 1) === '/') {
        return $base_url . $relative_url;
    }

    // 处理相对路径 (icon.png or ../icon.png)
    // 需要基于源 URL 的路径
    $source_path = parse_url($source_url, PHP_URL_PATH);
    $source_dir = pathinfo($source_path, PATHINFO_DIRNAME);
    if ($source_dir === '/') {
        $source_dir = '';
    }

    // 构建基础路径
    $absolute_path = $base_url . $source_dir;

    // 处理路径中的 '.' 和 '..'
    $parts = explode('/', $relative_url);
    foreach ($parts as $part) {
        if ($part == '.') {
            continue;
        } elseif ($part == '..') {
            $absolute_path = dirname($absolute_path);
        } else {
            $absolute_path = rtrim($absolute_path, '/') . '/' . $part;
        }
    }

    return $absolute_path;
}

/**
 * 检测系统代理设置
 */
function io_detect_system_proxy() {
    $proxy_config = null;
    
    // 检测 Windows 系统代理
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // 检济 HTTP_PROXY 环境变量
        $http_proxy = getenv('HTTP_PROXY') ?: getenv('http_proxy');
        $https_proxy = getenv('HTTPS_PROXY') ?: getenv('https_proxy');
        
        if ($http_proxy || $https_proxy) {
            $proxy_url = $https_proxy ?: $http_proxy;
            $proxy_config = io_parse_proxy_url($proxy_url);
        }
        
        // 如果环境变量没有，尝试检测 Windows 注册表
        if (!$proxy_config) {
            $proxy_config = io_detect_windows_registry_proxy();
        }
    } else {
        // Linux/Unix 系统检测
        $http_proxy = getenv('HTTP_PROXY') ?: getenv('http_proxy');
        $https_proxy = getenv('HTTPS_PROXY') ?: getenv('https_proxy');
        
        if ($http_proxy || $https_proxy) {
            $proxy_url = $https_proxy ?: $http_proxy;
            $proxy_config = io_parse_proxy_url($proxy_url);
        }
    }
    
    return $proxy_config;
}

/**
 * 解析代理 URL
 */
function io_parse_proxy_url($proxy_url) {
    if (empty($proxy_url)) return null;
    
    $parsed = parse_url($proxy_url);
    if (!$parsed || !isset($parsed['host'])) return null;
    
    return [
        'type' => isset($parsed['scheme']) ? $parsed['scheme'] : 'http',
        'host' => $parsed['host'],
        'port' => isset($parsed['port']) ? $parsed['port'] : 8080,
        'user' => isset($parsed['user']) ? $parsed['user'] : null,
        'pass' => isset($parsed['pass']) ? $parsed['pass'] : null
    ];
}

/**
 * 检测 Windows 注册表中的代理设置
 */
function io_detect_windows_registry_proxy() {
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') return null;
    
    try {
        // 尝试使用 PowerShell 检测代理设置
        $cmd = 'powershell -Command "Get-ItemProperty -Path \'HKCU:\Software\Microsoft\Windows\CurrentVersion\Internet Settings\' -Name ProxyEnable,ProxyServer 2>$null | Select-Object ProxyEnable,ProxyServer | ConvertTo-Json"';
        $output = shell_exec($cmd);
        
        if ($output) {
            $data = json_decode(trim($output), true);
            if ($data && isset($data['ProxyEnable']) && $data['ProxyEnable'] == 1 && !empty($data['ProxyServer'])) {
                $proxy_server = $data['ProxyServer'];
                
                // 处理 host:port 格式
                if (strpos($proxy_server, ':') !== false) {
                    list($host, $port) = explode(':', $proxy_server, 2);
                    return [
                        'type' => 'http',
                        'host' => trim($host),
                        'port' => intval(trim($port)),
                        'user' => null,
                        'pass' => null
                    ];
                }
            }
        }
    } catch (Exception $e) {
        // 忽略错误，返回 null
    }
    
    return null;
}

/**
 * 为 cURL 配置代理
 */
function io_configure_curl_proxy($ch, $proxy_config) {
    if (!$proxy_config || !is_resource($ch)) return;
    
    // 设置代理服务器
    curl_setopt($ch, CURLOPT_PROXY, $proxy_config['host'] . ':' . $proxy_config['port']);
    
    // 设置代理类型
    switch (strtolower($proxy_config['type'])) {
        case 'socks5':
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            break;
        case 'socks4':
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
            break;
        case 'http':
        case 'https':
        default:
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            break;
    }
    
    // 设置代理认证（如果有）
    if (!empty($proxy_config['user']) && !empty($proxy_config['pass'])) {
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_config['user'] . ':' . $proxy_config['pass']);
    }
    
    // 设置代理超时
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_config['port']);
}

/**
 * 增强的下载图标数据函数（支持代理）
 */
function io_download_icon_data_with_proxy($icon_url, $proxy_config = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $icon_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36');
    
    // 如果有代理配置，使用代理
    if ($proxy_config) {
        io_configure_curl_proxy($ch, $proxy_config);
    }
    
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $curl_errno = curl_errno($ch);
    curl_close($ch);
    
    if ($curl_errno) {
        return false; // 下载失败
    }

    if ($http_code >= 400) {
        return false; // HTTP 错误
    }

    if (strpos($content_type, 'image') === false) {
         // 检查是否是 ICO 文件（Content-Type 可能不规范）
        $path_info = pathinfo(parse_url($icon_url, PHP_URL_PATH));
        if (!isset($path_info['extension']) || strtolower($path_info['extension']) !== 'ico'){
             return false; // 不是图片类型，也不是 .ico 文件
        }
    }

    if (empty($body)) {
        return false; // 内容为空
    }

    return $body;
}
