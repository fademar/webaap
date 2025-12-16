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
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
	<script src="https://kit.fontawesome.com/eb37243aab.js" crossorigin="anonymous"></script>
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-gray-100 text-gray-900 font-sans antialiased flex flex-col min-h-screen' ); ?>>

<?php wp_body_open(); ?>