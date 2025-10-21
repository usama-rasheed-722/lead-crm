# Custom Dynamic Fields for Status - Feature Implementation

This document describes the implementation of custom dynamic fields for status management in the Lead Manager system.

## Features Implemented

### 1. Bulk Status Update Restriction
- Added a checkbox option to restrict bulk status updates for specific statuses
- When enabled, users cannot change to that status using bulk update operations
- Users must update leads individually to change to restricted statuses

### 2. Default Status Selection
- Added ability to set a default status for new leads
- Only one status can be the default at any time
- When creating new leads, the default status is automatically selected
- Quick action button in status management to set any status as default

### 3. Custom Dynamic Fields for Status
- Created a new table `status_custom_fields` to store custom fields related to each status
- Fields are dynamic and can be defined by admins/users
- Supported field types:
  - Text
  - Textarea
  - Select dropdown
  - Date
  - Number
  - Email
  - URL
- Fields can be marked as required or optional
- Fields have a display order for consistent presentation

### 4. Lead Screen Integration
- When a user changes the status of a lead, corresponding custom fields dynamically appear
- Users must fill in required fields before completing the status change
- Fields are validated on the server side
- AJAX-powered dynamic field loading for better user experience
- Quick status change button in lead view header for easy access
- Both sidebar form and modal popup for status changes

### 5. Status Change History
- Custom field data is stored as JSON in the `contact_status_history` table
- History is viewable on the lead view screen
- Shows previous status changes along with associated custom field values
- Expandable/collapsible custom field data display

### 6. Enhanced Bulk Update Functionality
- Bulk update modal now shows custom fields when a status is selected
- Bulk-restricted statuses are automatically hidden from bulk update options
- Required field validation for bulk updates
- Same custom field functionality as individual lead updates
- Improved user experience with larger modal and better field organization

### 7. Visual Enhancements and User Experience
- Status dropdowns show 📝 icon for statuses that have custom fields
- Loading indicators when fetching custom fields
- Enhanced styling for custom field containers with visual borders
- Clear messaging when no custom fields are required
- Error handling with user-friendly messages
- Required field indicators with asterisks and help text

### 8. Full-Page Status History
- Dedicated full-page view for status history
- Enhanced table layout with better formatting
- Expandable custom fields data display
- Quick status change functionality from history page
- Lead information summary and quick actions
- Navigation links between lead view and history

### 9. Enhanced Bulk Update Modal
- Redesigned modal with professional layout
- Selected leads summary with detailed information
- Status information panel with field requirements
- Additional options for notes and tags
- Larger modal size (modal-xl) for better organization
- Visual indicators and improved user guidance

## Database Changes

### New Tables
1. **status_custom_fields**
   - Stores custom field definitions for each status
   - Fields: id, status_id, field_name, field_label, field_type, field_options, is_required, field_order, created_at

### Modified Tables
1. **status**
   - Added: `restrict_bulk_update` (BOOLEAN) - restricts bulk updates to this status
   - Added: `is_default` (BOOLEAN) - marks this status as the default for new leads

2. **contact_status_history**
   - Added: `custom_fields_data` (JSON) - stores custom field values for each status change

## File Changes

### Models
- **StatusModel.php**: Added methods for custom field management and bulk update restrictions
- **LeadModel.php**: Updated to handle custom fields data in status change logging

### Controllers
- **LeadController.php**: Added methods for status updates with custom fields and AJAX endpoints
- **StatusController.php**: Added methods for custom field CRUD operations

### Views
- **leads/view.php**: Added dynamic status change form with custom fields
- **status/form.php**: Added custom field management interface

### Database
- **dbschema.sql**: Updated with new tables and columns
- **migration_custom_fields.sql**: Migration script for existing databases

## Usage Instructions

### For Administrators

1. **Setting up Custom Fields**:
   - Go to Status Management
   - Edit any status
   - Use the "Add Field" button to create custom fields
   - Configure field type, label, requirements, and display order

2. **Setting Default Status**:
   - When creating or editing a status, check "Set as Default Status"
   - Or use the star button in the status management list to quickly set a status as default
   - Only one status can be the default at any time

3. **Restricting Bulk Updates**:
   - When creating or editing a status, check "Restrict Bulk Status Updates"
   - This prevents users from changing to this status via bulk operations

### For Users

1. **Changing Lead Status**:
   - Go to any lead's detail page
   - Use the "Change Status" button in the header or the sidebar form
   - Select a new status
   - Fill in any required custom fields that appear
   - Submit the form

2. **Bulk Status Updates**:
   - Go to Leads Management page
   - Select multiple leads using checkboxes
   - Click "Bulk Update Status" button
   - Select a status (only bulk-allowed statuses are shown)
   - Fill in any required custom fields
   - Submit the form

3. **Viewing Status History**:
   - On the lead detail page, scroll to "Status History" or click "Full Status History" button
   - Click on "Custom Fields (X)" links to view field data from previous changes
   - Use the full-page status history for better viewing and management

4. **Enhanced Bulk Updates**:
   - Select multiple leads using checkboxes
   - Click "Bulk Update Status" to open the enhanced modal
   - View selected leads summary and status information
   - Fill in custom fields and additional options as needed
   - Submit to update all selected leads at once

## API Endpoints

### AJAX Endpoints
- `GET index.php?action=get_custom_fields_for_status&status={status_name}`: Returns custom fields for a status as JSON

### Form Actions
- `POST index.php?action=update_status_with_custom_fields`: Updates lead status with custom field data
- `POST index.php?action=bulk_update_status_with_custom_fields`: Bulk updates multiple leads with custom field data
- `POST index.php?action=create_custom_field`: Creates a new custom field
- `POST index.php?action=update_custom_field&id={field_id}`: Updates an existing custom field
- `GET index.php?action=delete_custom_field&id={field_id}`: Deletes a custom field
- `GET index.php?action=set_status_as_default&id={status_id}`: Sets a status as the default (AJAX)
- `GET index.php?action=lead_status_history&id={lead_id}`: View full-page status history for a lead

## Migration Instructions

1. **For New Installations**:
   - Use the updated `dbschema.sql` file

2. **For Existing Installations**:
   - Run the `migration_custom_fields.sql` script
   - This will add the new columns and tables without affecting existing data

## Technical Notes

### Field Types and Validation
- **Text**: Standard text input with length validation
- **Textarea**: Multi-line text input
- **Select**: Dropdown with options defined in field_options (one per line)
- **Date**: HTML5 date picker
- **Number**: Numeric input with validation
- **Email**: Email input with format validation
- **URL**: URL input with format validation

### Data Storage
- Custom field values are stored as JSON in the `custom_fields_data` column
- JSON structure: `{"field_name": "value", "field_name2": "value2"}`
- This allows for flexible field addition without schema changes

### Security Considerations
- All custom field operations require admin role
- Input validation is performed on both client and server side
- SQL injection protection through prepared statements
- XSS protection through proper HTML escaping

## Future Enhancements

Potential improvements for future versions:
1. Field validation rules (min/max length, regex patterns)
2. Conditional field display based on other field values
3. Field templates for common field sets
4. Import/export of custom field configurations
5. Field-level permissions (who can see/edit specific fields)
6. Field dependencies and cascading options
