<div id="search" class="s-search">
    <!-- Step 1: REMOVED Main Categories Above Search Bar -->
    <!-- <div id="main-search-categories"> ... </div> -->

    <!-- Original Search Form -->
    <form action="?s=" method="get" target="_blank" id="super-search-fm"><input type="text" id="search-text" placeholder="输入关键字搜索" style="outline:0"><button type="submit"><i class="fa fa-search "></i></button></form>

    <!-- Step 2: Simplified Search List Below Search Bar -->
    <div id="search-list">
        <!-- Removed .search-group wrappers and multiple groups -->
        <ul class="search-type">
            <!-- Reordered and kept only required engines. Set Bing checked by default -->
            <li><input checked hidden type="radio" name="type" id="type-bing" value="https://cn.bing.com/search?q=" data-placeholder="<?php _e('微软Bing搜索','i_theme') ?>"><label for="type-bing"><span style="color:#007daa">Bing</span></label></li>
            <li><input hidden type="radio" name="type" id="type-google" value="https://www.google.com/search?q=" data-placeholder="<?php _e('谷歌两下','i_theme') ?>"><label for="type-google"><span style="color:#3B83FA">G</span><span style="color:#F3442C">o</span><span style="color:#FFC300">o</span><span style="color:#4696F8">g</span><span style="color:#2CAB4E">l</span><span style="color:#F54231">e</span></label></li>
            <li><input hidden type="radio" name="type" id="type-zhannei" value="<?php bloginfo('url') ?>?s=" data-placeholder="<?php _e('站内搜索','i_theme') ?>"><label for="type-zhannei"><span style="color:#888888"><?php _e('站内','i_theme') ?></span></label></li>
            <li><input hidden type="radio" name="type" id="type-baidu" value="https://www.baidu.com/s?wd=" data-placeholder="<?php _e('百度一下','i_theme') ?>"><label for="type-baidu"><span style="color:#2100E0"><?php _e('百度','i_theme') ?></span></label></li>
            <!-- Other engines removed -->
        </ul>
    </div>
    <div class="set-check hidden-xs">
        <input type="checkbox" id="set-search-blank" class="bubble-3" autocomplete="off">
    </div>
</div>

<!-- REMOVED the old obfuscated script -->

<!-- Updated Clear JavaScript -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // --- DOM Elements ---
    // REMOVED mainCategoriesContainer, mainCategoryLinks
    const searchForm = document.getElementById('super-search-fm');
    const searchInput = document.getElementById('search-text');
    const searchList = document.getElementById('search-list'); // Changed from searchGroupsContainer
    // REMOVED searchGroups
    const newWindowCheckbox = document.getElementById('set-search-blank');

    // --- Constants ---
    // REMOVED LS_KEY_CATEGORY
    const LS_KEY_ENGINE = 'superSearchLastEngine';   // e.g., 'type-bing'
    const LS_KEY_NEW_WINDOW = 'superSearchNewWindow'; // 'true' or 'false'

    // --- Functions ---

    // Update search input placeholder and form action based on selected engine
    function updateSearchTarget(engineRadioInput) {
        if (!engineRadioInput) return;
        const placeholder = engineRadioInput.getAttribute('data-placeholder') || '';
        const actionUrl = engineRadioInput.value || '';
        searchInput.setAttribute('placeholder', placeholder);
        searchForm.setAttribute('action', actionUrl);
    }

    // Update form target based on checkbox state
    function updateFormTarget() {
        if (newWindowCheckbox.checked) {
            searchForm.setAttribute('target', '_blank');
        } else {
            searchForm.removeAttribute('target');
        }
        // Save state
        localStorage.setItem(LS_KEY_NEW_WINDOW, newWindowCheckbox.checked);
    }

    // REMOVED setActiveCategory function

    // --- Event Listeners ---

    // REMOVED Main category link clicks listener

    // Engine radio button changes (handles label clicks indirectly)
    // Use searchList directly now
    searchList.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                updateSearchTarget(this);
                // Save state (only engine, category removed)
                localStorage.setItem(LS_KEY_ENGINE, this.id);
            }
        });
    });

    // New window checkbox change
    newWindowCheckbox.addEventListener('change', updateFormTarget);

    // Form submission
    searchForm.addEventListener('submit', function(event) {
        // Basic validation: prevent submission if input is empty
        if (searchInput.value.trim() === '') {
            event.preventDefault();
            searchInput.focus();
            // Optional: add some visual feedback like a shake animation
            searchInput.style.animation = 'shake 0.5s';
            setTimeout(() => { searchInput.style.animation = ''; }, 500);
            return false;
        }
        // Action and target are handled by standard form submission or updateFormTarget
    });

    // --- Initialization (Simplified) ---
    // Restore new window state
    const savedNewWindowState = localStorage.getItem(LS_KEY_NEW_WINDOW);
    newWindowCheckbox.checked = (savedNewWindowState === 'true');
    updateFormTarget(); // Set initial form target

    // Restore last engine or set default (Bing)
    const defaultEngineId = 'type-bing'; // Default to Bing
    const savedEngineId = localStorage.getItem(LS_KEY_ENGINE);
    let engineToSelect = null;

    // Try to find the saved engine
    if (savedEngineId) {
        engineToSelect = document.getElementById(savedEngineId);
        // Basic validation: check if the saved engine actually exists in the current list
        if (!engineToSelect || !searchList.contains(engineToSelect)) {
            engineToSelect = null; // Invalid saved engine, fallback to default
        }
    }

    // If no valid saved engine, use the default
    if (!engineToSelect) {
        engineToSelect = document.getElementById(defaultEngineId);
    }

    // If default also somehow not found (edge case), select the first one available
    if (!engineToSelect) {
       engineToSelect = searchList.querySelector('input[type="radio"]');
    }

    // Select the engine and update UI
    if (engineToSelect) {
        engineToSelect.checked = true;
        updateSearchTarget(engineToSelect);
        // Ensure localStorage reflects the actual selected engine in case we fell back
        localStorage.setItem(LS_KEY_ENGINE, engineToSelect.id);
    } else {
        console.error("Search Tool Error: No search engine radio buttons found or configured!");
        // Handle error: maybe disable search input?
        searchInput.placeholder = '错误：未配置搜索引擎';
        searchInput.disabled = true;
    }

});
</script>
