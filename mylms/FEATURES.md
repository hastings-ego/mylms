# Fun Maths Mastery - New Features Setup Guide

## ✅ What Was Fixed

### 1. Upload Directory Permissions
- Fixed image and PDF upload failures by setting proper write permissions
- Directories: `/app/uploads/products/` and `/app/uploads/products/images/`
- Permission level: 777 (world-writable) for web server compatibility

## 🎓 New Features Implemented

### 1. Production-Ready Student Dashboard (`/student-dashboard.php`)

A modern, tab-based dashboard replacing the old dashboard for students:

**Tabs:**
- **Classes** - Enroll in live classes, view enrolled classes, discover new classes
- **Materials** - View purchased products/courses with downloads
- **Settings** - Update profile, change password
- **Support** - Submit support tickets, view ticket history

**Features:**
- Real-time class enrollment/unenrollment
- Class statistics (enrolled, available, support tickets)
- Responsive design for mobile and desktop
- CSRF token protection for all forms
- Flash messaging for user feedback

### 2. Google Classes Integration

Students can now:
- **Search and Discover Classes** - Browse available live classes from tutors
- **Enroll in Classes** - One-click enrollment with confirmation
- **View Class Details** - Instructor name, description, date/time, class size
- **Access Meeting Links** - Google Meet links provided by tutors
- **Manage Enrollments** - Leave classes when needed

**Sample Classes Included:**
- Algebra Basics - Grade 8
- Geometry Problem Solving
- Calculus Introduction

### 3. Support Ticket System

**Student Features:**
- Create support tickets with subject and detailed message
- View ticket history with status tracking
- Receive responses from admin in the dashboard
- Status indicators: Open, In Progress, Resolved, Closed

**Admin Features:**
- New `/admin/support-tickets.php` page to manage all tickets
- Priority levels: Urgent, High, Medium, Low
- Status management: Open, In Progress, Resolved, Closed
- Reply directly to student tickets
- View ticket statistics

### 4. Database Enhancements

**New Tables Created:**

#### `class_enrollments`
```
- id (PRIMARY KEY)
- user_id (FOREIGN KEY → users)
- class_id (FOREIGN KEY → live_classes)
- enrolled_at (TIMESTAMP)
- attendance_status (TEXT: 'registered', 'attended', 'absent')
```

#### `support_tickets`
```
- id (PRIMARY KEY)
- user_id (FOREIGN KEY → users)
- subject (VARCHAR 255)
- message (TEXT)
- status (TEXT: 'open', 'in_progress', 'resolved', 'closed')
- priority (TEXT: 'low', 'medium', 'high', 'urgent')
- response (TEXT)
- created_at, updated_at (TIMESTAMPS)
```

### 5. New Helper Functions in `config/functions.php`

#### Class Management
- `getAvailableClassesForStudent($userId, $limit)` - Get classes student can join
- `getStudentEnrolledClasses($userId)` - Get student's enrolled classes
- `enrollStudentInClass($userId, $classId)` - Enroll student
- `unenrollStudentFromClass($userId, $classId)` - Remove enrollment

#### Support Tickets
- `createSupportTicket($userId, $subject, $message)` - Create ticket
- `getStudentSupportTickets($userId)` - Get student's tickets
- `getAllOpenSupportTickets()` - Admin: get open tickets

#### User Profile Management
- `updateUserProfile($userId, $name, $email)` - Update profile
- `updateUserPassword($userId, $newPassword)` - Change password

## 🚀 Getting Started

### Initial Setup

1. **Initialize Database Tables** (if needed):
   ```bash
   php init-db.php
   ```

2. **Seed Sample Data**:
   ```bash
   php seed-db.php
   ```

### Usage

**For Students:**
1. Log in as a student
2. You'll be redirected to the new dashboard
3. Navigate tabs for different sections
4. Enroll in available classes from the Classes tab
5. Manage profile and support tickets in Settings and Support tabs

**For Admins:**
1. Go to `/admin/support-tickets.php`
2. Review incoming student support tickets
3. Click "Reply" to respond to a ticket
4. Update status and send response
5. View ticket statistics on the overview page

## 📊 Database Schema

### Class Lifecycle
1. Tutor creates a live class in `live_classes` table
2. Student discovers class in Classes tab
3. Student clicks "Enroll Now"
4. Entry created in `class_enrollments` table
5. Class appears in "My Enrolled Classes" section
6. Student can access meeting link 5 minutes before class starts

### Support Ticket Lifecycle
1. Student submits support ticket from Support tab
2. Ticket created in `support_tickets` table with status='open'
3. Admin reviews ticket in admin panel
4. Admin responds with status update
5. Student sees response in dashboard with status update
6. Ticket marked as 'resolved' or 'closed'

## 🔧 Configuration

### Directory Permissions
Make sure these directories exist and are writable:
```
/app/uploads/products/          (chmod 777)
/app/uploads/products/images/   (chmod 777)
```

### PHP Settings
Upload limits:
- `upload_max_filesize`: 2M
- `post_max_size`: 8M

### Security
- CSRF tokens on all forms
- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- Session-based authentication

## 📱 Responsive Design

All new features are fully responsive:
- ✓ Mobile (320px and up)
- ✓ Tablet (768px and up)
- ✓ Desktop (1024px and up)
- ✓ Large screens (1280px and up)

## 🎨 UI Components

Uses Tailwind CSS for consistent styling with:
- Color scheme: Brand colors (indigo)
- Spacing: Based on 4px grid
- Typography: Inter font family
- Components: Cards, tabs, forms, modals

## 📝 Notes

### SQLite Database
The system uses SQLite, not MySQL. Key differences:
- Use `datetime('now')` instead of `NOW()`
- TEXT type for ENUM-like values
- Use `AUTOINCREMENT` for auto-increment columns

### Class Meeting Times
- Classes require future `end_at` timestamp
- Meeting links shown 5 minutes before class starts
- Times displayed in user's local timezone via JavaScript

### User Roles
- `student` - Can enroll in classes, purchase materials, submit tickets
- `collaborator` - Can create products (materials)
- `tutor` - Can create live classes
- `admin` - Full system access, can manage support tickets

## 🐛 Troubleshooting

### Upload Failures
**Error**: "File upload failed" or "Image upload failed"
**Fix**: Check `/app/uploads/products/` permissions are 777

### Class Not Showing
**Error**: Classes don't appear in Classes tab
**Fix**: Run `php seed-db.php` to add sample classes, or ensure live_classes have future `end_at` time

### Database Errors
**Error**: "SQLSTATE syntax error"
**Fix**: Ensure you're using SQLite syntax (datetime('now'), TEXT instead of ENUM)

## 🔄 Recent Changes Summary

| Component | Change | Impact |
|-----------|--------|--------|
| `dashboard.php` | Now redirects to `student-dashboard.php` | Clean redirect for all students |
| `student-dashboard.php` | New file with tabs | Production-ready dashboard |
| `config/functions.php` | Added 9 new helper functions | Supports all new features |
| `admin/support-tickets.php` | New admin page | Admin can manage tickets |
| `app/database.sql` | Added 2 new tables | Schema for enrollments & tickets |
| Upload permissions | Fixed to 777 | File uploads now work |

## ✨ Future Enhancements

Potential improvements (not yet implemented):
- Email notifications for ticket responses
- Class attendance tracking
- Real-time class reminders
- Class recordings storage
- Advanced search/filtering for classes
- Admin dashboard with analytics
- Student achievement badges
- Class chat/discussion forum
