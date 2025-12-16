<?php
/**
 * Header for test templates (no sidebar, topbar only)
 *
 * @package msh-webaap
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <script src="https://kit.fontawesome.com/eb37243aab.js" crossorigin="anonymous"></script>
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-gray-50 text-gray-900 font-sans antialiased min-h-screen' ); ?>>

<?php wp_body_open(); ?>

<div id="app-wrapper" class="min-h-screen">

    <a href="#app-main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:p-4 focus:bg-white focus:text-blue-600 focus:rounded-lg focus:shadow-lg">
        <?php esc_html_e( 'Aller au contenu principal', 'msh-webaap' ); ?>
    </a>
