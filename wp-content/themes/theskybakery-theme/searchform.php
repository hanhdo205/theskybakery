<?php
/**
 * Search Form Template
 *
 * @package TheSkyBakery
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>" style="display: flex; max-width: 500px; margin: 0 auto;">
    <input type="search" class="search-field" placeholder="Search products, cakes..." value="<?php echo get_search_query(); ?>" name="s" style="flex: 1; padding: 12px 20px; border: 2px solid #eee; border-right: none; border-radius: 5px 0 0 5px; font-size: 16px; outline: none; transition: border-color 0.3s;">
    <button type="submit" class="search-submit" style="background: var(--primary-color); color: #fff; border: none; padding: 12px 25px; border-radius: 0 5px 5px 0; cursor: pointer; transition: background 0.3s;">
        <i class="fas fa-search"></i>
    </button>
</form>

<style>
.search-form .search-field:focus {
    border-color: var(--primary-color);
}
.search-form .search-submit:hover {
    background: #b8974f;
}
</style>
