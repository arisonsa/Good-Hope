<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package CharityM3
 */

// If Acorn is available, render the view using Blade. Otherwise, fallback or error.
if (function_exists('Roots\\bootloader')) {
    echo \Roots\view('index')->render();
} else {
    // Fallback for when Acorn isn't running, though it should be.
    get_header();
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            the_title('<h1>', '</h1>');
            the_content();
        }
    } else {
        echo '<p>No content found.</p>';
    }
    get_footer();
}
