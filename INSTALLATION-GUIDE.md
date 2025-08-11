# UQUAL Financial Calculators - Installation Guide

## 🚨 Critical Issue Resolution

Your site crashed because the original plugin tried to load all dependencies immediately without error handling. The debug version worked, confirming the basic plugin structure is sound.

## 📋 Current Status

✅ **Plugin activated successfully** (debug version)  
❌ **Missing required files** on server  
🔄 **Ready for file upload**  

## 📂 Required Files Checklist

Upload these files to your server in the **exact** directory structure shown:

### Main Plugin Files
```
uqual-calculators/
├── uqual-calculators.php           (Main plugin file)
├── install-check.php               (Installation checker)
└── INSTALLATION-GUIDE.md           (This guide)
```

### Core Includes Directory
```
uqual-calculators/includes/
├── class-database-handler.php      (Database operations)
├── class-base-calculator.php       (Calculator base class)
├── class-calculator-manager.php    (Calculator management)
├── class-analytics-tracker.php     (Analytics tracking)
├── class-shortcode-handler.php     (Shortcode processing)
└── calculators/
    ├── class-loan-readiness-calculator.php
    ├── class-dti-calculator.php
    ├── class-affordability-calculator.php
    ├── class-credit-simulator.php
    └── class-savings-calculator.php
```

### Admin Interface Directory
```
uqual-calculators/admin/
├── class-admin-interface.php       (Admin interface)
└── views/
    ├── dashboard.php               (Dashboard view)
    ├── settings.php                (Settings view)
    └── help.php                    (Help view)
```

### Assets Directory (Optional - for full functionality)
```
uqual-calculators/assets/
├── css/
│   ├── uqual-calculators.css
│   └── divi-compatibility.css
└── js/
    └── uqual-calculators.js
```

## 🚀 Step-by-Step Installation

### Step 1: Upload Installation Checker
1. Upload `install-check.php` to your plugin directory
2. Visit: `https://uqual.tempurl.host/wp-content/plugins/uqual-calculators/install-check.php?standalone=1`
3. This will show you exactly which files are missing

### Step 2: Upload Missing Files
1. **Create directory structure** on your server (if it doesn't exist):
   ```
   /wp-content/plugins/uqual-calculators/includes/
   /wp-content/plugins/uqual-calculators/admin/views/
   ```

2. **Upload core files** to their correct locations:
   - `includes/class-database-handler.php`
   - `includes/class-base-calculator.php` 
   - `includes/class-calculator-manager.php`
   - `includes/class-analytics-tracker.php`
   - `includes/class-shortcode-handler.php`
   - `admin/class-admin-interface.php`
   - `admin/views/dashboard.php`
   - `admin/views/settings.php`
   - `admin/views/help.php`

3. **Set file permissions** to 644 for all PHP files

### Step 3: Verify Installation
1. Re-run the installation checker
2. Ensure all files show ✅ (existing)
3. Check WordPress admin for "UQUAL Debug" menu

### Step 4: Switch to Safe Version
1. **Replace** `uqual-calculators.php` with `uqual-calculators-safe.php`
2. **Rename** `uqual-calculators-safe.php` to `uqual-calculators.php`
3. **Test** that the plugin still works

## 🛠 File Upload Methods

### Option A: FTP/SFTP Client
1. Use FileZilla, WinSCP, or similar
2. Navigate to `/wp-content/plugins/uqual-calculators/`
3. Upload files maintaining directory structure

### Option B: Hosting Control Panel
1. Access your hosting file manager
2. Navigate to WordPress plugins directory
3. Upload files using the web interface

### Option C: WordPress Plugin Uploader
1. Create a ZIP file with proper structure
2. Use WordPress Admin → Plugins → Add New → Upload
3. Upload and activate

## 🔍 Troubleshooting

### Files Still Showing as Missing?

**Check File Paths:**
```php
// These should all return TRUE:
file_exists('/path/to/wp-content/plugins/uqual-calculators/includes/class-database-handler.php');
file_exists('/path/to/wp-content/plugins/uqual-calculators/admin/class-admin-interface.php');
```

**Check File Permissions:**
```bash
# Should be 644 or 755
ls -la /path/to/wp-content/plugins/uqual-calculators/includes/
```

**Check File Contents:**
- Ensure files aren't empty or corrupted
- Verify PHP opening tags `<?php` are present
- No extra spaces before opening tags

### Common Issues

1. **Wrong Directory Structure**
   - Files must be in exact subdirectories shown
   - Case-sensitive on Linux servers

2. **File Permission Issues**
   - Set to 644 for files, 755 for directories
   - Ensure web server can read files

3. **File Corruption**
   - Re-upload any suspicious files
   - Check file sizes match expectations

4. **Server Restrictions**
   - Some hosts block certain file types
   - Contact hosting support if needed

## 🎯 Expected Results

After successful installation:

1. **Debug Version:** Shows all files as ✅ existing
2. **Safe Version:** Admin menu changes to "UQUAL Calculators" 
3. **Shortcode Test:** `[uqual_calculator]` works on frontend
4. **No Errors:** WordPress admin and frontend remain accessible

## 📞 Support

If you encounter issues:

1. **Run install-check.php** first to identify missing files
2. **Check WordPress error logs** for specific PHP errors
3. **Test with other plugins disabled** to rule out conflicts
4. **Try default WordPress theme** temporarily

## 🚀 Next Steps After Installation

Once all files are uploaded and working:

1. **Test basic functionality** with debug version
2. **Upgrade to safe version** for full features
3. **Configure settings** in WordPress admin
4. **Add calculators to pages** using shortcodes
5. **Monitor performance** and error logs

---

**Important:** Always test on a staging site first, and keep backups before making changes!