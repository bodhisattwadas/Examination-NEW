# Demo Screenshots

This folder contains screenshots of the main pages of the **Examination Duty Management Portal** for demo purposes.

## How to Generate Screenshots

1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

2. (Recommended) Log in as admin in your browser first:
   - URL: http://127.0.0.1:8000/login
   - Email: `admin@example.com`
   - Password: `password`

3. Run the screenshot script from the project root:
   ```powershell
   .\generate-demo-screenshots.ps1
   ```

## Main Pages

| File                        | Page Description                  | URL Path                     |
|----------------------------|-----------------------------------|------------------------------|
| 01-login.png               | Login Page                        | /login                       |
| 02-dashboard.png           | Dashboard                         | /dashboard                   |
| 03-staff-list.png          | Staff Directory                   | /staff                       |
| 04-add-staff.png           | Add New Staff                     | /staff/create                |
| 05-staff-upload.png        | Bulk Upload Staff                 | /staff/upload                |
| 06-assign-duty.png         | Assign Exam Duty                  | /duty/create                 |
| 07-reports.png             | Reports & Filters                 | /report                      |
| 08-configuration.png       | Exam Time Configuration           | /config/exam-times           |

## Notes

- Some pages require authentication. For clean screenshots, log in first or temporarily disable auth middleware.
- Screenshots are taken at 1366x768 resolution.
- The script uses Google Chrome in headless mode.

## Manual Alternative

If the script doesn't work, you can manually visit each URL and take screenshots using your browser's developer tools or extensions like "Full Page Screen Capture".