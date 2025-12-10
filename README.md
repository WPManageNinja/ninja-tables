# Ninja Tables – Advanced WordPress Table Builder for Data, Pricing, and Comparison Tables

**Contributors:** techjewel, adreastrian, heera, wpmanageninja  
**Tags:** table builder, responsive table, data table, comparison table, pricing table  
**Requires at least:** 4.5  
**Tested up to:** 6.8  
**Requires PHP:** 7.4  
**Stable tag:** 5.2.4  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Build powerful and responsive WordPress tables in minutes. Create sortable, filterable, and beautiful data tables without writing a single line of code.

[Download Link](https://wordpress.org/plugins/ninja-tables/)

---

## Project Setup

To clone and set up the Ninja Tables project, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/WPManageNinja/ninja-tables.git
   ```

2. **Navigate into the project directory:**
   ```bash
   cd ninja-tables
   ```

3. **Install dependencies:**
   ```bash
   npm install
   ```

4. **Run the development server:**
   ```bash
   npm run dev | watch
   ```

5. **For production build:**
   ```bash
   npm run production
   ```

> Make sure you have **Node.js** and **npm** installed before running these commands.

---

## Directory Structure

```
├── app
│   ├── CPT                  # Custom Post Types
│   └── Hooks                # Action and Filter Hooks
│   └── Library              # Helper Classes
│   └── Http                 # REST API routes, controllers, policies
│   └── Models               # Database Models
│   └── Modules              # Modules
│   └── Services             # Module Services
│   └── Traits               # Reusable Traits
│   └── Views                # Php view files
│   └── App.php     
│   └── ComposerScript.php 
│
├── assets                   # contains css,js, media files
├── boot                     # [internal] contains plugin boot files
├── config                   # [internal] contains plugin config files
├── database                 # [internal] Database migration files
├── language                 # [internal] Language Files
├── resource                 # [internal] Resource Files
├── vendor                   # [internal] Core Framework Files
│
└── ninja-tables.php         # Plugin entry File
```

---

## Description

### Ninja Tables – Fast, Responsive, and Customizable WordPress Table Plugin

**Ninja Tables** is the most intuitive and feature-rich **WordPress table builder plugin** designed for speed and simplicity. Build any type of table — from product lists, pricing comparisons, and data charts to member directories — all without writing a single line of code.

Built with performance and accessibility in mind, Ninja Tables ensures all your tables are **lightweight**, **mobile-friendly**, and **SEO-optimized**.

---

### Powerful Features in the Free Version

* Easy drag-and-drop table builder  
* Responsive table layouts with stackable mode  
* Search, sort, and pagination  
* CSV/JSON import & export  
* Inline cell editing  
* Custom CSS & JS support  
* Conditional column visibility  
* Role-based data control  
* Shortcode for embedding anywhere  
* Table preview with design templates  
* Integration with Fluent Forms (view form data as tables)  

---

### Pro Version Features

* WooCommerce integration – display product or review tables  
* Google Sheets integration – sync live data automatically  
* Advanced filtering and custom search  
* Frontend editing with access control  
* Conditional formatting for data highlighting  
* Custom SQL query-based tables  
* Dynamic column rendering (HTML, buttons, images)  
* Fluent Forms integration with custom field mapping  
* Advanced table styling and custom templates  
* Media and file listing support  
* Advanced actions and bulk editing  
* Customizable tooltips, icons, and buttons  

---

## Installation

1. Upload the plugin files to the `/wp-content/plugins/ninja-tables` directory, or install the plugin via the **WordPress Plugin Installer**.  
2. Activate the plugin through the **‘Plugins’** menu in WordPress.  
3. Navigate to **Ninja Tables → Add Table** to create your first table.  
4. Use the shortcode to display the table on any page or post.

---

## Frequently Asked Questions

### Do I need coding skills to use Ninja Tables?
No. Ninja Tables is built for all users — beginners and developers alike. You can build professional-looking tables using the drag-and-drop builder.

### Are the tables mobile responsive?
Yes. Every table created with Ninja Tables is fully responsive and optimized for all screen sizes. You can also customize how columns stack on smaller devices.

### Can I import or export table data?
Absolutely! You can import and export tables in CSV, JSON, and Excel formats, and even connect with Google Sheets (Pro feature).

### Does Ninja Tables support WooCommerce?
Yes. The Pro version includes advanced WooCommerce integration to create dynamic product or review tables with sorting, filtering, and add-to-cart functionality.

### Can I display WordPress posts or custom post types as tables?
Yes. You can display data from WordPress posts, pages, and any custom post types.

### Can I customize table design?
Yes. Ninja Tables offers full control over styling — colors, fonts, borders, hover effects, and even custom CSS for advanced customization.

---

## Changelog

### 5.2.4 (Date: November 27, 2025)
- Security: Improved REST API sanitization to prevent SQL injection.
- Improve: Stock visibility in FluentCart table

### 5.2.3 (Date: November 11, 2025)
- Added: Fluent Cart Product Table integration
- Added: WooCommerce Reviews table integration
- Added: WooCommerce average rating in products table
- Added: Separate progress bar & progress bar text color options
- Added: Vietnamese translation support
- Fixes: WooCommerce multiple attributes variations duplicate issue
- Fixes: WooCommerce products comparison in stackable table
- Fixes: WooCommerce table theme conflict
- Fixes: WooCommerce dynamic column post meta display issue
- Fixes: Post table conditions date filter issue
- Fixes: Image clickable issue when no link is provided in drag & drop
- Fixes: Button hover issue on drag & drop
- Fixes: Fluent forms table Optional conditions issue
- Fixes: Progress bar style issue
- Improve: Excel formula support
- Improve: Single cart button action from table
- Improve: Pagination style in WooCommerce table
- Improve: Text-domains & data escaping
- Improve: WooCommerce bulk operations
- Tested: Full plugin via Plugin Check(PCP) 

[View complete changelog history](https://ninjatables.com/docs/changelog/)

---

## Support and Documentation

For more information and support:

- [Official Website](https://ninjatables.com/)  
- [Demo](https://ninjatables.com/demo/)  
- [Documentation](https://ninjatables.com/docs/)  
- [YouTube Tutorials](https://www.youtube.com/watch?v=U8AHiC1g3Ew&list=PLXpD0vT4thWGYSCX6fOf7o0eTS9EtU0oK)  
- [Support Tickets](https://wpmanageninja.com/support-tickets/)
