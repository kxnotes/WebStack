<div id="search" class="s-search">
    <!-- Step 1: REMOVED Main Categories Above Search Bar -->
    <!-- <div id="main-search-categories"> ... </div> -->

    <!-- Updated Search Form for Site Search Only -->
    <!-- Form action points to site root, WP handles ?s= -->
    <!-- Input field now has name="s" for WP search -->
    <!-- Placeholder explicitly set for site search -->
    <form action="<?php bloginfo('url'); ?>/" method="get" id="super-search-fm">
        <input type="text" id="search-text" name="s" placeholder="<?php _e('站内搜索','i_theme'); ?>" style="outline:0">
        <button type="submit"><i class="fa fa-search "></i></button>
    </form>

    <!-- Step 2: REMOVED Search Engine Selection List -->
    <!-- <div id="search-list"> ... </div> -->

    <!-- REMOVED the "open in new window" checkbox -->
    <!-- <div class="set-check hidden-xs"> ... </div> -->
</div>

<!-- REMOVED the old obfuscated script -->

<!-- Simplified JavaScript for Site Search Input Validation -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // --- DOM Elements ---
    const searchForm = document.getElementById('super-search-fm');
    const searchInput = document.getElementById('search-text');

    // --- Event Listeners ---

    // Form submission validation
    searchForm.addEventListener('submit', function(event) {
        // Basic validation: prevent submission if input is empty
        if (searchInput.value.trim() === '') {
            event.preventDefault(); // Stop form submission
            searchInput.focus(); // Focus the input field
            // Optional: add some visual feedback like a shake animation
            searchInput.style.animation = 'shake 0.5s';
            // Remove the animation after it finishes
            setTimeout(() => { searchInput.style.animation = ''; }, 500);
            return false; // Prevent submission
        }
        // If input is not empty, the form submits normally via GET to the action URL specified in the HTML <form> tag
    });

    // --- Initialization ---
    // No initialization needed anymore (engine selection, new window state, etc. removed).
    // Placeholder is set directly in HTML.
});
</script>
