# GCP Always Free VM (`e2-micro`) Deployment Guide

This guide walks you through deploying the **Examination Duty Management Portal** to Google Cloud Platform under the **100% Free Tier**.

---

## 1. Create the Free VM in GCP Console

1. Log in to [Google Cloud Console](https://console.cloud.google.com/).
2. Navigate to **Compute Engine** > **VM instances**.
3. Click **Create Instance**.
4. Configure the exact options below to stay within the **Always Free** limits:

| Setting | Value to Choose | Why |
| :--- | :--- | :--- |
| **Name** | `exam-portal-vm` | Any identifier |
| **Region** | `us-central1` (Iowa), `us-east1` (S. Carolina), or `us-west1` (Oregon) | **Must be one of these 3 US regions for 100% Free tier** |
| **Zone** | Any (e.g. `us-central1-a`) | |
| **Series** | **E2** | |
| **Machine Type** | **`e2-micro`** (2 vCPU, 1 GB memory) | **Free tier eligible** |
| **Boot Disk** | Click **Change**: <br>• OS: **Ubuntu**<br>• Version: **Ubuntu 24.04 LTS (x86/64)**<br>• Boot disk type: **Standard persistent disk** *(Do NOT select SSD or Balanced)*<br>• Size: **30 GB** | **Up to 30 GB standard disk is 100% Free** |
| **Firewall** | Check **Allow HTTP traffic** and **Allow HTTPS traffic** | Allows web visitors to reach your portal |

5. Click **Create**. Within 60 seconds, your VM will be active and display an **External IP** (e.g., `34.123.45.67`).

---

## 2. Connect & Run Server Setup

1. In the VM instances list, click the **SSH** button next to your new VM. A browser terminal window will open.
2. Download and run the turnkey setup script:
   ```bash
   curl -O https://raw.githubusercontent.com/YOUR_REPO_OR_UPLOAD/setup_server.sh
   # (Or simply create setup_server.sh using nano / paste)
   chmod +x setup_server.sh
   ./setup_server.sh
   ```
   *What this script does automatically:*
   * Adds a **2 GB Swap file** (crucial so 1 GB RAM never runs out during PDF/Excel generation).
   * Installs **PHP 8.3-FPM** with all required Laravel extensions.
   * Installs **Nginx**, **MariaDB/MySQL**, and **Composer**.
   * Creates the database `examination_portal` and user `exam_user`.
   * Sets up the Nginx virtual host with caching headers and security rules.

---

## 3. Upload Code & Initialize Application

1. Put your code into `/var/www/examination-portal`:
   * **Option A (Git):**
     ```bash
     cd /var/www
     git clone <YOUR_GIT_REPO_URL> examination-portal
     ```
   * **Option B (Direct Upload):**
     Upload the zip file using the SSH window's "Upload File" tool, then:
     ```bash
     sudo unzip Examination-NEW.zip -d /var/www/examination-portal
     ```

2. Configure environment:
   ```bash
   cd /var/www/examination-portal
   cp .env.example .env
   nano .env
   ```
   Set:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://YOUR_VM_EXTERNAL_IP

   DB_DATABASE=examination_portal
   DB_USERNAME=exam_user
   DB_PASSWORD=ExamPass@2026_Secure
   ```

3. Deploy and optimize:
   ```bash
   chmod +x deploy_app.sh
   ./deploy_app.sh
   ```

4. Create the initial admin user & seed data:
   ```bash
   php artisan migrate --seed --force
   ```
   *(Or create your admin user manually using `php artisan tinker`: `User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('your_password')]);`)*

---

## 4. Access the Portal

Open your web browser and navigate to:
```
http://<YOUR_VM_EXTERNAL_IP>
```
Log in with your administrator credentials.

---

## 5. (Optional) Custom Domain & Free HTTPS / SSL

If you have a domain or subdomain (e.g. `exam.yourcollege.edu`):
1. Add an **A Record** in your DNS pointing to your VM's External IP.
2. In the SSH terminal, run:
   ```bash
   sudo apt-get install -y certbot python3-certbot-nginx
   sudo certbot --nginx -d exam.yourcollege.edu
   ```
Let's Encrypt will install a free SSL certificate with automatic renewals.
