# TheSkyBakery WordPress Theme

A complete WordPress theme clone of theskybakery.com.au - a professional bakery and cake shop website.

## Features

- **WooCommerce Integration**: Full e-commerce functionality for selling cakes and bakery items
- **Custom Cake Builder**: Interactive step-by-step cake customization with live pricing
- **Multi-Store Support**: Manage multiple store locations with addresses, hours, and maps
- **Pickup Scheduling**: Customer can select pickup location, date, and time at checkout
- **Responsive Design**: Fully responsive layout optimized for all devices
- **AJAX Cart**: Smooth add-to-cart experience without page reload
- **Newsletter Subscription**: Built-in newsletter signup functionality
- **Demo Data Import**: One-click import of sample products, stores, and pages

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 6.0 or higher

## Installation

### Method 1: Upload via WordPress Admin

1. Download the theme as a `.zip` file
2. Go to **Appearance > Themes** in your WordPress admin
3. Click **Add New** > **Upload Theme**
4. Select the `.zip` file and click **Install Now**
5. Activate the theme

### Method 2: FTP Upload

1. Extract the theme folder
2. Upload `theskybakery-theme` to `/wp-content/themes/`
3. Go to **Appearance > Themes** and activate

## Setup

### 1. Install Required Plugins

- **WooCommerce** (Required for products)
- **Advanced Custom Fields** (Optional, for extended functionality)

### 2. Import Demo Data

1. Go to **Appearance > Demo Import**
2. Select the content you want to import
3. Click **Import Demo Data**

This will create:
- 5 store locations
- 15 sample products with categories
- Homepage slider content
- Required pages (Menu, Stores, Cake Builder, About, Contact)
- Navigation menus

### 3. Configure Theme Settings

Go to **Appearance > Customize** to configure:

- **Site Identity**: Logo, site title, tagline
- **Colors**: Primary and secondary colors
- **Contact Info**: Phone, email, address
- **Social Media**: Facebook, Instagram, Twitter, YouTube links
- **Footer**: Copyright text
- **Pickup Settings**: Minimum order notice time

### 4. Set Up Menus

1. Go to **Appearance > Menus**
2. Create a Primary Menu and assign to "Primary Menu" location
3. Create a Top Menu and assign to "Top Menu" location

### 5. Set Homepage

1. Go to **Settings > Reading**
2. Select "A static page"
3. Set "Homepage" to "Home" page

## Theme Structure

```
theskybakery-theme/
├── assets/
│   ├── css/
│   │   └── main.css
│   ├── js/
│   │   └── main.js
│   ├── images/
│   └── fonts/
├── inc/
│   ├── ajax-handlers.php
│   ├── cake-builder.php
│   ├── custom-post-types.php
│   ├── customizer.php
│   ├── demo-import.php
│   ├── pickup-scheduling.php
│   ├── store-locations.php
│   └── woocommerce-functions.php
├── templates/
│   ├── template-menu.php
│   ├── template-store.php
│   └── template-cake-builder.php
├── woocommerce/
│   └── (WooCommerce template overrides)
├── style.css
├── functions.php
├── header.php
├── footer.php
├── front-page.php
├── index.php
├── screenshot.png
└── README.md
```

## Custom Post Types

### Store Locations
- **Post Type**: `store_location`
- **Fields**: Address, Phone, Email, Hours, Map Embed, Latitude, Longitude

### Slider
- **Post Type**: `slider`
- **Fields**: Slide Link, Slide Order

## Shortcodes

### Cake Builder
```
[cake_builder]
```
Displays the interactive cake builder with all customization options.

### Store Locations
```
[store_locations]
```
Displays all store locations in a grid.

## Product Categories

The theme supports the following product category structure:

- Birthday
  - Black Forrest
  - Carrot Cake
  - Chocolate Gateau
  - Coffee Cake
  - Colourful Cake
  - Drip Cake
  - Mud Cake
  - Rosie Cake
  - Torte
- Catering
- Cream Cake
- Cup Cake
- Edible Picture
- Others

## Customization

### Colors

Primary colors can be changed in the Customizer. Default values:
- Primary Color: `#c9a86c` (Gold)
- Secondary Color: `#2c2c2c` (Dark)

### Fonts

The theme uses Google Fonts:
- Headings: Playfair Display
- Body: Poppins
- Decorative: Dancing Script

### CSS Variables

```css
:root {
    --primary-color: #c9a86c;
    --secondary-color: #2c2c2c;
    --text-color: #333;
    --light-bg: #f8f8f8;
    --font-body: 'Poppins', sans-serif;
    --font-heading: 'Playfair Display', serif;
    --font-script: 'Dancing Script', cursive;
}
```

## Pickup Scheduling

The pickup scheduling system:
- Requires minimum 24 hours advance notice
- Shows available time slots based on store hours
- Validates date/time selection
- Saves pickup details to order meta

## AJAX Functions

The theme includes these AJAX endpoints:
- `tsb_add_to_cart` - Add products to cart
- `tsb_get_cart_count` - Update cart counter
- `tsb_newsletter_subscribe` - Newsletter signup
- `tsb_get_pickup_times` - Get available pickup times
- `tsb_add_custom_cake` - Add custom cake to cart

## Support

For support or customization requests, please contact the developer.

## Changelog

### Version 1.0.0
- Initial release
- Full WooCommerce integration
- Custom cake builder
- Multi-store support
- Pickup scheduling
- Demo data import

## License

This theme is licensed under the GPL v2 or later.

---

Made with ❤️ for bakery lovers
