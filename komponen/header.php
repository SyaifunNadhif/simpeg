<?php
// Komponen reusable untuk header + breadcrumb
// Cara pakai:
// include 'components/header.php';
// Set variabel sebelum include:
// $page_title = 'Judul';
// $page_subtitle = 'Subjudul';
// $breadcrumbs = [ ['label' => 'Home', 'url' => 'home-admin.php'], ['label' => 'Judul Sekarang'] ];
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h1 class="h4 font-weight-bold text-dark mb-0">
        <?= isset($page_title) ? $page_title : 'Judul Halaman'; ?>
        <?php if (isset($page_subtitle)): ?>
          <small class="text-muted"><?= $page_subtitle; ?></small>
        <?php endif; ?>
      </h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
          <?php foreach ($breadcrumbs as $i => $item): ?>
            <?php if (isset($item['url'])): ?>
              <li class="breadcrumb-item">
                  <?= $item['label']; ?>
              </li>
            <?php else: ?>
              <li class="breadcrumb-item active text-muted" aria-current="page">
                <?= $item['label']; ?>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </ol>
    </div>
  </div>
</section>
