# Getting Started with EMP

## Introduction

This guide helps new users, managers, and admins quickly start using EMP (Employee Max Portal), developed by [SOHUB (Solution Hub Technologies)](https://sohub.com.bd/). It covers installation and basic usage.

## Installation

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- 2GB RAM minimum

### Quick Installation (3 Steps)

**Step 1: Clone Repository**
```bash
git clone https://github.com/sohub23/employeemaxportal.git
cd employeemaxportal
```

**Step 2: Import Database**
```bash
mysql -u username -p database_name < sql/emp.sql
```

**Step 3: Configure Database**
```bash
cp application/config/database.php.example application/config/database.php
# Edit database.php with your database credentials
```

That's it! Access your EMP installation at your domain.

## 1. Users – Step by Step

### 1.1 Login
- Go to EMP portal URL
- Enter credentials (username/email + password)
- Forgot password? Click Reset Password link

### 1.2 Dashboard Overview
- **Tasks Section**: Shows assigned tasks with status (Pending, In Progress, Completed)
- **Notifications**: Alerts for approvals, deadlines, or updates
- **Goals/Performance**: Track personal KPIs and goal progress
- **Quick Links**: Access leave requests, reports, and team updates

### 1.3 Task Management
- Open assigned task → update status → add comments if needed
- Completed tasks are automatically logged in reporting
- Attach supporting files if required

### 1.4 Leave & Attendance
- Submit leave request → Manager approval required
- Attendance automatically tracked via system (if integrated)
- View leave balance and history

### 1.5 Notifications & Alerts
- System sends proactive reminders for upcoming deadlines
- Alerts for pending approvals and overdue tasks
- Customize alert preferences in settings

## 2. Admins / Managers – Step by Step

### 2.1 Organization Setup
- Configure company name, branches, departments
- Add roles and assign employees to proper departments

### 2.2 Workflow & Policy Configuration
- Define workflows for task approvals, leave, payroll, and reporting
- Set organizational policies (attendance rules, escalation rules)

### 2.3 User Management
- Add or remove employees
- Assign roles: Employee, Manager, Admin
- Configure access permissions based on role

### 2.4 Task & Performance Monitoring
- Monitor tasks assigned across teams
- Approve or reject leave requests
- Track KPIs, goals, and departmental performance
- Generate auto-reports for payroll, HR, and operations

### 2.5 Notifications & Alerts Management
- Configure system-wide alerts for deadlines, approvals, and escalations
- Ensure managers receive proper notifications for pending actions

### 2.6 Reporting & Insights
- Access real-time reports generated from actual work activity
- Track employee performance, department KPIs, and operational metrics
- Export reports in CSV/PDF formats for audits or meetings

## 3. Tips for Smooth Start

- Bookmark dashboard for easy access
- Regularly check notifications to avoid overdue tasks
- Review assigned roles & permissions to ensure accurate access
- Use demo login (if available) to explore EMP features before full deployment