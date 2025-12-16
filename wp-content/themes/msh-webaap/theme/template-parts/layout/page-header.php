<?php
/**
 * Template part: Page header (app)
 *
 * Usage:
 * get_template_part( 'template-parts/layout/page-header', null, [
 *   'title'    => 'Dashboard',
 *   'subtitle' => 'Filtrez, cherchez et ouvrez les dossiers.',
 *   'actions'  => [
 *     [ 'label' => 'Nouveau projet', 'url' => home_url('/nouveau-projet/'), 'class' => '...' ],
 *   ],
 * ] );
 *
 * @package msh-webaap
 */
?>

<?php
$args = $args ?? [];

$title    = isset( $args['title'] ) ? (string) $args['title'] : '';
$subtitle = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$actions  = isset( $args['actions'] ) && is_array( $args['actions'] ) ? $args['actions'] : [];
?>

<?php if ( $title !== '' || $subtitle !== '' || ! empty( $actions ) ) : ?>
<header class="mb-6">
  <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
      <?php if ( $title !== '' ) : ?>
        <h1 class="text-2xl font-bold text-gray-900"><?php echo esc_html( $title ); ?></h1>
      <?php endif; ?>

      <?php if ( $subtitle !== '' ) : ?>
        <p class="mt-1 text-sm text-gray-500"><?php echo esc_html( $subtitle ); ?></p>
      <?php endif; ?>
    </div>

    <?php if ( ! empty( $actions ) ) : ?>
      <div class="flex flex-wrap gap-2">
        <?php foreach ( $actions as $a ) : ?>
          <?php
          if ( ! is_array( $a ) ) { continue; }
          $label = isset( $a['label'] ) ? (string) $a['label'] : '';
          $url   = isset( $a['url'] ) ? (string) $a['url'] : '';
          $class = isset( $a['class'] ) ? (string) $a['class'] : 'inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50';
          if ( $label === '' || $url === '' ) { continue; }
          ?>
          <a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>">
            <?php echo esc_html( $label ); ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</header>
<?php endif; ?>

