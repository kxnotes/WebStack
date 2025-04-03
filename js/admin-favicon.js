jQuery(document).ready(function($) {
    // 获取按钮、状态区域、预览图、Spinner 等元素
    const fetchButton = $('#fetch-favicon-button');
    const statusMessage = $('#favicon-status-message');
    const previewImg = $('#favicon-preview');
    const spinner = $('#favicon-fetch-spinner');
    const nonceField = $('#fetch_favicon_nonce_field');
    // 使用 name 属性选择器获取主网址输入框
    const siteUrlInput = $('input[name="sites_meta[_sites_link]"]'); 
    const siteUrlDisplay = $('#site-url-display'); // 获取只读显示框

    // 从主 URL 输入框同步 URL 到只读框 (当用户修改主 URL 时)
    if(siteUrlInput.length && siteUrlDisplay.length){
        // 初始加载时也同步一次
        siteUrlDisplay.val(siteUrlInput.val());
        siteUrlInput.on('change keyup', function() {
            siteUrlDisplay.val($(this).val());
        });
    }

    // 按钮点击事件
    fetchButton.on('click', function() {
        // 获取最新的 URL 和 Post ID
        const siteUrl = siteUrlInput.val(); // 从可编辑的输入框获取最新的 URL
        
        let postId = null;
        // 安全地尝试从 Gutenberg 获取 Post ID
        if (typeof wp !== 'undefined' && typeof wp.data !== 'undefined' && typeof wp.data.select === 'function') {
             const editorSelect = wp.data.select('core/editor');
             if (editorSelect && typeof editorSelect.getCurrentPostId === 'function') {
                 postId = editorSelect.getCurrentPostId();
             }
        }

        // 使用从 PHP 传递过来的 postId 作为备用（兼容经典编辑器）
        const postIdFallback = (typeof admin_favicon_params !== 'undefined' && admin_favicon_params.post_id) ? admin_favicon_params.post_id : 0;
        // 优先使用 Gutenberg 获取的 ID，否则使用备用 ID
        const finalPostId = postId || postIdFallback;

        const nonce = nonceField.val();

        if (!siteUrl) {
            statusMessage.css('color', 'red').text('请输入目标网址！');
            return;
        }
        // 确保最终获取到了 Post ID
        if (!finalPostId) {
            statusMessage.css('color', 'red').text('无法获取文章 ID！请刷新页面重试。');
            return;
        }
        if (!nonce) {
            statusMessage.css('color', 'red').text('安全验证失败 (Nonce missing)！');
            return;
        }

        // 禁用按钮并显示加载状态
        fetchButton.prop('disabled', true);
        spinner.addClass('is-active');
        statusMessage.css('color', '').text('正在获取 Favicon...');

        // 发送 AJAX 请求
        $.ajax({
            url: ajaxurl, // WordPress AJAX URL (全局变量)
            type: 'POST',
            data: {
                action: 'fetch_site_favicon', // 对应 PHP add_action 的名字
                nonce: nonce,
                post_id: finalPostId,
                site_url: siteUrl
            },
            success: function(response) {
                if (response.success) {
                    statusMessage.css('color', 'green').text(response.data.message);
                    // 更新预览图
                    if (response.data.favicon_url) {
                        previewImg.attr('src', response.data.favicon_url);
                        // 新增：更新 _thumbnail 输入框的值，确保保存时提交正确数据
                        $('input[name="sites_meta[_thumbnail]"]').val(response.data.favicon_url);
                        // 可选：尝试更新 CS Framework 的 _thumbnail 预览 (如果知道其 DOM 结构)
                        // 例如: $('input[name="_thumbnail"]').val(response.data.favicon_url);
                        // $('.cs-field-image .cs-preview img').attr('src', response.data.favicon_url);
                    }
                } else {
                    statusMessage.css('color', 'red').text('错误: ' + (response.data.message || '未知错误'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                statusMessage.css('color', 'red').text('AJAX 请求失败: ' + textStatus + ' - ' + errorThrown);
            },
            complete: function() {
                // 无论成功失败，都恢复按钮和 Spinner
                fetchButton.prop('disabled', false);
                spinner.removeClass('is-active');
            }
        });
    });
}); 