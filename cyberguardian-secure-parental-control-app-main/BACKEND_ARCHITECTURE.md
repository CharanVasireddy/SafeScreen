# CBGD Backend Architecture

This document describes the complete backend structure for the CBGD Android Application. The backend is built using **PHP** and **MySQL**, hosted on a local XAMPP server.

## 1. Directory Structure

The backend is located at `C:\xampp\htdocs\CBGD`.

```
CBGD/
├── db_connect.php              # Database connection configuration
├── setup_database.php          # Script to initialize database and tables
├── register.php                # API: User Registration
├── login.php                   # API: User Login
├── get_alerts.php              # API: Fetch user alerts
├── get_app_usage.php           # API: Fetch app usage statistics
├── get_security_data.php       # API: Fetch security status, history, and blocked sites
├── generate_pairing_code.php   # API: Generate a code to link a child device
├── pair_device.php             # API: Link a device using a code
└── BACKEND_ARCHITECTURE.md     # This documentation file
```

---

## 2. Database Schema

The database is named `cbgd_db`.

### 2.1. Users Table (`users`)
Stores parent and child account information.

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK, Auto Inc) | Unique User ID |
| `name` | VARCHAR(50) | Full Name |
| `email` | VARCHAR(50) | Email Address (Unique) |
| `password` | VARCHAR(255) | Hashed Password |
| `role` | VARCHAR(10) | 'parent' or 'child' |
| `reg_date` | TIMESTAMP | Registration Date |

### 2.2. Alerts Table (`alerts`)
Stores notifications exhibited in the `AlertsFragment`.

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK, Auto Inc) | Unique Alert ID |
| `user_id` | INT (FK) | Linked User |
| `title` | VARCHAR(100) | Alert Title |
| `description` | TEXT | Detailed message |
| `created_at` | DATETIME | Time of alert |
| `is_unread` | BOOLEAN | Read status |

### 2.3. App Usage Table (`app_usage`)
Stores usage statistics shown in `DashboardFragment` and `MonitoringFragment`.

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK, Auto Inc) | Unique Record ID |
| `user_id` | INT (FK) | Linked User |
| `app_name` | VARCHAR(50) | Name of the app (e.g., "TikTok") |
| `usage_time` | VARCHAR(20) | Display string (e.g., "1h 12m") |
| `progress` | INT(3) | Usage percentage (0-100) |

### 2.4. Security Tables
Data for the `SecurityFragment`.

#### `security_apps`
External apps monitored for safety.
- `app_name`: Name of app
- `is_safe`: Boolean status
- `status_text`: Message (e.g., "No threats found")

#### `blocked_sites`
History of blocked web content.
- `url`: Site URL
- `reason`: Block reason (e.g., "Phishing")
- `blocked_at`: Timestamp

#### `security_history`
General security audit log.
- `title`: Event title
- `description`: Event details
- `event_time`: Timestamp
- `status`: Status string (e.g., "Resolved")

### 2.5. Device Pairing Tables
Supports the flow in `PairingFragment` and `LinkedDevicesFragment`.

#### `devices`
- `device_name`: User-friendly name
- `device_identifier`: Unique hardware ID or UUID
- `last_active`: Timestamp

#### `pairing_codes`
Temporary codes for linking.
- `code`: The 6-digit code
- `expires_at`: Expiration timestamp

---

## 3. API Endpoints

All responses are in **JSON** format.

### 3.1. Authentication

#### **Register**
- **URL**: `/register.php`
- **Method**: `POST`
- **Input**:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secretpassword",
    "role": "parent"
  }
  ```
- **Response**: `{"status": "success", "user_id": 1}`

#### **Login**
- **URL**: `/login.php`
- **Method**: `POST`
- **Input**:
  ```json
  {
    "email": "john@example.com",
    "password": "secretpassword"
  }
  ```
- **Response**:
  ```json
  {
      "status": "success",
      "user": { "id": 1, "name": "John Doe", "role": "parent" }
  }
  ```

### 3.2. Dashboard & Monitoring

#### **Get App Usage**
- **URL**: `/get_app_usage.php?user_id=1`
- **Method**: `GET`
- **Response**:
  ```json
  {
    "status": "success",
    "data": [
      {
        "appName": "TikTok",
        "usageTime": "1h 12m",
        "progress": 60
      }
    ]
  }
  ```
  *Maps to `AppUsageItem` class in Android.*

#### **Get Alerts**
- **URL**: `/get_alerts.php?user_id=1`
- **Method**: `GET`
- **Response**:
  ```json
  {
    "status": "success",
    "data": [
      {
        "title": "Suspicious Activity",
        "description": "Unusual login attempt detected.",
        "timeAgo": "5m ago",
        "isUnread": true
      }
    ]
  }
  ```
  *Maps to `AlertItem` class in Android.*

### 3.3. Security

#### **Get Security Data**
- **URL**: `/get_security_data.php?user_id=1`
- **Method**: `GET`
- **Response**: Returns a composite object containing arrays for `apps`, `blocked_sites`, and `history`.
  ```json
  {
    "status": "success",
    "apps": [ ... ],
    "blocked_sites": [ ... ],
    "history": [ ... ]
  }
  ```

### 3.4. Device Pairing

#### **Generate Pairing Code**
- **URL**: `/generate_pairing_code.php`
- **Method**: `POST`
- **Input**: `user_id`
- **Response**: `{"status": "success", "code": "CG-829102", "expires_at": "..."}`

#### **Pair Device**
- **URL**: `/pair_device.php`
- **Method**: `POST`
- **Input**:
  ```json
  {
      "code": "CG-829102",
      "device_name": "Child's Tablet",
      "device_identifier": "android-id-12345"
  }
  ```
- **Response**: `{"status": "success", "message": "Device paired successfully"}`

---

## 4. Setup Instructions

1.  **Start XAMPP**: Open XAMPP Control Panel and start **Apache** and **MySQL**.
2.  **Initialize Database**:
    *   Open your web browser.
    *   Navigate to: `http://localhost/CBGD/setup_database.php`
    *   You should see messages confirming tables were created.
3.  **Android Configuration**:
    *   In your Android app, update your API Service Base URL.
    *   **Emulator**: Use `http://10.0.2.2/CBGD/`
    *   **Physical Device**: Use your PC's local IP address (e.g., `http://192.168.1.5/CBGD/`). Ensure both devices are on the same Wi-Fi.
