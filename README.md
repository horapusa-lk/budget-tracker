# Student Budget Tracker 💰

A comprehensive web-based expense tracking and budget management application designed for students to monitor their spending habits, manage their monthly allowance, and work towards savings goals.

## Features

### Core Functionality
- **User Authentication**: Secure login and registration system with session management
- **Dashboard**: Real-time overview of balance, allowance, total spending, and recent expenses
- **Expense Tracking**: Record expenses with category, amount, date, and description
- **Budget Management**: Set and monitor category-specific budget limits
- **Spending Analytics**: Visual pie charts showing spending breakdown by category
- **Savings Goals**: Create and track progress toward savings milestones with optional deadlines
- **Spending History**: Browse and analyze expenses from previous months
- **CSV Export**: Download expense data for external analysis

### User Interface
- Modern, responsive design using **Tailwind CSS**
- Dark/Light theme toggle with persistent user preference
- Smooth animations and transitions
- Mobile-friendly interface
- Real-time data updates via AJAX

## Tech Stack

### Backend
- **PHP** (7.4+) - Server-side logic
- **MySQL** - Data persistence
- **PDO** - Database abstraction layer

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Styling with Tailwind CSS via CDN
- **JavaScript (Vanilla)** - Interactive features and AJAX

### Infrastructure
- **XAMPP** - Local development environment
- **Apache** - Web server

## Project Structure

```
student-budget-tracker/
├── api/                          # JSON API endpoints
│   ├── add_expense.php           # Create new expense
│   ├── delete_expense.php        # Remove expense
│   ├── export_csv.php            # Export expenses to CSV
│   ├── get_expenses.php          # Retrieve expenses
│   ├── login.php                 # User authentication
│   ├── register.php              # New user registration
│   ├── savings_goals.php         # Manage savings goals (CRUD)
│   ├── update_allowance.php      # Update monthly allowance
│   └── update_category_limits.php # Set category budgets
├── assets/                       # Static files
│   ├── css/                      # Stylesheets
│   ├── js/                       # Client-side scripts
│   └── vendor/                   # Third-party libraries
├── config/                       # Configuration
│   └── db.php                    # Database connection setup
├── includes/                     # Reusable components
│   ├── auth.php                  # Authentication helpers
│   ├── functions.php             # Utility functions
│   ├── header.php                # Page header/navigation
│   └── footer.php                # Page footer
├── index.php                     # Dashboard
├── add-transaction.php           # Expense entry form
├── history.php                   # Spending history view
├── settings.php                  # User settings
├── login.php                     # Login page
├── register.php                  # Registration page
├── logout.php                    # Session termination
└── README.md                     # This file
```

## Database Schema

### Tables
- **users** - User accounts and authentication
- **allowances** - Monthly allowance records (month/year based)
- **expenses** - Individual expense transactions
- **categories** - Predefined spending categories (Food, Transport, Books, etc.)
- **user_category_limits** - Custom budget limits per category per user
- **savings_goals** - Savings goals with targets and deadlines

## Installation & Setup

### Prerequisites
- XAMPP (or Apache + PHP + MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Steps

1. **Clone/Extract the project** to your XAMPP htdocs folder:
   ```bash
   C:\xampp\htdocs\
   ```

2. **Create the database**:
   ```sql
   CREATE DATABASE student_budget;
   ```

3. **Import the database schema** (execute in phpMyAdmin or MySQL CLI):
   ```sql
   USE student_budget;

   -- Create tables with proper structure
   -- (Schema file should be provided separately)
   ```

4. **Update database credentials** in `config/db.php` if needed:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'student_budget');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Start XAMPP** and access the application:
   ```
   http://localhost/
   ```

## API Endpoints

### Authentication
- `POST /api/login.php` - User login
- `POST /api/register.php` - User registration

### Expenses
- `POST /api/add_expense.php` - Create expense
- `GET /api/get_expenses.php` - Retrieve expenses
- `DELETE /api/delete_expense.php` - Remove expense
- `GET /api/export_csv.php` - Export to CSV

### Budget Management
- `POST /api/update_allowance.php` - Set monthly allowance
- `POST /api/update_category_limits.php` - Update category budgets
- `POST /api/savings_goals.php` - Create savings goal
- `PUT /api/savings_goals.php` - Add to goal
- `DELETE /api/savings_goals.php` - Delete goal

## Usage Guide

### Dashboard
View your financial overview at a glance:
- **Balance**: Remaining allowance for the month
- **Allowance**: Total monthly budget
- **Total Spent**: Amount spent so far
- **Spending Breakdown**: Pie chart by category
- **Recent Expenses**: Latest 15 transactions
- **Savings Goals**: Progress toward goals

### Adding Expenses
1. Click **+ Add New** on the dashboard
2. Select a category
3. Enter the amount in Rs.
4. Add optional description
5. Set the expense date
6. Click **Save Expense**

### Managing Budget
1. Go to **Settings**
2. Set your monthly allowance
3. Configure category-specific limits
4. Create savings goals with optional deadlines
5. Track progress on each goal

### Viewing History
1. Click **Spending History**
2. Select month and year
3. View category breakdown and all expenses
4. Download as CSV if needed

### Theme Toggle
Click the sun/moon icon in the top right to switch between light and dark themes.

## Key Functions

### Helper Functions (`includes/functions.php`)
- `formatCurrency()` - Format amounts as Sri Lankan Rupees (Rs.)
- `sanitize()` - Sanitize user input
- `isValidDate()` - Validate date format
- `getCurrentAllowance()` - Fetch monthly allowance
- `getTotalSpent()` - Calculate total expenses
- `getSpendingByCategory()` - Get spending per category
- `getSavingsGoals()` - Retrieve user's goals
- `getRecentExpenses()` - Get latest transactions

### Auth Functions (`includes/auth.php`)
- `requireLogin()` - Enforce authentication
- `getCurrentUserId()` - Get logged-in user ID
- `getCurrentUserName()` - Get user's display name
- `isLoggedIn()` - Check authentication status

## Security Features

✅ **Prepared Statements** - Protection against SQL injection
✅ **Password Hashing** - Secure password storage
✅ **Session Management** - Server-side session handling
✅ **Input Sanitization** - XSS protection via htmlspecialchars()
✅ **PDO with Error Handling** - Safe database interactions
✅ **CORS Headers** - API security (can be enhanced)

## Configuration

### Currency
Default currency is **Sri Lankan Rupee (Rs.)**. To change, modify `includes/functions.php`:
```php
function formatCurrency(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}
```

### Database Connection
Edit `config/db.php` to adjust:
- Host
- Database name
- Username
- Password
- Charset

### Categories
Categories are managed in the `categories` table. Add, remove, or customize as needed.

## Troubleshooting

### Login Issues
- Verify database is running
- Check credentials in `config/db.php`
- Ensure `categories` table is populated

### Blank Pages
- Check PHP error logs: `C:\xampp\apache\logs\error.log`
- Verify session support is enabled in PHP
- Ensure database connection is successful

### Styling Not Loading
- Clear browser cache (Ctrl+Shift+Delete)
- Check if Tailwind CDN is accessible
- Verify internet connection (CSS loaded from CDN)

### CSV Export Issues
- Check file permissions in project folder
- Verify PHP can write to temporary directories
- Ensure no special characters in expense descriptions

## Development Notes

### Session Handling
- Sessions are started in `includes/auth.php`
- All protected pages call `requireLogin()`
- Session data includes `user_id` and `full_name`

### Database Queries
- All queries use prepared statements with parameterized placeholders
- User ID is always verified for data isolation
- Month/year filters use SQL functions for accuracy

### Frontend Communication
- AJAX requests use `fetch()` API
- Content-Type set to `application/json`
- Error handling displays user-friendly messages
- Toast notifications show operation status

## Future Enhancement Ideas

- 📊 Advanced analytics and charts
- 📱 Mobile app (React Native/Flutter)
- 🔔 Spending alerts and notifications
- 👥 Family/group budget sharing
- 📈 Budget forecasting
- 🏦 Bank account integration
- 💳 Payment method tracking
- 📧 Email reports
- 🎯 Recurring expenses
- 📱 Push notifications

## License

This project is provided as-is for educational purposes.

## Support

For issues or questions, contact the development team or refer to the inline code comments for implementation details.

---

**Built with ❤️ for student financial literacy**
