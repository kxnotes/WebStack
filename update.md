# Favicon 获取功能增强 - 需求与计划文档

## 版本：0.0.1

**日期**: 2024-08-02

**功能**: 增强导航网址的 Favicon 获取能力，支持重定向并本地化存储。

**目标**: 修改当前主题，使其能够正确获取导航网址（特别是涉及重定向的网址，如短链接）的 Favicon，并将图标存储在本地媒体库，减少对外部 API 的依赖和手动输入的需求。

**核心需求**:

1.  **重定向支持**: 必须能正确处理 HTTP 重定向（3xx），获取最终目标 URL。
2.  **重定向追踪**: 使用 PHP cURL 库追踪重定向，限制最大跳转次数（例如 5 次）以防无限循环。
3.  **抓取策略 (基于最终 URL)**:
    *   优先级 1: 尝试直接获取最终 URL 根目录下的 `/favicon.ico`。
    *   优先级 2: 若失败，获取最终 URL 的 HTML 内容，解析 `<head>` 中的 `<link>` 标签（查找 `rel="icon"`, `rel="shortcut icon"`, `rel="apple-touch-icon"` 等），并尝试下载 `href` 指向的图标。
    *   优先级 3: 若仍失败，尝试查找并解析 `manifest.json` 文件中的图标信息。
4.  **本地存储**: 成功抓取 Favicon 后，必须将其上传并保存到 WordPress 媒体库。
5.  **元数据更新**:
    *   将获取到的 Favicon 在媒体库中的 **URL** 保存到 `sites` 文章类型对应的 `_thumbnail` Post Meta 字段中。
    *   将解析得到的最终 URL 保存到一个新的 Post Meta 字段 `_final_url` 中。
6.  **失败处理**: 如果重定向解析失败、所有抓取策略均失败或存入媒体库失败，则应有明确的失败状态，前端显示默认图标 (`/images/favicon.png`)。
7.  **手动触发**: Favicon 获取流程不应在保存文章时自动触发，而应通过在后台 `sites` 文章编辑页面添加一个**手动点击的按钮**来启动。
8.  **用户反馈**: 在后台编辑页面，点击按钮后应有明确的视觉反馈（如加载状态），并通过 AJAX 返回操作结果（成功获取并显示新图标预览 / 获取失败并提示原因）。

**实施计划**:

1.  **核心 PHP 函数**: 在 `inc/ajax.php` (或 `inc/inc.php`) 中创建一个核心函数，如 `io_enhanced_get_favicon($url, $post_id)`，负责实现重定向追踪、多策略抓取、媒体库存储，并返回获取到的图标 URL 或 WP_Error 对象。
2.  **AJAX Handler**: 在 `inc/ajax.php` 中添加 `handle_fetch_site_favicon_ajax` 函数，作为 AJAX 请求的后端处理程序。它将调用核心函数，更新 `_thumbnail` 和 `_final_url` 元数据，并返回 JSON 响应给前端。
3.  **后台界面**:
    *   在 `sites` 文章编辑页面添加"获取 Favicon"按钮（考虑使用 `add_meta_box` 或修改现有 Metabox 框架配置）。
    *   添加必要的 JavaScript 代码来处理按钮点击、Nonce 安全验证、发送 AJAX 请求以及更新前端界面反馈。
4.  **显示逻辑**: 利用现有 `templates/site-card.php` 的逻辑，该逻辑已优先使用 `_thumbnail` 字段。通过将新获取的图标 URL 存入 `_thumbnail`，实现无缝显示更新。

# 更新日志

## 版本 0.0.2 (YYYY-MM-DD)

- **文件**: `css/nav.css`
- **修改**: 
    - 调整了搜索框容器 (`#search`) 的外边距。
    - 桌面端默认 `margin` 设置为 `80px auto 14px`。
    - 添加了媒体查询 (`@media screen and (max-width: 767px)`)，为移动端设置 `margin` 为 `30px 15px 14px`。

## 版本 0.0.1 (YYYY-MM-DD)

- **文件**: `search-tool.php`, `css/nav.css`
- **修改**: 
    - **PHP (`search-tool.php`)**: 
        - 移除了顶部的多分类导航（常用、搜索、工具等）。
        - 精简了搜索引擎列表，仅保留 Bing、Google、站内搜索、百度。
        - 调整了搜索引擎的显示顺序为：Bing、Google、站内、百度。
        - 设置 Bing 为默认选中的搜索引擎。
        - 简化了相关的 JavaScript 代码，移除了分类处理逻辑，优化了初始化和状态保存（仅针对引擎选择和新窗口设置）。
    - **CSS (`css/nav.css`)**: 
        - 更新了搜索区域的 CSS 选择器，以匹配修改后的 HTML 结构（移除 `.search-group` 层级）。
        - 确保搜索引擎列表 (`#search-list .search-type li`) 恢复水平排列。
        - 添加了 `text-align: center` 样式，使搜索引擎链接居中显示。

## v0.0.5 (YYYY-MM-DD)

- **样式**: 修改网站详情页图标 (`.siteico`) 样式。
    - 移除了背景层 (`.blur-layer`) 的旋转动画。
    - 移除了图标本身 (`.img-cover`) 的旋转动画。
    - 将图标 (`.img-cover`) 的尺寸调整为 128x128 像素。
- **文件修改**:
    - `single-sites.php`: 删除了 `.blur-layer` 行内样式中的 `animation` 规则。
    - `css/nav.css`: 修改了 `.img-cover` 的 `width`, `height` 和 `animation` 属性。

## v0.0.4 (YYYY-MM-DD)

- **功能增强**: 显著提高了 Favicon 获取成功率。
    - 增加了对更多常见 Favicon 路径的尝试 (如 `/favicon.png`, `/apple-touch-icon.png`)。
    - 新增 Logo 获取作为备选方案：如果找不到 Favicon，会尝试解析 HTML 中的 `og:image` meta 标签，并尝试常见的 Logo 文件路径 (如 `/logo.png`)。
- **文件修改**:
    - `inc/ajax.php`: 重构了 `io_get_favicon` 函数以实现多策略图标/Logo 获取流程，并添加了 `io_parse_html_for_og_image` 辅助函数。

## v0.0.3 (YYYY-MM-DD)

- **功能**: 新增将获取到的 Favicon 统一转换为 PNG 格式再保存到媒体库的功能。
- **文件修改**:
    - `inc/ajax.php`: 修改了 `io_save_icon_to_media_library` 函数，在保存前使用 GD 库尝试将图标数据转换为 PNG 格式。

## v0.0.2 (YYYY-MM-DD)

- **修复**: 修复了通过 AJAX 获取 Favicon 后，图标 URL 未同步更新到文章编辑页面的 `_thumbnail` 输入框，导致保存文章时图标丢失的问题。
- **文件修改**: 
    - `js/admin-favicon.js`: 在 AJAX 成功回调中添加了更新 `input[name="sites_meta[_thumbnail]"]` 值的代码。 