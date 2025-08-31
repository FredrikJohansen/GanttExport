<?php

namespace Kanboard\Plugin\GanttExport\Controller;

use Kanboard\Controller\BaseController;

class GanttExportController extends BaseController
{
    private function getLanguageTexts()
    {
        $current_language = $this->languageModel->getCurrentLanguage();
        $is_norwegian = (strpos($current_language, 'nb') === 0 || strpos($current_language, 'no') === 0 || $current_language === 'norwegian');
        
        if ($is_norwegian) {
            return [
                'gantt_chart' => 'Gantt-diagram',
                'export_date' => 'Eksportdato',
                'time_period' => 'Tidsperiode',
                'task' => 'Oppgave',
                'timeline' => 'Tidslinje',
                'task_name' => 'Oppgavenavn',
                'start_date' => 'Startdato',
                'due_date' => 'Forfallsdato',
                'duration' => 'Varighet',
                'assigned' => 'Tildelt',
                'estimate' => 'Estimat (t)',
                'time_spent' => 'Brukt tid (t)',
                'status' => 'Status',
                'not_assigned' => 'Ikke tildelt',
                'active' => 'Aktiv',
                'completed' => 'Fullført',
                'overdue' => 'Forsinket',
                'days' => 'dager',
                'explanation' => 'Forklaring',
                'completed_tasks' => 'Fullførte oppgaver',
                'active_tasks' => 'Aktive oppgaver',
                'overdue_tasks' => 'Forsinkede oppgaver',
                'total_tasks' => 'Totale oppgaver',
                'progress' => 'Fremdrift',
                'today' => 'I DAG',
                'no_tasks_found' => 'Ingen oppgaver med datoer funnet',
                'months' => ['jan', 'feb', 'mar', 'apr', 'mai', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'des']
            ];
        } else {
            return [
                'gantt_chart' => 'Gantt Chart',
                'export_date' => 'Export Date',
                'time_period' => 'Time Period',
                'task' => 'Task',
                'timeline' => 'Timeline',
                'task_name' => 'Task Name',
                'start_date' => 'Start Date',
                'due_date' => 'Due Date',
                'duration' => 'Duration',
                'assigned' => 'Assigned',
                'estimate' => 'Estimate (h)',
                'time_spent' => 'Time Spent (h)',
                'status' => 'Status',
                'not_assigned' => 'Not assigned',
                'active' => 'Active',
                'completed' => 'Completed',
                'overdue' => 'Overdue',
                'days' => 'days',
                'explanation' => 'Legend',
                'completed_tasks' => 'Completed tasks',
                'active_tasks' => 'Active tasks',
                'overdue_tasks' => 'Overdue tasks',
                'total_tasks' => 'Total tasks',
                'progress' => 'Progress',
                'today' => 'TODAY',
                'no_tasks_found' => 'No tasks with dates found',
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            ];
        }
    }
    
    public function export()
    {
        $project_id = $this->request->getIntegerParam('project_id');
        $hide_today = $this->request->getStringParam('hide_today') === '1';
        
        // Get project info
        $project = $this->projectModel->getById($project_id);
        
        if (empty($project)) {
            echo "<h1>Error: Project not found</h1>";
            exit();
        }
        
        // Get tasks with dates
        $tasks = $this->getTasksWithDates($project_id);
        
        $texts = $this->getLanguageTexts();
        
        if (empty($tasks)) {
            echo "<p>" . $texts['no_tasks_found'] . "</p>";
            exit();
        }
        
        // Calculate date range
        $start_dates = array();
        $end_dates = array();
        foreach ($tasks as $task) {
            $start_dates[] = $task['date_started'];
            $end_dates[] = $task['date_due'];
        }
        $start_date = min($start_dates);
        $end_date = max($end_dates);
        // Use consistent integer days calculation throughout
        $base_span_days = ceil(($end_date - $start_date) / 86400);
        $actual_timeline_days = $base_span_days + 1; // Add 1 extra day for the blank marker
        $total_days = $actual_timeline_days + 0.5;
        
        echo $this->generateFullGanttHTML($project, $tasks, $start_date, $end_date, $total_days, $texts, $hide_today, $actual_timeline_days);
        exit();
    }
    
    private function getTasksWithDates($project_id)
    {
        return $this->db->table('tasks')
            ->columns(
                'tasks.id',
                'tasks.title',
                'tasks.date_started',
                'tasks.date_due',
                'tasks.is_active',
                'tasks.owner_id',
                'tasks.time_estimated',
                'tasks.time_spent'
            )
            ->eq('project_id', $project_id)
            ->neq('date_started', 0)
            ->neq('date_due', 0)
            ->orderBy('date_started', 'ASC')
            ->findAll();
    }
    
    private function generateFullGanttHTML($project, $tasks, $start_date, $end_date, $total_days, $texts, $hide_today = false, $actual_timeline_days = null)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $texts['gantt_chart'] . ': ' . htmlspecialchars($project['name']) . '</title>
    <style>
        @page { size: A4 landscape; margin: 0.5in; }
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .project-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .gantt-container { margin: 30px 0; border: 1px solid #ddd; position: relative; }
        .gantt-header { border-bottom: 1px solid #ddd; }
        .gantt-row { border-bottom: 1px solid #eee; padding: 8px 0; display: flex; align-items: center; min-height: 40px; }
        .task-name { width: 200px; padding: 0 10px; border-right: 1px solid #ddd; font-size: 11px; }
        .task-timeline { flex: 1; position: relative; height: 30px; margin: 0 10px; overflow: visible; }
        .task-bar { position: absolute; height: 25px; border-radius: 4px; color: white; font-size: 10px; font-weight: bold; display: flex; align-items: center; padding-left: 8px; }
        .task-bar.completed { background: #4CAF50; }
        .task-bar.active { background: #2196F3; }
        .task-bar.overdue { background: #F44336; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
        th { background: #f5f5f5; font-weight: bold; }
        .legend { margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
        .legend-item { display: inline-block; margin-right: 20px; }
        .legend-color { display: inline-block; width: 20px; height: 15px; margin-right: 5px; border-radius: 3px; }
        @media print { body { padding: 10px; } .gantt-container { page-break-after: always; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="project-title">' . $texts['gantt_chart'] . ': ' . htmlspecialchars($project['name']) . '</div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <span>' . $texts['export_date'] . ': ' . date('d.m.Y H:i') . '</span>
            <span>' . $texts['time_period'] . ': ' . date('d.m.Y', $start_date) . ' - ' . date('d.m.Y', $end_date) . '</span>
        </div>
        ' . $this->generateProjectSummary($tasks, $total_days, $texts) . '
    </div>
    
    <div class="gantt-container">
        <div class="gantt-header" style="display: flex;">
            <div style="width: 200px; padding: 10px; border-right: 1px solid #ddd; background: #f5f5f5; font-weight: bold;">' . $texts['task'] . '</div>
            <div style="flex: 1; padding: 10px; background: #f5f5f5; font-weight: bold; text-align: center;">' . $texts['timeline'] . '</div>
        </div>
        <div class="date-header" style="display: flex; border-bottom: 1px solid #ddd; background: #fafafa;">
            <div style="width: 200px; border-right: 1px solid #ddd; padding: 0 10px;"></div>
            <div style="flex: 1; position: relative; padding: 0 10px;">' . $this->generateDateMarkers($start_date, $end_date, $total_days, $texts, $hide_today, $actual_timeline_days) . '</div>
        </div>';
        
        foreach ($tasks as $task) {
            $html .= $this->generateTaskRow($task, $start_date, $total_days, $texts, $hide_today, $actual_timeline_days);
        }
        
        $html .= '</div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ganttContainer = document.querySelector(".gantt-container");
            var ganttHeight = ganttContainer.offsetHeight;
            var dateLines = document.querySelectorAll(".date-marker-line");
            var todayLines = document.querySelectorAll(".today-line");
            
            dateLines.forEach(function(line) {
                line.style.height = (ganttHeight - 58) + "px";
            });
            
            todayLines.forEach(function(line) {
                line.style.height = (ganttHeight - 65) + "px";
            });
        });
    </script>
    
    <table>
        <thead>
            <tr>
                <th>' . $texts['task_name'] . '</th>
                <th>' . $texts['start_date'] . '</th>
                <th>' . $texts['due_date'] . '</th>
                <th>' . $texts['duration'] . '</th>
                <th>' . $texts['assigned'] . '</th>
                <th>' . $texts['estimate'] . '</th>
                <th>' . $texts['time_spent'] . '</th>
                <th>' . $texts['status'] . '</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($tasks as $task) {
            $duration = ceil(($task['date_due'] - $task['date_started']) / 86400);
            $assignee = $texts['not_assigned'];
            if (!empty($task['owner_id'])) {
                $user = $this->userModel->getById($task['owner_id']);
                if (!empty($user['name'])) {
                    $assignee = $user['name'];
                }
            }
            $status = $task['is_active'] ? $texts['active'] : $texts['completed'];
            if ($task['is_active'] && $task['date_due'] < time()) {
                $status = $texts['overdue'];
            }
            
            // Display time values (Kanboard stores estimates in hours, time spent in seconds)
            $estimated_hours = !empty($task['time_estimated']) ? $task['time_estimated'] : '-';
            $spent_hours = !empty($task['time_spent']) ? round($task['time_spent'] / 3600, 1) : '-';
            
            $html .= '<tr>
                <td>' . htmlspecialchars($task['title']) . '</td>
                <td>' . date('d.m.Y', $task['date_started']) . '</td>
                <td>' . date('d.m.Y', $task['date_due']) . '</td>
                <td>' . $duration . ' ' . $texts['days'] . '</td>
                <td>' . htmlspecialchars($assignee) . '</td>
                <td>' . $estimated_hours . '</td>
                <td>' . $spent_hours . '</td>
                <td>' . $status . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>
    
    <div class="legend">
        <strong>' . $texts['explanation'] . ':</strong>
        <div class="legend-item">
            <span class="legend-color" style="background: #4CAF50;"></span>
            ' . $texts['completed_tasks'] . '
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #2196F3;"></span>
            ' . $texts['active_tasks'] . '
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #F44336;"></span>
            ' . $texts['overdue_tasks'] . '
        </div>
    </div>
    
</body>
</html>';
        
        return $html;
    }
    
    private function generateTaskRow($task, $start_date, $total_days, $texts, $hide_today = false, $actual_timeline_days = null)
    {
        $task_start = $task['date_started'];
        $task_end = $task['date_due'];
        
        $days_from_start = ($task_start - $start_date) / 86400;
        $task_duration = ($task_end - $task_start) / 86400;
        
        $left_percent = ($days_from_start / $total_days) * 100; // Use full width for task bars
        $width_percent = ($task_duration / $total_days) * 100;
        
        $status_class = 'active';
        if ($task['is_active'] == 0) {
            $status_class = 'completed';
        } elseif ($task_end < time()) {
            $status_class = 'overdue';
        }
        
        $task_name = $task['title'];
        
        // No today line in task rows - only in header
        $today_line = '';
        
        return '<div class="gantt-row">
            <div class="task-name">' . htmlspecialchars($task_name) . '</div>
            <div class="task-timeline">
                <div class="task-bar ' . $status_class . '" style="left: ' . $left_percent . '%; width: ' . $width_percent . '%; z-index: 3;" title="' . htmlspecialchars($task['title']) . '">
                    ' . round($task_duration, 1) . ' d
                </div>
                ' . $today_line . '
            </div>
        </div>';
    }
    
    private function calculateDynamicMarkers($start_date, $end_date, $total_days)
    {
        $markers = [];
        $actual_days = ceil(($end_date - $start_date) / 86400);
        
        if ($actual_days > 365) {
            // More than a year - show monthly markers (1st of each month)
            $current = mktime(0, 0, 0, date('n', $start_date), 1, date('Y', $start_date));
            if ($current < $start_date) {
                $current = mktime(0, 0, 0, date('n', $start_date) + 1, 1, date('Y', $start_date));
            }
            
            while ($current < $end_date) {
                if ($current > $start_date) {
                    $markers[] = [
                        'timestamp' => $current,
                        'label' => date('M Y', $current)
                    ];
                }
                $current = mktime(0, 0, 0, date('n', $current) + 1, 1, date('Y', $current));
            }
        } elseif ($actual_days > 30) {
            // More than a month - show weekly markers (Mondays)
            $current = $start_date;
            
            // Find first Monday after start date
            while (date('N', $current) != 1 && $current < $end_date) {
                $current += 86400;
            }
            
            while ($current < $end_date) {
                if ($current > $start_date) {
                    $markers[] = [
                        'timestamp' => $current,
                        'label' => date('j M', $current)
                    ];
                }
                $current += (7 * 86400); // Add 7 days for next Monday
            }
        } else {
            // 30 days or less - show daily markers (exclude start and end dates)
            $base_span_days = ceil(($end_date - $start_date) / 86400);
            for ($i = 1; $i < $base_span_days; $i++) {
                $marker_date = $start_date + ($i * 86400);
                $markers[] = [
                    'timestamp' => $marker_date,
                    'label' => date('j M', $marker_date)
                ];
            }
        }
        
        return $markers;
    }
    
    private function generateDateMarkers($start_date, $end_date, $total_days, $texts, $hide_today = false, $actual_timeline_days = null)
    {
        $html = '';
        
        // Add start date marker with line
        $html .= '<div style="position: absolute; left: 0%; top: 2px; font-size: 9px; color: #666; z-index: 2;">' . date('j M', $start_date) . '</div>';
        $html .= '<div class="date-marker-line" style="position: absolute; left: 0%; top: 20px; width: 1px; background: #e0e0e0; z-index: 1;"></div>';
        
        // Dynamic date markers based on timeline length
        $markers = $this->calculateDynamicMarkers($start_date, $end_date, $total_days);
        
        foreach ($markers as $marker) {
            $marker_date = $marker['timestamp'];
            $days_from_start = ($marker_date - $start_date) / 86400;
            $percent = ($days_from_start / $actual_timeline_days) * 100; // Use actual timeline days for proper spacing
            $date_label = $marker['label'];
            
            $html .= '<div style="position: absolute; left: ' . $percent . '%; top: 2px; font-size: 9px; color: #666; white-space: nowrap; text-align: center; transform: translateX(-50%); z-index: 2.">' . $date_label . '</div>';
            $html .= '<div class="date-marker-line" style="position: absolute; left: ' . $percent . '%; top: 20px; width: 1px; background: #e0e0e0; z-index: 1;"></div>';
        }
        
        // Add end date marker with line at integer day position (show day after end date)
        $base_span_days = ceil(($end_date - $start_date) / 86400);
        $end_percent = ($base_span_days / $actual_timeline_days) * 100;
        $day_after_end = $end_date + 86400; // Add one day
        $html .= '<div style="position: absolute; left: ' . $end_percent . '%; top: 2px; font-size: 9px; color: #666; transform: translateX(-50%); z-index: 2;">' . date('j M', $day_after_end) . '</div>';
        $html .= '<div class="date-marker-line" style="position: absolute; left: ' . $end_percent . '%; top: 20px; width: 1px; background: #e0e0e0; z-index: 1;"></div>';
        
        // Add blank marker line one day after end date (no text)
        $blank_percent = (($base_span_days + 1) / $actual_timeline_days) * 100;
        $html .= '<div class="date-marker-line" style="position: absolute; left: ' . $blank_percent . '%; top: 20px; width: 1px; background: #e0e0e0; z-index: 1;"></div>';
        
        // Add today indicator line if today is within the timeline and not hidden
        if (!$hide_today) {
            $today = time();
            if ($today >= $start_date && $today <= $end_date) {
                $days_from_start_today = ($today - $start_date) / 86400;
                $today_percent = ($days_from_start_today / $actual_timeline_days) * 100; // Use actual timeline days for proper positioning
                $html .= '<div class="today-line" style="position: absolute; left: ' . $today_percent . '%; top: 26px; width: 2px; border-left: 2px dashed #000; z-index: 10;"></div>';
                $html .= '<div style="position: absolute; left: ' . $today_percent . '%; bottom: 2px; font-size: 8px; color: #000; font-weight: bold; transform: translateX(-50%); background: white; padding: 1px 3px; border: 1px solid #000; z-index: 11;">' . $texts['today'] . '</div>';
            }
        }
        
        return '<div style="position: relative; height: 25px; padding: 5px 0; border-bottom: 1px solid #ccc; background: #fafafa;">' . $html . '</div>';
    }
    
    private function generateProjectSummary($tasks, $total_days, $texts)
    {
        $total_tasks = count($tasks);
        $completed_tasks = 0;
        $overdue_tasks = 0;
        $active_tasks = 0;
        
        foreach ($tasks as $task) {
            if ($task['is_active'] == 0) {
                $completed_tasks++;
            } elseif ($task['date_due'] < time()) {
                $overdue_tasks++;
            } else {
                $active_tasks++;
            }
        }
        
        $completion_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 1) : 0;
        
        return '<div style="background: #f8f9fa; padding: 12px; border-radius: 5px; margin: 10px 0; border: 1px solid #dee2e6;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 30px;">
                    <span><strong>' . $texts['total_tasks'] . ':</strong> ' . $total_tasks . '</span>
                    <span style="color: #4CAF50;"><strong>' . $texts['completed'] . ':</strong> ' . $completed_tasks . '</span>
                    <span style="color: #2196F3;"><strong>' . $texts['active'] . ':</strong> ' . $active_tasks . '</span>
                    <span style="color: #F44336;"><strong>' . $texts['overdue'] . ':</strong> ' . $overdue_tasks . '</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span><strong>' . $texts['progress'] . ':</strong></span>
                    <div style="width: 100px; height: 20px; background: #e0e0e0; border-radius: 10px; overflow: hidden;">
                        <div style="width: ' . $completion_percentage . '%; height: 100%; background: #4CAF50; border-radius: 10px;"></div>
                    </div>
                    <span><strong>' . $completion_percentage . '%</strong></span>
                </div>
            </div>
        </div>';
    }
}