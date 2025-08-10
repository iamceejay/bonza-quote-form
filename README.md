# Bonza Quote Form

A WordPress plugin that adds a quote request form to your website with admin management.

## What it does

* Adds a quote form to any page or post using shortcode
* Stores quote requests in the database
* Provides admin interface to manage quotes (approve, reject, delete)
* Includes email validation and required field checking

## How to use

Add this shortcode to any page or post:

```
[bonza_quote_form]
```

### Shortcode options

```
[bonza_quote_form title="Get Your Quote" service_types="Web Design,SEO,Marketing"]
```

Available options:
* `title` - Custom form title
* `service_types` - Comma separated list for dropdown
* `submit_text` - Custom submit button text
* `ajax` - Enable/disable AJAX (default: true)

## Setup instructions

1. Upload plugin to `wp-content/plugins/bonza-quote-form/`
2. Activate the plugin in WordPress admin
3. Database table is created automatically
4. Use the shortcode where you want the form
5. Manage quotes in admin under "Bonza Quotes" menu

## Admin features

Navigate to **Bonza Quotes** in WordPress admin to:
* View all quote submissions
* Filter by status (pending, approved, rejected)
* Change quote status with action buttons
* Delete quotes
* Search quotes by name, email, or service type

## Running tests

The plugin includes automated tests to verify functionality.

### Requirements

* PHP 7.4 or higher
* PHPUnit installed
* WordPress environment

### Install PHPUnit

```bash
# Mac with Homebrew
brew install phpunit

# Or with Composer
composer global require phpunit/phpunit
```

### Run tests

```bash
cd wp-content/plugins/bonza-quote-form
phpunit
```

Expected output shows successful CRUD operations and validation tests.

## Technical assumptions

* WordPress 5.0 or higher
* MySQL database with CREATE TABLE permissions  
* PHP with mysqli extension
* Modern browser with JavaScript enabled for AJAX features
* Admin users have 'manage_options' capability

## Database

Creates table `wp_bonza_quotes` with these fields:
* id, name, email, service_type, notes, status, created_at, updated_at

## Notes

* Form submissions default to "pending" status
* Test data uses "@test.com" emails and is automatically cleaned up
* Plugin follows WordPress coding standards and security practices
* All user input is sanitized and validated
* AJAX submission includes nonce security tokens
* Compatible with WordPress multisite installations

## Support

Check that required files exist in includes/ directory:
* `class-bonza-quote-form-activator.php`
* `class-bonza-quote-form-quote.php`

If tests fail, verify WordPress is properly loaded and database permissions are correct.