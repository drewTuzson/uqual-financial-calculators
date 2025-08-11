# UQUAL Financial Calculators WordPress Plugin

A comprehensive WordPress plugin providing professional financial calculators for loan readiness assessment, DTI calculation, mortgage affordability analysis, and more.

## 🚀 Features

### 5 Professional Calculators
- **Loan Readiness Score Calculator** - Proprietary UQUAL algorithm assessing mortgage qualification readiness
- **Advanced DTI Calculator** - Comprehensive debt-to-income ratio analysis
- **Mortgage Affordability Plus** - Multi-scenario affordability calculator
- **Credit Score Improvement Simulator** - Interactive credit enhancement planning
- **Down Payment Savings Calculator** - Strategic savings planning tool

### ⚙️ Technical Features
- **WordPress Integration** - Native WordPress admin interface
- **Divi Theme Compatibility** - Seamless integration with Divi 5
- **Mobile-First Design** - Responsive calculators with step-by-step mobile interface
- **Analytics Tracking** - Comprehensive usage analytics and conversion tracking
- **Customizable Colors** - Admin color picker for brand customization
- **SEO Optimized** - Schema.org structured data markup
- **Shortcode System** - Easy embedding with `[uqual_calculator type="loan_readiness"]`

## 📋 Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## 🔧 Installation

### Method 1: GitHub Updater (Recommended)

1. **Install GitHub Updater Plugin**
   ```
   Download from: https://github.com/afragen/github-updater
   Install in WordPress Admin → Plugins → Add New → Upload Plugin
   ```

2. **Configure Repository**
   - Update the plugin header with your GitHub username
   - Set up GitHub Personal Access Token (if using private repo)

3. **Automatic Updates**
   - Plugin will automatically check for updates from GitHub releases
   - Updates appear in WordPress Admin → Dashboard → Updates

### Method 2: Manual Installation

1. Download the latest release ZIP from GitHub
2. Upload via WordPress Admin → Plugins → Add New → Upload Plugin
3. Activate the plugin

## 🏗️ Setup & Configuration

### 1. Plugin Activation
```
WordPress Admin → Plugins → UQUAL Financial Calculators → Activate
```

### 2. Basic Configuration
```
WordPress Admin → UQUAL Calculators → Settings
- Set primary and accent colors
- Configure CTA text and URLs
- Enable/disable analytics tracking
```

### 3. Add Calculators to Pages
```
// Loan Readiness Calculator
[uqual_calculator type="loan_readiness"]

// DTI Calculator
[uqual_calculator type="dti"]

// Affordability Calculator
[uqual_calculator type="affordability"]

// Credit Simulator
[uqual_calculator type="credit_simulator"]

// Savings Calculator
[uqual_calculator type="savings"]
```

### 4. Shortcode Parameters
```
[uqual_calculator 
    type="loan_readiness" 
    theme="light" 
    show_intro="true" 
    mobile_steps="true"
    cta_text="Get Professional Help"
]
```

## 🎨 Customization

### Color Customization
- Primary Color: Main buttons, progress bars, focus states
- Accent Color: Secondary elements, highlights, CTA buttons
- CSS Custom Properties: `--uqual-primary-color`, `--uqual-accent-color`

### Advanced Styling
```css
/* Override calculator styles */
.uqual-calculator-wrapper {
    --uqual-primary-color: #your-color;
    --uqual-accent-color: #your-accent;
}
```

## 📊 Analytics & Tracking

### Built-in Analytics
- Session tracking and completion rates
- Form interaction analytics
- Calculator performance metrics
- User journey tracking

### Integration Options
- Google Analytics events
- Custom conversion tracking
- Lead generation metrics

## 🔒 Security Features

- **Input Sanitization** - All user inputs properly sanitized
- **SQL Injection Prevention** - Prepared statements and validation
- **XSS Protection** - Output escaping and validation
- **Nonce Verification** - CSRF protection on all forms
- **Capability Checks** - Proper WordPress permissions

## 🛠️ Development

### File Structure
```
uqual-calculators/
├── uqual-calculators.php          # Main plugin file
├── includes/                      # Core classes
│   ├── class-database-handler.php
│   ├── class-base-calculator.php
│   ├── class-calculator-manager.php
│   ├── class-analytics-tracker.php
│   ├── class-shortcode-handler.php
│   └── calculators/               # Individual calculator classes
├── admin/                         # Admin interface
│   ├── class-admin-interface.php
│   └── views/                     # Admin templates
├── assets/                        # Frontend assets
│   ├── css/
│   └── js/
└── templates/                     # Calculator templates
```

### Database Schema
- `wp_uqual_calculator_sessions` - Session tracking
- `wp_uqual_calculator_inputs` - Form data storage
- `wp_uqual_calculator_events` - Analytics events

### Hooks & Filters
```php
// Customize calculator output
add_filter('uqual_calculator_results', 'your_custom_function');

// Modify form fields
add_filter('uqual_calculator_fields', 'your_field_modifier');

// Custom analytics tracking
add_action('uqual_calculation_complete', 'your_tracking_function');
```

## 🚀 Deployment & Updates

### GitHub Updater Integration
1. **Create Release**: Tag version in GitHub
2. **Automatic Detection**: Plugin checks for updates
3. **One-Click Update**: Install from WordPress admin

### Version Management
- Semantic versioning (1.0.0, 1.1.0, 2.0.0)
- Changelog documentation
- Backward compatibility maintenance

## 📝 Changelog

### Version 1.0.0
- Initial release
- 5 financial calculators implemented
- WordPress admin interface
- Analytics tracking system
- Divi theme integration
- Mobile-responsive design

## 🐛 Support & Issues

- **GitHub Issues**: Report bugs and request features
- **Documentation**: Comprehensive setup guides
- **Professional Support**: Available for UQUAL clients

## 📄 License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## 👥 Contributors

- **UQUAL LLC** - Plugin development and maintenance
- **Claude AI** - Development assistance and optimization

---

**Built by UQUAL LLC for professional mortgage and financial assessment.**
