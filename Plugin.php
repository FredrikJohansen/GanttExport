<?php

namespace Kanboard\Plugin\GanttExport;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        $this->route->addRoute('gantt-export/:project_id', 'GanttExportController', 'export', 'plugin');
        
        $this->projectAccessMap->add('GanttExportController', '*', Role::PROJECT_VIEWER);
        
        $this->template->hook->attach('template:project:dropdown', 'ganttExport:project/dropdown');
        
        $this->helper->register('ganttExportHelper', '\Kanboard\Plugin\GanttExport\Helper\GanttExportHelper');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'Gantt Export';
    }

    public function getPluginDescription()
    {
        return t('Export Gantt charts with visual representation');
    }

    public function getPluginAuthor()
    {
        return 'Claude Code Assistant';
    }

    public function getPluginVersion()
    {
        return '1.1.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/claude-code/kanboard-gantt-export';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }

    public function getClasses()
    {
        return array(
            'Plugin\GanttExport\Controller' => array(
                'GanttExportController',
            ),
            'Plugin\GanttExport\Model' => array(
                'GanttExportModel',
            ),
            'Plugin\GanttExport\Helper' => array(
                'GanttExportHelper',
            )
        );
    }
}