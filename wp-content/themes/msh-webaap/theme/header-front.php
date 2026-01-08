<?php
/**
 * The header for the Front Page / Login screen.
 *
 * @package msh-webaap
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-gray-100 text-gray-900 font-sans antialiased flex flex-col min-h-screen' ); ?>>

<?php wp_body_open(); ?>