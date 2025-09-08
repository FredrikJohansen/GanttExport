<li>
    <a href="/?controller=GanttExportController&action=export&project_id=<?= $project['id'] ?>&plugin=GanttExport" title="<?= t('Export Gantt chart with visual representation (Print to PDF)') ?>">
        <i class="fa fa-file-o"></i> <?= t('Export Gantt Chart') ?>
    </a>
</li>
<li>
    <a href="/?controller=GanttExportController&action=export&project_id=<?= $project['id'] ?>&plugin=GanttExport&hide_today=1" title="<?= t('Export Gantt chart without NOW indicator (Print to PDF)') ?>">
        <i class="fa fa-file-o"></i> <?= t('Export Gantt Chart (No NOW indicator)') ?>
    </a>
</li>