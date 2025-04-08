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

## v0.0.5 (YYYY-MM-DD)

- **Bug修复 & 优化**:
    - **灯箱功能**: 
        - 修复了移动端 Fancybox 初始化时因工具栏配置导致的 JavaScript 错误 (TypeError: i[t] is not iterable)。
        - 调整 Fancybox 初始化设置，使用默认工具栏，确保功能稳定运行。

## v0.0.4 (YYYY-MM-DD)

- **功能优化**:
    - **图片查看体验**:
        - 优化灯箱功能，现在只在移动端启用
        - 桌面端直接显示响应式图片，无需点击放大
    - **性能优化**:
        - 减少桌面端不必要的 JavaScript 和 CSS 加载
        - 保持移动端完整的灯箱功能体验

## v0.0.3 (YYYY-MM-DD)

- **功能优化**:
    - **响应式图片支持**:
        - 启用 WordPress 原生的响应式图片功能
        - 优化图片在移动端的加载，自动使用 300px 宽度的缩略图
    - **图片查看体验**:
        - 集成 Fancybox 5.0 灯箱功能
        - 支持图片点击放大、全屏查看
        - 优化图片加载性能
    - **代码重构**:
        - 重写图片处理逻辑，使用 WordPress 标准函数
        - 添加图片处理相关的钩子函数

## v0.0.2 (YYYY-MM-DD)

- **结构 & 样式调整**:
    - **`templates/header-nav.php`**: 将 Logo 外层 `div.logo` 修改为 `h1.logo`，使 Logo 成为站点主标题。
    - **`css/nav.css`**:
        - 移除了用于隐藏 `h1.screen-reader-text` 的 CSS 规则。
        - 添加了用于重置 `h1.logo` 浏览器默认样式的 CSS 规则，以保持 Logo 外观不变。
    - **`index.php`**: 移除了原有的隐藏的 `h1.screen-reader-text` 标签。

## v0.0.1 (YYYY-MM-DD)

- **样式调整:**
    - **`css/nav.css`**:
        - 完全隐藏首页 `h1.screen-reader-text` 元素。
        - 将分类标题 (`.sites-list h2.text-gray`) 的字体大小调整为 18px。

## 版本 0.0.2 (2024-08-03)

- **文件**: `header.php`
- **修复**: 移除了非首页 `<title>` 标签中重复输出网站名称 (`bloginfo('name')`) 的问题，解决标题显示为 "文章名 - 网站名网站名" 的错误。

## 版本 0.0.2 (2024-08-03)

- **文件**: `css/nav.css`
- **修改**: 
    - 调整了搜索框容器 (`#search`) 的外边距。
    - 桌面端默认 `margin` 设置为 `80px auto 14px`。
    - 添加了媒体查询 (`@media screen and (max-width: 767px)`)，为移动端设置 `margin` 为 `30px 15px 14px`。

## 版本 0.0.1 (2024-08-03)

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

## v0.0.5 (2024-08-03)

- **样式**: 修改网站详情页图标 (`.siteico`) 样式。
    - 移除了背景层 (`.blur-layer`) 的旋转动画。
    - 移除了图标本身 (`.img-cover`) 的旋转动画。
    - 将图标 (`.img-cover`) 的尺寸调整为 128x128 像素。
- **文件修改**:
    - `single-sites.php`: 删除了 `.blur-layer` 行内样式中的 `animation` 规则。
    - `css/nav.css`: 修改了 `.img-cover` 的 `width`, `height` 和 `animation` 属性。

## v0.0.4 (2024-08-03)

- **功能增强**: 显著提高了 Favicon 获取成功率。
    - 增加了对更多常见 Favicon 路径的尝试 (如 `/favicon.png`, `/apple-touch-icon.png`)。
    - 新增 Logo 获取作为备选方案：如果找不到 Favicon，会尝试解析 HTML 中的 `og:image` meta 标签，并尝试常见的 Logo 文件路径 (如 `/logo.png`)。
- **文件修改**:
    - `inc/ajax.php`: 重构了 `io_get_favicon` 函数以实现多策略图标/Logo 获取流程，并添加了 `io_parse_html_for_og_image` 辅助函数。

## v0.0.3 (2024-08-03)

- **功能**: 新增将获取到的 Favicon 统一转换为 PNG 格式再保存到媒体库的功能。
- **文件修改**:
    - `inc/ajax.php`: 修改了 `io_save_icon_to_media_library` 函数，在保存前使用 GD 库尝试将图标数据转换为 PNG 格式。

## v0.0.2 (2024-08-03)

- **修复**: 修复了通过 AJAX 获取 Favicon 后，图标 URL 未同步更新到文章编辑页面的 `_thumbnail` 输入框，导致保存文章时图标丢失的问题。
- **文件修改**: 
    - `js/admin-favicon.js`: 在 AJAX 成功回调中添加了更新 `input[name="sites_meta[_thumbnail]"]` 值的代码。

# 更新记录

## 版本 0.0.1 (2024-08-03)

- **文件:** `header.php`
- **修改:** 注释掉主题自带的 SEO 相关元标签输出（包括 title, keywords, description, Open Graph, favicon, apple-touch-icon），以避免与 Rank Math 插件冲突。

## 版本 0.0.2 (2024-08-03)

- **文件:** `templates/header-banner.php`
- **修改:** 注释掉顶部的天气插件代码。

## 版本 0.0.3 (2024-08-03)

- **文件:** `functions.php`
- **修改:** 添加自定义代码，修改 WordPress 图片上传逻辑：
  - 如果原图宽度 > 1200px，只额外生成 300px 宽度的版本。
  - 如果原图宽度 <= 1200px，不生成任何额外尺寸。
  - 目标是取代图片优化插件，精确控制生成的尺寸，减少冗余文件。

## 版本 0.0.4 (2024-08-03)

- **文件:** `header.php`
- **修改:** 在 `<head>` 部分添加了 `<link rel="preload">` 标签，用于预加载核心 CSS (`nav.css`, `font-awesome.min.css`) 和关键字体 (`fontawesome-webfont.woff2`)，以提升页面加载性能。

## 版本 0.0.5 (2024-08-03)

- **文件:** `index.php`
- **修改:** 在首页内容区域添加了一个包含网站名称 (`bloginfo('name')`) 的 `<h1>` 标签，并使用 `screen-reader-text` 类在视觉上隐藏它，以满足 SEO 和可访问性要求，同时不影响现有设计。

## 版本 0.0.6 (2024-08-03)

- **文件:** `inc/fav-content.php`
- **修改:** 将 `fav_con()` 函数中输出分类标题的 HTML 标签从 `<h4>` 修改为 `<h2>`，以确保首页标题层级的正确性 (`<h1>` -> `<h2>`)。

## 版本 0.0.7 (2024-08-03)

- **文件:** `functions.php`, `header.php`
- **修改:** 统一并明确网站语言声明。
  - 在 `functions.php` 添加过滤器，通过 `language_attributes` 钩子为 `<html>` 标签自动添加 `prefix="og: https://ogp.me/ns#"` 属性。
  - 在 `header.php` 的 `<head>` 部分添加 `<meta property="og:locale" content="zh_CN" />` 标签。

## 版本 0.0.1 (2024-08-03)

- **文件:** `functions.php`
- **修改:** 添加了对 `title-tag` 的主题支持，以允许 WordPress 和 SEO 插件（如 Rank Math）自动生成页面 `<title>` 标签。
  ```php
  // Add theme support features
  function webstack_theme_setup() {
  	// Add theme support for title tag
  	// Lets WordPress manage the document title.
  	// By adding theme support, we declare that this theme does not use a
  	// hard-coded <title> tag in the document head, and expect WordPress to
  	// provide it for us.
  	add_theme_support( 'title-tag' );
  }
  add_action( 'after_setup_theme', 'webstack_theme_setup' );
  ``` 