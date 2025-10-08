# CRM Lead Manager

A comprehensive Customer Relationship Management (CRM) system built with CorePHP and MySQL, featuring a clean and responsive Bootstrap dashboard.

## Features

### 🔐 User Authentication & Roles
- **Admin**: Full control over users, leads, and system settings
- **Manager**: Can view all leads, analytics, and team performance
- **SDR**: Can view/add/edit their own leads only

### 📊 Lead Management
- **Lead Fields**: Name, Company, Email, Phone, LinkedIn, Website, Clutch, SDR Name, Lead ID, Duplicate Status, Notes
- **Auto Lead ID Generation**: Format SDR{ID}-00001 (dynamic based on SDR)
- **Duplicate Detection**: Automatic detection based on email, phone, LinkedIn, website, or Clutch
- **Status Icons**: ✅ Unique, 🔁 Duplicate, ⚠️ Incomplete
- **CRUD Operations**: Add, Edit, Delete, View leads

### 📈 Dashboard & Analytics
- **Summary Cards**: Total leads, unique, duplicates, incomplete
- **Recent Leads**: Latest lead entries
- **Recent Activity**: Lead notes and updates
- **Team Performance**: For managers and admins

### 🔍 Search & Filtering
- **Global Search**: Search by any field (email, company, website, etc.)
- **Advanced Filters**: By SDR, duplicate status, date range
- **Real-time Results**: Instant search results

### 📁 Import/Export
- **CSV/Excel Import**: Bulk import leads with auto ID generation and duplicate check
- **CSV/Excel Export**: Export leads with filtering
- **Sample Templates**: Provided CSV sample for easy import

### 📝 Lead Activity & Notes
- **Activity Tracking**: Log calls, emails, updates, and notes
- **Activity History**: Visible on lead detail pages
- **User Attribution**: Track who made each activity

## Installation

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone/Download** the project to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\LeadManager\
   ```

2. **Start XAMPP** services (Apache and MySQL)

3. **Run Setup Script**:
   ```
   http://localhost/LeadManager/setup.php
   ```
   This will:
   - Create the database and tables
   - Insert demo users
   - Add sample leads and notes

4. **Access the Application**:
   ```
   http://localhost/LeadManager/
   ```

### Demo Accounts
- **Admin**: `admin` / `admin123`
- **Manager**: `manager` / `manager123`
- **SDR**: `sdr` / `sdr123`

## Project Structure

```
LeadManager/
├── README.md
├── setup.php                   # Database setup script
├── dbschema.sql               # Database schema
├── index.php                  # Main entry point
├── core/                      # Core framework files
│   ├── Database.php
│   ├── Controller.php
│   └── Model.php
├── app/                       # Application files
│   ├── config.php            # Configuration
│   ├── helpers.php           # Helper functions
│   ├── controllers/          # Controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── LeadController.php
│   │   ├── ImportController.php
│   │   ├── NoteController.php
│   │   └── UserController.php
│   ├── models/               # Data models
│   │   ├── BaseModel.php
│   │   ├── UserModel.php
│   │   ├── LeadModel.php
│   │   └── NoteModel.php
│   └── views/                # View templates
│       ├── layout/
│       │   ├── header.php
│       │   └── footer.php
│       ├── auth/
│       │   └── login.php
│       ├── dashboard/
│       │   └── home.php
│       ├── leads/
│       │   ├── index.php
│       │   ├── form.php
│       │   ├── view.php
│       │   └── import.php
│       ├── user/
│       │   ├── index.php
│       │   └── form.php
│       └── errors/
│           └── 404.php
├── public/                    # Public assets
│   └── assets/
│       ├── css/
│       │   └── app.css
│       └── js/
└── tools/                     # Utility files
    └── csv_sample.csv
```

## Database Schema

### Users Table
- `id` (Primary Key)
- `username` (Unique)
- `email` (Unique)
- `password` (Bcrypt hashed)
- `full_name`
- `role` (admin/manager/sdr)
- `created_at`, `updated_at`

### Leads Table
- `id` (Primary Key)
- `lead_id` (Unique, auto-generated)
- `name`, `company`, `email`, `phone`
- `linkedin`, `website`, `clutch`
- `sdr_id` (Foreign Key to users)
- `duplicate_status` (unique/duplicate/incomplete)
- `notes`
- `created_by` (Foreign Key to users)
- `created_at`, `updated_at`

### Lead Notes Table
- `id` (Primary Key)
- `lead_id` (Foreign Key to leads)
- `user_id` (Foreign Key to users)
- `type` (call/email/update/note)
- `content`
- `created_at`

## Key Features Explained

### Auto Lead ID Generation
- Format: `SDR{ID}-{SEQUENCE}`
- Example: `SDR3-00001`, `SDR3-00002`
- Automatically increments for each SDR

### Duplicate Detection
- Checks: email, phone, LinkedIn, website, Clutch
- Status: Unique ✅, Duplicate 🔁, Incomplete ⚠️
- Real-time detection during import and manual entry

### Role-Based Permissions
- **SDR**: Own leads only
- **Manager**: All leads + team analytics
- **Admin**: Full system access + user management

### Security Features
- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- Role-based access control
- Session management

## Usage

### Adding Leads
1. Navigate to "Leads" → "Add New Lead"
2. Fill in lead information
3. Lead ID is auto-generated
4. Duplicate status is automatically detected

### Importing Leads
1. Go to "Import/Export" page
2. Download sample CSV template
3. Prepare your CSV with lead data
4. Upload and import
5. System will auto-generate IDs and detect duplicates

### Managing Users (Admin Only)
1. Navigate to "Users" menu
2. Add/edit/delete users
3. Assign appropriate roles

### Adding Notes
1. View any lead
2. Use "Add Note" section
3. Select note type (call/email/update/note)
4. Add content and save

## Customization

### Adding New Fields
1. Update database schema in `dbschema.sql`
2. Modify `LeadModel.php` for data handling
3. Update form views in `app/views/leads/`
4. Update import/export functionality

### Styling
- Main styles: `public/assets/css/app.css`
- Bootstrap 5.3.0 via CDN
- Font Awesome 6.0.0 for icons
- Custom CSS variables for theming

### Extending Functionality
- Add new controllers in `app/controllers/`
- Create models in `app/models/`
- Add views in `app/views/`
- Update routing in `index.php`

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL is running
   - Verify credentials in `app/config.php`

2. **Permission Denied**
   - Ensure proper file permissions
   - Check XAMPP Apache is running

3. **Import Not Working**
   - Check file format (CSV/Excel)
   - Verify column headers match sample
   - Check file size limits

4. **Login Issues**
   - Run `setup.php` to create demo accounts
   - Check database connection

## Future Enhancements

- Email integration
- WhatsApp integration
- Task/reminder system
- Advanced reporting
- API endpoints
- Mobile app support
- Real-time notifications

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify all prerequisites are met
3. Ensure proper file permissions
4. Check XAMPP services are running

## License

This project is open source and available under the MIT License.