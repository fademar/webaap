<?php 
$project_id = get_the_ID();

$logs = get_posts([
    'post_type'      => 'projet_log',
    'posts_per_page' => -1,
    'meta_query'     => [
        [
            'key'   => 'log_project',
            'value' => $project_id,
        ],
    ],
    'orderby' => 'date',
    'order'   => 'DESC',
]);

function msh_get_log_ui($type) {
  return match ($type) {
      'submission'     => ['icon' => 'fa-paper-plane', 'color' => 'blue'],
      'status_change'  => ['icon' => 'fa-arrow-right-arrow-left', 'color' => 'purple'],
      'comment_add'    => ['icon' => 'fa-comment-dots', 'color' => 'green'],
      'evaluation_add',
      'evaluation_update',
      'evaluation_delete'
                        => ['icon' => 'fa-clipboard-check', 'color' => 'indigo'],
      'budget'         => ['icon' => 'fa-euro-sign', 'color' => 'amber'],
      'export_pdf'     => ['icon' => 'fa-file-pdf', 'color' => 'yellow'],
      'attachment'     => ['icon' => 'fa-paperclip', 'color' => 'slate'],
      default          => ['icon' => 'fa-circle', 'color' => 'gray'],
  };
}

?>

<div class="bg-white p-6">

  <?php if ($logs): ?>
    <ul class="relative space-y-6">

      <!-- Ligne verticale -->
      <span class="absolute left-4 top-0 bottom-0 w-px bg-gray-200"></span>

      <?php foreach ($logs as $log): 
        $type   = get_field('log_type', $log->ID);
        $actor  = get_field('log_actor', $log->ID);
        $ui     = msh_get_log_ui($type);
        $date   = date_i18n('d/m/Y · H:i', strtotime($log->post_date));
        $user   = $actor ? get_userdata($actor) : null;
      ?>

        <li class="relative flex gap-4">
          <div class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full bg-<?= esc_attr($ui['color']) ?>-100 text-<?= esc_attr($ui['color']) ?>-700">
            <i class="fa-solid <?= esc_attr($ui['icon']) ?> text-sm"></i>
          </div>

          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 text-sm">
              <span class="text-slate-500"><?= esc_html($date) ?></span>

              <span class="font-medium text-slate-900">
                <?= esc_html($log->post_title) ?>
              </span>

              <?php if ($user): ?>
                <span class="text-slate-500">
                  — <?= esc_html($user->display_name) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </li>

      <?php endforeach; ?>

    </ul>
  <?php else: ?>

    <p class="text-sm text-slate-500">
      Aucun événement n’a encore été enregistré pour ce projet.
    </p>

  <?php endif; ?>

</div>