# Enhanced Leads Quota Management System

## Overview

The Enhanced Leads Quota Management System provides comprehensive quota tracking, automatic rollover functionality, and detailed reporting capabilities for both administrators and SDRs (Sales Development Representatives).

## Key Features

### 1. Automatic Quota Rollover
- **Daily Processing**: Incomplete quotas automatically roll over to the next day
- **Progressive Accumulation**: Uncompleted leads keep accumulating until completed
- **Audit Logging**: Complete tracking of rollover activities in `quota_logs` table
- **Manual Processing**: Admin can manually trigger rollover processing

### 2. Enhanced SDR Experience
- **Real-time Progress Tracking**: Visual progress bars and completion percentages
- **Status Management**: Direct status updates from quota view
- **Column Customization**: Personalized column visibility settings
- **Filtering & Navigation**: Date and status-based filtering
- **Quota History**: Historical view of quota performance

### 3. Comprehensive Admin Tools
- **Rollover Management**: Manual rollover processing controls
- **Detailed Reports**: Analytics and performance insights
- **Export Functionality**: CSV export for external analysis
- **Performance Analytics**: Top performers and improvement areas

### 4. Advanced Reporting System
- **Multi-dimensional Reports**: Filter by date range, SDR, and status
- **Performance Metrics**: Completion rates, carry-forward tracking
- **Visual Analytics**: Progress bars, completion percentages
- **Export Capabilities**: CSV download for further analysis

## Database Schema Enhancements

### Existing Tables (No Changes Required)
- `leads_quota`: Main quota assignments
- `lead_quota_assignments`: Individual lead assignments
- `quota_logs`: Rollover and audit tracking

### Key Relationships
- `leads_quota` → `users` (SDR assignments)
- `leads_quota` → `status` (status-based quotas)
- `lead_quota_assignments` → `leads` (individual lead tracking)
- `quota_logs` → `users` & `status` (audit trail)

## File Structure

### Models
- `app/models/LeadsQuotaModel.php` - Enhanced with rollover logic and reporting

### Controllers
- `app/controllers/LeadsQuotaController.php` - New methods for rollover and reporting

### Views
- `app/views/leads_quota/sdr_view.php` - Enhanced SDR interface
- `app/views/leads_quota/manage.php` - Enhanced admin management
- `app/views/leads_quota/history.php` - SDR quota history
- `app/views/leads_quota/reports.php` - Admin reporting dashboard

### Automation
- `cron/process_quota_rollover.php` - Daily rollover processing script

## New Controller Actions

### Admin Actions
- `leads_quota_process_rollover` - Manual rollover processing
- `leads_quota_reports` - Comprehensive reporting dashboard
- `export_quota_report` - CSV export functionality

### SDR Actions
- `leads_quota_history` - Personal quota history
- `leads_quota_update_lead_status` - Status updates from quota view

### AJAX Endpoints
- `get_statuses` - Status dropdown data
- `get_quota_stats` - Real-time quota statistics

## Usage Guide

### For Administrators

#### 1. Managing Quotas
- Navigate to "Manage Leads Quotas"
- Use date filters to view specific periods
- Process rollovers manually using the "Process Rollover" button
- View carry-forward information in the quotas table

#### 2. Generating Reports
- Access "Reports" from the quota management page
- Filter by date range and specific SDRs
- Export data to CSV for external analysis
- Review performance analytics and improvement areas

#### 3. Setting Up Automation
- Configure cron job to run `cron/process_quota_rollover.php` daily
- Monitor rollover logs in `cron/quota_rollover.log`
- Set up email notifications for rollover activities

### For SDRs

#### 1. Viewing Quotas
- Access "My Assigned Leads Quota" from dashboard
- Use filters to navigate between dates and statuses
- View progress bars and completion percentages
- See carry-forward information for rolled-over leads

#### 2. Managing Leads
- Click "View Leads" to see assigned leads for a status
- Use "Update Status" to change lead status and mark as completed
- Customize column visibility using the "Columns" button
- Track completion progress in real-time

#### 3. Historical Analysis
- Access "Quota History" to view past performance
- Filter by date ranges to analyze trends
- Review completion rates and carry-forward patterns

## Technical Implementation

### Rollover Logic
```php
// Process daily rollover
$rolloverCount = $quotaModel->processDailyRollover($date);

// Key steps:
// 1. Find incomplete quotas from previous day
// 2. Calculate remaining leads
// 3. Create or update quotas for current day
// 4. Assign incomplete leads to new quotas
// 5. Log rollover activities
```

### Progress Tracking
```php
// Calculate completion percentage
$percentage = $quota['quota_count'] > 0 ? 
    ($quota['completed_leads'] / $quota['quota_count']) * 100 : 0;

// Determine progress bar color
$progressClass = $percentage >= 100 ? 'bg-success' : 
    ($percentage >= 75 ? 'bg-info' : 
    ($percentage >= 50 ? 'bg-warning' : 'bg-danger'));
```

### Column Management
```javascript
// Save column preferences
function saveColumnsSelection(keys) {
    setCookie('quota_columns', JSON.stringify(keys), 30);
}

// Apply column visibility
function applyColumns(keys) {
    const show = new Set(keys);
    // Toggle header and body cells based on selection
}
```

## Configuration

### Cron Job Setup
```bash
# Add to crontab for daily execution at 1:00 AM
0 1 * * * /usr/bin/php /path/to/LeadManager/cron/process_quota_rollover.php
```

### Log Monitoring
- Rollover logs: `cron/quota_rollover.log`
- Application logs: Standard PHP error logs
- Database logs: MySQL/MariaDB logs

## Security Considerations

### Access Control
- All actions require appropriate role permissions
- SDRs can only access their own quota data
- Admins have full access to all quota management functions

### Data Validation
- Input sanitization for all user inputs
- SQL injection prevention through prepared statements
- XSS protection through proper output escaping

### Audit Trail
- Complete logging of rollover activities
- User action tracking in quota assignments
- Timestamp tracking for all quota changes

## Performance Optimization

### Database Indexes
- Optimized indexes on `user_id`, `status_id`, and `assigned_date`
- Composite indexes for common query patterns
- Proper foreign key constraints for data integrity

### Caching Strategy
- Column preferences cached in browser cookies
- Session-based user data caching
- Query result caching for frequently accessed reports

### Pagination
- Implemented pagination for large lead lists
- Configurable page sizes for different views
- Efficient offset-based pagination

## Troubleshooting

### Common Issues

#### 1. Rollover Not Processing
- Check cron job configuration
- Verify file permissions on rollover script
- Review rollover logs for error messages

#### 2. Quota Display Issues
- Clear browser cache and cookies
- Check database connection
- Verify user permissions

#### 3. Performance Issues
- Monitor database query performance
- Check for missing indexes
- Review server resource usage

### Debug Mode
```php
// Enable debug logging in rollover script
define('DEBUG_MODE', true);
logMessage("Debug: Processing quota ID {$quotaId}");
```

## Future Enhancements

### Planned Features
1. **Email Notifications**: Automated alerts for quota milestones
2. **Mobile Responsiveness**: Enhanced mobile interface
3. **Advanced Analytics**: Trend analysis and forecasting
4. **Integration APIs**: REST API for external system integration
5. **Bulk Operations**: Mass quota assignments and updates

### Scalability Considerations
- Database partitioning for large datasets
- Redis caching for high-traffic scenarios
- Load balancing for multiple server deployments
- Microservices architecture for complex workflows

## Support and Maintenance

### Regular Maintenance Tasks
1. **Daily**: Monitor rollover processing logs
2. **Weekly**: Review quota performance reports
3. **Monthly**: Analyze completion trends and adjust quotas
4. **Quarterly**: Performance optimization and cleanup

### Backup Strategy
- Regular database backups including quota data
- Configuration file backups
- Log file archival and rotation

### Monitoring
- Set up alerts for rollover failures
- Monitor quota completion rates
- Track system performance metrics
- User activity monitoring

---

## Conclusion

The Enhanced Leads Quota Management System provides a comprehensive solution for quota tracking, automatic rollover, and detailed reporting. The system is designed to be scalable, maintainable, and user-friendly while providing powerful analytics and automation capabilities.

For technical support or feature requests, please refer to the development team or system administrator.
