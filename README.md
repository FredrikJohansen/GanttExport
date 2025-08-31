# Kanboard Gantt Export Plugin

A Kanboard plugin that exports Gantt charts to HTML format for PDF printing with professional visual representation.

## Features

- Visual Gantt timeline with proportional task bars
- Today indicator with dotted line
- Project summary with task statistics and progress bar
- Time tracking information (estimated vs actual hours)
- Dynamic timeline markers (daily/weekly/monthly based on timeline length)
- Multilingual support (Norwegian/English based on user language)
- Color-coded task status (completed, active, overdue)
- Comprehensive task details table
- Print-optimized layout

## Requirements

- Kanboard >= 1.0.37

## Installation

1. Extract to your Kanboard `plugins` directory as `GanttExport`

## Usage

1. Navigate to any project with Gantt view
2. Click project dropdown menu and select "Export Gantt Chart"
3. Use browser print function (Ctrl+P) → "Print to PDF" → Landscape orientation

## Language Support

- **Norwegian**: Automatically used for Norwegian language users
- **English**: Default for all other languages
- Language detection based on user's Kanboard language setting

## Export Includes

- **Visual Timeline**: Adaptive date markers with proportional task bars and today indicator
- **Project Summary**: Task count, completion percentage, and progress statistics  
- **Task Table**: Names, dates, duration, assignees, time estimates, and status
- **Legend**: Color coding explanation

## Color Coding

- **Green**: Completed tasks
- **Blue**: Active tasks
- **Red**: Overdue tasks

## Version

1.0.0 - Professional Gantt export with multilingual support
