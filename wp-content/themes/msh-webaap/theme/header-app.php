<?php
/**
 * Header for templates app (no sidebar, topbar only)
 *
 * @package msh-webaap
 */

 // Empêcher le cache navigateur
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Empêcher le cache LiteSpeed (Spécifique Hostinger)
if (defined('LSCWP_V')) {
    do_action( 'litespeed_disable_cache' );
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-slate-50 text-gray-900 font-sans antialiased min-h-screen' ); ?>>

<?php wp_body_open(); ?>

<?php get_template_part( 'template-parts/layout/topbar', null, [ 
    'active_page' => is_page('dashboard') ? 'tous-les-projets' : (is_page('nouveau-projet') ? 'nouveau-projet' : (is_page('mes-projets') ? 'mes-projets' :
    '')) 
]); ?>

<div id="app-wrapper" class="min-h-screen">

    <a href="#app-main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:p-4 focus:bg-white focus:text-blue-600 focus:rounded-lg focus:shadow-lg">
        <?php esc_html_e( 'Aller au contenu principal', 'msh-webaap' ); ?>
    </a>
