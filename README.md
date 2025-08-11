# UQUAL Financial Calculators WordPress Plugin

A comprehensive WordPress plugin that provides interactive financial calculators for loan readiness assessment, designed specifically for UQUAL LLC's loan readiness platform.

## Overview

This professional WordPress plugin creates 5 sophisticated financial calculators with modern UI/UX design, comprehensive analytics tracking, and seamless Divi theme integration. Built for lead generation and establishing market authority in the "loan readiness" category.

## Features

### 🧮 Five Professional Calculators

1. **Loan Readiness Score Calculator** (Priority 1)
   - UQUAL's proprietary holistic assessment algorithm
   - Evaluates credit score, DTI ratio, down payment, and documentation
   - Visual score gauge with color-coded classifications
   - Personalized improvement recommendations

2. **Advanced DTI Calculator**
   - Industry-standard debt-to-income calculations
   - Detailed monthly breakdown analysis
   - Improvement recommendations based on ratios
   - Support for multiple income frequencies

3. **Mortgage Affordability Plus Calculator**
   - Maximum home price calculations with local market data
   - Multiple down payment scenario analysis
   - Property tax and insurance estimations
   - PMI calculations for down payments <20%

4. **Credit Score Improvement Simulator**
   - Interactive credit score projection tool
   - Action-based improvement calculations
   - Timeline estimations for score improvements
   - Step-by-step action plan generation

5. **Down Payment Savings Calculator**
   - Compound interest savings calculations
   - Goal-based timeline projections
   - Required payment calculations for target timelines
   - Interactive savings growth charts

### 🎨 Modern UI/UX Design

- **Mobile-First Responsive Design**: Optimized for all devices with step-by-step wizards on mobile
- **Divi Theme Integration**: Inherits Divi's design system, colors, and typography
- **Accessibility Compliant**: WCAG 2.1 AA standards with proper ARIA labels
- **Interactive Elements**: Range sliders, real-time calculations, animated progress indicators
- **Dark/Light Themes**: Customizable color schemes with CSS custom properties

### 📊 Comprehensive Analytics

- **User Behavior Tracking**: Session management, completion rates, and interaction patterns
- **Business Intelligence**: Popular input ranges, conversion metrics, and performance insights
- **Privacy-Focused**: Data anonymization and GDPR-compliant tracking
- **Admin Dashboard**: Real-time analytics with charts and export capabilities
- **Google Analytics Integration**: Custom event tracking and conversion monitoring

### ⚡ Performance & SEO Optimized

- **Fast Loading**: <3 second load times with code splitting and lazy loading
- **SEO Friendly**: Schema.org structured data markup for financial services
- **Caching Ready**: WordPress object cache integration with configurable cache duration
- **CDN Compatible**: Optimized static assets for CDN deployment

## Installation

1. **Upload Plugin Files**:
   ```
   /wp-content/plugins/uqual-calculators/
   ```

2. **Activate Plugin**:
   - Go to WordPress Admin → Plugins
   - Find "UQUAL Financial Calculators"
   - Click "Activate"

3. **Configure Settings**:
   - Navigate to "UQUAL Calculators" in the admin menu
   - Configure your preferences in the Settings page
   - Set your CTA URL and button text

## Usage

### Basic Shortcodes

```php
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

### Advanced Shortcode Parameters

```php
// Full parameter example
[uqual_calculator 
    type="loan_readiness" 
    theme="light" 
    show_intro="true"
    mobile_steps="true"
    cta_text="Get Professional Help"]
```

#### Available Parameters:

| Parameter | Default | Description |
|-----------|---------|-------------|
| `type` | `loan_readiness` | Calculator type (required) |
| `theme` | `light` | Theme (`light` or `dark`) |
| `show_intro` | `true` | Show calculator introduction |
| `mobile_steps` | `true` | Use step wizard on mobile |
| `cta_text` | `Get Help` | Custom CTA button text |

## Technical Architecture

### File Structure

```
uqual-calculators/
├── uqual-calculators.php          # Main plugin file
├── includes/                       # Core classes
│   ├── class-calculator-manager.php
│   ├── class-analytics-tracker.php
│   ├── class-database-handler.php
│   ├── class-shortcode-handler.php
│   ├── class-base-calculator.php
│   └── calculators/               # Individual calculator classes
│       ├── class-loan-readiness-calculator.php
│       ├── class-dti-calculator.php
│       ├── class-affordability-calculator.php
│       ├── class-credit-simulator.php
│       └── class-savings-calculator.php
├── admin/                         # Admin interface
│   ├── class-admin-interface.php
│   └── views/                    # Admin view templates
│       ├── dashboard.php
│       ├── settings.php
│       ├── analytics.php
│       └── help.php
├── assets/                       # Frontend assets
│   ├── css/
│   │   ├── uqual-calculators.css
│   │   └── divi-compatibility.css
│   ├── js/
│   │   └── uqual-calculators.js
│   └── images/
└── templates/                    # Template files
    └── calculator-templates/
```

### Database Schema

The plugin creates three custom database tables:

1. **`wp_uqual_calculator_sessions`**: Tracks user sessions
2. **`wp_uqual_calculator_inputs`**: Stores anonymized calculation data
3. **`wp_uqual_calculator_events`**: Records user interactions and events

### Key Classes

- **`UQUAL_Financial_Calculators`**: Main plugin class with singleton pattern
- **`UQUAL_Calculator_Manager`**: Manages all calculator instances
- **`UQUAL_Base_Calculator`**: Abstract base class for all calculators
- **`UQUAL_Analytics_Tracker`**: Handles all tracking and analytics
- **`UQUAL_Database_Handler`**: Manages database operations
- **`UQUAL_Shortcode_Handler`**: Processes shortcodes and rendering

## Calculator Algorithms

### Loan Readiness Score

```javascript
// Proprietary UQUAL algorithm with industry-standard weightings
const WEIGHTS = {
    creditScore: 0.30,    // 30% weight
    dtiRatio: 0.30,       // 30% weight  
    downPayment: 0.30,    // 30% weight
    documentation: 0.10   // 10% weight
};

// Final score calculation (0-100 scale)
finalScore = (
    creditComponent * WEIGHTS.creditScore +
    dtiComponent * WEIGHTS.dtiRatio +
    downPaymentComponent * WEIGHTS.downPayment +
    documentationComponent * WEIGHTS.documentation
);
```

### DTI Calculation

```javascript
// Industry-standard DTI calculation
dtiRatio = (totalMonthlyDebt / monthlyGrossIncome) * 100;

// Classification based on lending standards:
// ≤28%: Excellent
// 28-36%: Good  
// 36-43%: Acceptable
// >43%: High Risk
```

### Affordability Calculation

```javascript
// 28/36 rule implementation
maxHousingPayment = monthlyIncome * 0.28;
maxTotalDebt = monthlyIncome * 0.36;
availableForHousing = maxTotalDebt - existingDebt;

// Use the more conservative limit
affordablePayment = Math.min(maxHousingPayment, availableForHousing);
```

## Customization

### CSS Custom Properties

The plugin uses CSS custom properties for easy theming:

```css
:root {
    --uqual-primary-color: #2E7D32;
    --uqual-accent-color: #FFA726;
    --uqual-success-color: #4CAF50;
    --uqual-warning-color: #FF9800;
    --uqual-danger-color: #F44336;
}
```

### Divi Integration

Automatic integration with Divi theme:
- Inherits Divi's color scheme via CSS variables
- Compatible with Divi Builder modules
- Matches Divi's responsive breakpoints
- Works with Visual Builder

### Hooks and Filters

```php
// Register custom calculators
do_action('uqual_register_calculators', $calculator_manager);

// Customize calculator output
add_filter('uqual_calculator_results', 'custom_results_formatter', 10, 3);

// Modify analytics tracking
add_filter('uqual_track_event_data', 'custom_event_data', 10, 2);
```

## Analytics & Reporting

### Tracked Metrics

- **Usage Analytics**: Sessions, completions, bounce rates
- **User Behavior**: Input patterns, abandonment points, time on calculator
- **Business Intelligence**: Popular loan amounts, credit score ranges, DTI distributions
- **Conversion Tracking**: CTA clicks, lead generation rates

### Privacy & Compliance

- **Data Anonymization**: IP addresses truncated, sensitive data ranges
- **GDPR Compliant**: User consent handling and data retention controls
- **Secure Storage**: Encrypted sensitive data with WordPress security standards

### Export Capabilities

- **CSV Export**: Detailed analytics data for external analysis
- **JSON Export**: Raw data for integration with other tools
- **Scheduled Cleanup**: Automatic old data removal (configurable retention)

## Performance Optimization

### Frontend Performance

- **Code Splitting**: Separate bundles for each calculator type
- **Lazy Loading**: Components load only when visible
- **Asset Optimization**: Minified CSS/JS with vendor prefixes
- **Mobile Optimization**: Step wizards reduce cognitive load

### Backend Performance

- **Database Optimization**: Proper indexing and query optimization
- **Caching Integration**: WordPress object cache support
- **Memory Management**: Efficient singleton patterns and resource cleanup
- **Background Processing**: Non-blocking analytics data processing

## Security Features

- **Input Sanitization**: All user inputs properly sanitized and validated
- **Nonce Verification**: CSRF protection on all AJAX requests
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Output escaping and content security policies
- **Rate Limiting**: Built-in protection against form spam and abuse

## Browser Support

- **Modern Browsers**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Mobile Browsers**: iOS Safari 14+, Chrome Mobile 90+
- **Progressive Enhancement**: Graceful degradation for older browsers
- **Accessibility**: Screen reader support and keyboard navigation

## Requirements

- **WordPress**: 5.8+
- **PHP**: 8.0+
- **MySQL**: 5.7+ or MariaDB 10.3+
- **JavaScript**: ES6+ support (modern browsers)

## Support & Documentation

### Admin Dashboard Features

- **Real-time Analytics**: Live usage statistics and performance metrics
- **Settings Management**: Easy configuration of colors, CTAs, and tracking
- **Help Documentation**: Built-in help system with troubleshooting guides
- **Export Tools**: Analytics data export in multiple formats

### Troubleshooting

Common issues and solutions:

1. **Calculator Not Displaying**:
   - Verify shortcode syntax
   - Check browser console for JavaScript errors
   - Test with default WordPress theme

2. **Styling Conflicts**:
   - Adjust CSS specificity in your theme
   - Use the theme customization options
   - Check for conflicting CSS rules

3. **Performance Issues**:
   - Adjust cache duration settings
   - Clear caching plugins after changes
   - Monitor database table sizes

## License

This plugin is proprietary software developed specifically for UQUAL LLC. All rights reserved.

## Version History

### v1.0.0 (Current)
- Initial release with all 5 calculators
- Complete analytics system
- Divi theme integration
- Mobile-responsive design
- Admin dashboard and settings
- Performance optimizations

---

**Developed by**: UQUAL LLC Development Team  
**Plugin Version**: 1.0.0  
**WordPress Tested**: 6.4+  
**PHP Compatibility**: 8.0+