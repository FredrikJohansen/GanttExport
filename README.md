# Kanboard Gantt Export Plugin

Exports Gantt charts to HTML format for PDF printing.

Examples with and without "TODAY" indicator
<img width="1280" height="720" alt="GE111" src="https://github.com/user-attachments/assets/caf3f3c2-e7b0-48b3-9c7d-24b607ffeb3b" />
<img width="1280" height="720" alt="GE111T" src="https://github.com/user-attachments/assets/d48498bd-7f07-44a3-9e1b-c2ed74d0db2f" />



## Features

- Visual Gantt timeline with task bars
- Dynamic date markers (daily/weekly/monthly based on timeline length)
- Optional today indicator line
- Project summary with task statistics
- Time tracking information (estimated vs actual hours)
- Task details table
- Multilingual support (Norwegian/English)
- Two export options (with or without today line)

## Requirements

- Kanboard >= 1.0.37

## Installation

1. Extract to your Kanboard `plugins/` directory as `GanttExport`

## Usage

1. Navigate to any project
2. Open the project dropdown menu
3. Select "Export Gantt Chart" or "Export Gantt Chart (No Today Line)"
4. Use browser print function (Ctrl+P) → Save as PDF → Landscape orientation → Enable "Background Graphics"

## Export Includes

- Visual timeline with task bars
- Project summary with task count and progress
- Task table with names, dates, duration, assignees, time estimates, and status
- Color legend (Green: completed, Blue: active, Red: overdue)

## Language Support

- Norwegian: Used for Norwegian language users
- English: Default for all other languages

## Version

1.1.1


## Changelog v1.0.0 → v1.1.0

  Improvements:
  - Fixed date marker spacing to ensure exactly one day gaps between consecutive dates
  - Added toggleable today line with two export options (with/without today indicator)
  - Extended timeline with visual padding after end date for better appearance
  - Improved today line positioning to prevent overlap with text labels
  - Added dynamic line height calculation that adapts to number of tasks
  - Enhanced date calculations for consistent positioning across all timeline elements

  Fixes:
  - Corrected end date positioning and duplicate date labels
  - Added JavaScript for responsive line height adjustment

  The main focus was fixing visual spacing issues and adding functionality to export with or without a today indactor line.


## Changelog v1.1.0 → v1.1.1

  Fixes:
  - Fixed task bar alignment - task bars now align perfectly with date markers on the timeline
  - Fixed page break issues when printing to PDF - tasks will no longer be split across multiple pages


---
  This project was developed with assistance from AI tools
