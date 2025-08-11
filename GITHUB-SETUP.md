# GitHub Integration Setup Guide

This guide walks through setting up GitHub-based automatic updates for the UQUAL Financial Calculators plugin using GitHub Updater.

## 🚀 Quick Setup (5 minutes)

### Step 1: Create GitHub Repository

1. **Create New Repository**
   ```
   Repository name: uqual-financial-calculators
   Description: WordPress plugin for comprehensive financial calculators
   Visibility: Private (recommended) or Public
   Initialize: with README
   ```

2. **Upload Plugin Files**
   - Upload all files from `uqual-calculators-complete/` to the repository root
   - Ensure the main plugin file `uqual-calculators.php` is in the root

### Step 2: Configure Plugin Headers

1. **Update Plugin Header**
   - Edit `uqual-calculators.php`
   - Replace `your-username` with your actual GitHub username:
   ```php
   * GitHub Plugin URI: your-username/uqual-financial-calculators
   ```

2. **Set Access Token (Private Repos Only)**
   - Create GitHub Personal Access Token: Settings → Developer settings → Personal access tokens
   - Required scopes: `repo` (for private repos)
   - Update header with token:
   ```php
   * GitHub Access Token: ghp_your_actual_token_here
   ```

### Step 3: Install GitHub Updater

1. **Download GitHub Updater Plugin**
   ```
   URL: https://github.com/afragen/github-updater/releases
   Download: github-updater.zip (latest release)
   ```

2. **Install on WordPress**
   ```
   WordPress Admin → Plugins → Add New → Upload Plugin
   Upload: github-updater.zip
   Activate: GitHub Updater
   ```

### Step 4: Test Integration

1. **Install UQUAL Plugin**
   - Upload your plugin via WordPress admin
   - Activate the plugin

2. **Verify Update Detection**
   ```
   WordPress Admin → Settings → GitHub Updater
   Check: UQUAL Financial Calculators should appear in list
   ```

## 🔧 Detailed Configuration

### GitHub Repository Structure
```
your-username/uqual-financial-calculators/
├── uqual-calculators.php          # Main plugin file (required in root)
├── README.md                      # Repository documentation
├── CHANGELOG.md                   # Version history
├── includes/                      # Plugin core files
├── admin/                         # Admin interface
├── assets/                        # CSS/JS assets
├── .gitignore                     # Git ignore rules
└── LICENSE                        # License file
```

### Plugin Header Options
```php
/**
 * GitHub Plugin URI: username/repository-name
 * GitHub Branch: main                          # Default branch
 * GitHub Access Token: ghp_token              # For private repos
 * Primary Branch: main                        # Primary development branch
 * Release Asset: true                         # Use release assets
 * Requires WP: 5.8                           # Minimum WP version
 * Tested up to: 6.4                          # Tested WP version
 * Network: false                             # Not a network plugin
 * Update Server: https://api.github.com      # GitHub API endpoint
 */
```

### Private Repository Setup

1. **Create Personal Access Token**
   ```
   GitHub → Settings → Developer settings → Personal access tokens → Generate
   Scopes needed: repo (full repository access)
   Expiration: No expiration (recommended for plugins)
   ```

2. **Security Considerations**
   - Store token in plugin header (encrypted in database)
   - Alternative: Store in wp-config.php as constant
   - Never commit tokens to public repositories

### Release Management

1. **Creating Releases**
   ```bash
   # Tag a version
   git tag -a v1.0.0 -m "Version 1.0.0 - Initial release"
   git push origin v1.0.0
   
   # Create release on GitHub
   GitHub → Releases → Create new release
   Tag: v1.0.0
   Title: Version 1.0.0
   Description: Release notes from CHANGELOG.md
   ```

2. **Automatic ZIP Generation**
   - GitHub automatically creates ZIP files for releases
   - GitHub Updater downloads these for plugin updates

## 🔄 Update Workflow

### For Plugin Users
1. **Automatic Detection**
   - WordPress checks for updates every 12 hours
   - Manual check: Dashboard → Updates

2. **Update Process**
   ```
   Dashboard → Updates → UQUAL Financial Calculators
   Click: "Update Now"
   Automatic: Download, install, activate
   ```

### For Developers
1. **Development Workflow**
   ```bash
   # Make changes
   git add .
   git commit -m "Fix: Improve color picker functionality"
   git push origin main
   
   # Release new version
   # 1. Update version in plugin header
   # 2. Update CHANGELOG.md
   # 3. Create git tag
   # 4. Create GitHub release
   ```

2. **Version Numbering**
   - **Major (1.0.0 → 2.0.0)**: Breaking changes
   - **Minor (1.0.0 → 1.1.0)**: New features
   - **Patch (1.0.0 → 1.0.1)**: Bug fixes

## ⚙️ Advanced Configuration

### GitHub Actions (Optional)
Create `.github/workflows/release.yml`:
```yaml
name: Create Release
on:
  push:
    tags:
      - 'v*'
jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Create Release
        uses: actions/create-release@v1
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          tag_name: ${{ github.ref }}
          release_name: Release ${{ github.ref }}
          draft: false
          prerelease: false
```

### Custom Update Server (Advanced)
For complete control, implement custom update server:
```php
// In plugin main file
add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');
function check_for_plugin_update($transient) {
    // Custom update logic
    $remote_version = wp_remote_get('https://your-domain.com/api/version');
    // Compare versions and provide update info
}
```

## 🐛 Troubleshooting

### Common Issues

1. **Plugin Not Appearing in Updates**
   - Check GitHub Plugin URI format
   - Verify repository accessibility
   - Confirm GitHub Updater is active

2. **Update Fails**
   - Check GitHub access token permissions
   - Verify repository is accessible
   - Check WordPress error logs

3. **Version Detection Issues**
   - Ensure version in plugin header matches GitHub release tag
   - Verify semantic versioning format (1.0.0)

### Debug Mode
Enable debugging in wp-config.php:
```php
define('GITHUB_UPDATER_DEBUG', true);
```

### Log Locations
```
WordPress Admin → Tools → Site Health → Info
GitHub Updater logs in WordPress debug.log
```

## 📋 Checklist

Before going live:
- [ ] Repository created and files uploaded
- [ ] Plugin headers updated with correct GitHub URI
- [ ] Access token configured (if private repo)
- [ ] GitHub Updater plugin installed and activated
- [ ] Test release created and tagged
- [ ] Update detection verified in WordPress admin
- [ ] Update process tested on staging site

## 🎯 Benefits

### For Developers
- **Version Control**: Full Git history and branching
- **Automated Deployment**: Push to update all sites
- **Issue Tracking**: GitHub Issues for bug reports
- **Collaboration**: Multiple developers can contribute

### For Users
- **Automatic Updates**: No manual downloads
- **Security**: Always latest version with fixes
- **Reliability**: Professional update mechanism
- **Transparency**: View code and changes on GitHub

---

**Ready to go live with professional plugin updates!** 🚀