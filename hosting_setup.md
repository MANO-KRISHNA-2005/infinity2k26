# Hosting Configuration Guide - Infinity 6.0

When moving your website from a local environment (XAMPP) to a live server, you must update the following files with your production credentials.

## 1. MySQL Database Configuration
**File:** `php/db_config.php`

Update these values to match your hosting provider's database settings:
- `$host`: Usually `'localhost'`, but check with your provider.
- `$db`: The name of the MySQL database you created on the host.
- `$user`: The database username.
- `$pass`: The database password (not empty on live servers).

---

## 2. Firebase Frontend Configuration
**File:** `js/firebase-config.js`

Update the `firebaseConfig` object with your production project keys from the Firebase Console:
- `apiKey`: Your production API key.
- `authDomain`: Typically `your-project.firebaseapp.com`.
- `projectId`: Your Firebase Project ID.
- `storageBucket`: Your storage bucket name.
- `messagingSenderId`: Your unique sender ID.
- `appId`: Your Web App ID.

---

## 3. Admin Dashboard Configuration
**File:** `admin/config.php`

Update the following for security and functionality:
- `ADMIN_USER`: Set a strong username for your admin panel.
- `ADMIN_PASS`: Set a strong, unique password.
- `DESK_PASSWORD`: Set the password for your event registration desk.
- `FIREBASE_PROJECT_ID`: Ensure this matches your production Firebase Project ID for backend syncing.

---

## 4. Summary Table

| Service | File Path | Key Variables |
| :--- | :--- | :--- |
| **MySQL** | `php/db_config.php` | `$host`, `$db`, `$user`, `$pass` |
| **Firebase (UI)**| `js/firebase-config.js` | `apiKey`, `projectId`, `appId`, etc. |
| **Admin/Desk** | `admin/config.php` | `ADMIN_PASS`, `DESK_PASSWORD`, `FIREBASE_PROJECT_ID` |

> [!WARNING]
> Never share these files publicly or upload them to public GitHub repositories, as they contain sensitive keys and passwords.
