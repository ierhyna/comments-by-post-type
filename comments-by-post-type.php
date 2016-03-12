<?php

/*
  Plugin Name: Comments by Post Type
  Plugin URI: http://github.com/ierhyna/comments-by-post-type/
  Description: Separate comments by post type in admin menu.
  Version: 1.0.1
  Author: Irina Sokolovskaya
  Author URI: http://oriolo.ru/
  License: GNU General Public License v2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
  Text domain: comments-by-post-type
  Domain Path: /languages


  Copyright 2015  Irina Sokolovskaya  (email : sokolovskaja.irina@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Load Localization
 */
function cbpt_lang_init() {
    $domain = 'comments-by-post-type';
    // The "plugin_locale" filter is also used in load_plugin_textdomain()
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    load_textdomain($domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo');
    load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}

add_action('plugins_loaded', 'cbpt_lang_init');

/**
 * Exclude comments of all post types except "post" from edit-comments.php page.
 *
 * @param  array  $clauses
 * @param  object $wp_comment_query
 * @return array
 */

function cbpt_exclude_comments_query($clauses, $wp_comment_query) {
    global $wpdb;
    
    if (!$clauses['join']) {
        $clauses['join'] = "JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID";
    }
    
    if (!$wp_comment_query->query_vars['post_type']) {
        
        $post_types = apply_filters('comment_admin_menu_post_types', get_post_types(array('public' => true, 'show_ui' => true)));
        
        foreach ($post_types as $post_type) {
            if ($post_type != 'post') {
                $clauses['where'].= $wpdb->prepare(" AND {$wpdb->posts}.post_type != %s", $post_type);
            }
        }
    }
    
    return $clauses;
}

/**
 * Hook the comments clauses.
 *
 * @param object $screen
 */

function cbpt_exclude_comments_delay_hook($screen) {
    if ($screen->id == 'edit-comments') {
        add_filter('comments_clauses', 'cbpt_exclude_comments_query', 10, 2);
    }
}

add_action('current_screen', 'cbpt_exclude_comments_delay_hook', 10, 2);

/**
 * Create separated comments menu items for every post type 
 */

function cbpt_add_comments_menues() {

    $post_types = apply_filters('comment_admin_menu_post_types', get_post_types(array('public' => true, 'show_ui' => true)));

    foreach ($post_types as $post_type) {
        if ($post_type != 'post') {
            
            add_submenu_page("edit.php?post_type={$post_type}", __('Comments', 'comments-by-post-type'), __('Comments', 'comments-by-post-type'), 'moderate_comments', "edit-comments.php?post_type={$post_type}");
        }
    }
}

add_action('admin_menu', 'cbpt_add_comments_menues');

?>
