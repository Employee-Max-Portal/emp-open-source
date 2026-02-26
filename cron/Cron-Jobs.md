# Replace {YOUR_DOMAIN} with your actual domain (e.g., https://yourdomain.com or http://localhost/emp-opensource)

#############
# RDC reminder – every 30 min
*/30 * * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/rdc_reminder" >> /var/log/rdc_reminder.log 2>&1

# Check escalation tasks – every 30 min
*/30 * * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/check_escalation_tasks" >> /var/log/check_escalation_tasks.log 2>&1

# Send RPS emails – every 30 min
*/30 * * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/send_rps_emails" >> /var/log/send_rps_emails.log 2>&1

# Check late completed tasks – every 30 min
*/30 * * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/check_late_completed_tasks" >> /var/log/check_late_completed_tasks.log 2>&1

# Send event emails – every 30 min
*/30 * * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/send_event_emails" >> /var/log/send_event_emails.log 2>&1

#############
# Create recurring RDC tasks – 10:00 AM
0 10 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/create_recurring_rdc_tasks" >> /var/log/create_recurring_rdc_tasks.log 2>&1

# Send warning emails – 10:03 AM
3 10 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/send_warning_emails" >> /var/log/send_warning_emails.log 2>&1

# Send warning reminder emails – 10:06 AM
6 10 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/send_warning_reminder_emails" >> /var/log/send_warning_reminder_emails.log 2>&1

# Send separation emails – 10:09 AM
9 10 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/send_separation_emails" >> /var/log/send_separation_emails.log 2>&1

# Update overdue milestones – 10:12 AM
12 10 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/update_overdue_milestones" >> /var/log/update_overdue_milestones.log 2>&1

# Milestone due reminders – 10:15 AM
15 10 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/milestone_due_reminders" >> /var/log/milestone_due_reminders.log 2>&1

# Probation reminder – 10:18 AM
18 10 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/probation_reminder" >> /var/log/probation_reminder.log 2>&1


##########
# Work summary reminder – 9:00 PM
0 21 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/work_summary_reminder" >> /var/log/work_summary_reminder.log 2>&1

# Work summary follow-up – 11:00 PM
0 23 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/work_summary_followup" >> /var/log/work_summary_followup.log 2>&1

# Absence TODO check – 12:00 PM
0 12 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/absence_todo_check" >> /var/log/absence_todo_check.log 2>&1

# Remind goal tasks today – 3:15 PM
15 15 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/remind_goal_tasks_today" >> /var/log/remind_goal_tasks_today.log 2>&1

# Check daily goal tasks – 11:00 PM
0 23 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/check_daily_goal_tasks" >> /var/log/check_daily_goal_tasks.log 2>&1

0 22 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/send_todays_task_status" >> /var/log/send_todays_task_status.log 2>&1

##########
# Create monthly revenue share – 1st day of month, 2:00 AM
0 2 1 * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/create_monthly_revenue_share" >> /var/log/create_monthly_revenue_share.log 2>&1

# Update existing leave balances for non-regulars – daily midnight
0 0 * * * /usr/bin/curl -s "{YOUR_DOMAIN}/api/update_existing_leave_balances_for_non_regulars" >> /var/log/update_existing_leave_balances_for_non_regulars.log 2>&1

#########
# Manual leave balance reset – 1st Jan, 2:00 AM
0 2 1 1 * /usr/bin/curl -s "{YOUR_DOMAIN}/api/manual_leave_balance_reset" >> /var/log/manual_leave_balance_reset.log 2>&1