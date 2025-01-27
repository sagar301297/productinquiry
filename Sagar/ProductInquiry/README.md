# Product Inquiry Module (`sagar/productinquiry`)

## Overview

The `sagar/productinquiry` module is a Magento 2 custom module designed to allow customers to submit inquiries about products. The module supports:
- A front-end inquiry form for customers.
- Automated email notifications to admin.
- Admin tools to manage inquiries and respond to customers.

---

## Module Architecture

The module is built following Magento 2's standard structure. Key components include:

- **Controller**: Handles requests for submitting the inquiry form and displaying success/error pages.
- **Model**: Manages interactions with the `sagar_product_inquiry` database table.
- **Helper**: Provides utility functions for reusable tasks, such as retrieving email configurations.
- **UI Components**: Renders the admin grid for inquiries.
- **View Layer**: Includes the front-end inquiry form and layout updates.
- **Email Templates**: Configurable email notifications for admin and customer.

---

## Features

### 1. Front-End Form Integration
- Displays a "Product Inquiry" form on the product details page.
- Fields include customer name, email, phone, and inquiry message.
- Front-end validation for required fields and valid email format.
- A success or error page is shown based on form submission status.

### 2. Email Notification Process
- **Customer Email**: Sends a email to the customer when admin responds.
- **Admin Email**: Sends an email to the admin with inquiry details.
- Email templates are customizable:
  - `email/product_inquiry_customer.html` for customer notifications.
  - `email/product_inquiry_admin.html` for admin notifications.

### 3. Admin Inquiry Management
- A new admin grid available under `Product Inquiry`.
- Admin can:
  - View all inquiries with filters and sorting options.
  - Reply to inquiries directly from the grid.
  - Reply notifications are sent to the customer's email.

### 4. Configurations
- Enable or disable the module functionality.
- Configure the admin notification email address.
- Pick any email templates for admin and customer.

### 5. Additional Features
- Product details (name, id) send to admin for better clarity.
- Reduce error rate with Try/Catch and Unit testing.
- Ajax submission for faster and better experience.

---

## Installation

1. Copy the module to the `app/code/Sagar/ProductInquiry` directory.
2. Run the following Magento commands:
   ```bash
   php bin/magento module:enable Sagar_ProductInquiry
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:flush

## Future Improvement
- Modern design and popup inquiry form
- Product-specific form visibility
- Customer group restrictions
- Out-of-stock product limitations
- reCAPTCHA integration for spam prevention
- Automated reminders for unanswered inquiries
- Visual highlighting of pending inquiries
- Responsive admin management grid