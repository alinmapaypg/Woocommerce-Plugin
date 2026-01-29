# AlinmaPay Payment Gateway – WooCommerce Plugin

This repository contains the **WooCommerce Payment Gateway plugin** for integrating **AlinmaPay Payment Gateway (PG)** with WordPress WooCommerce stores.

The plugin enables merchants to accept payments securely through AlinmaPay using supported payment methods.

---

## 📄 Document Information

- **Integration Type:** WooCommerce
- **Plugin Version:** 3.0.3
- **Prepared For:** AlinmaPay PG Integration

---

## 🛒 Introduction

WooCommerce is a widely used open-source e-commerce platform for WordPress.  
Integrating WooCommerce with **AlinmaPay Payment Gateway** allows merchants to accept online payments securely through AlinmaPay.

Before starting integration, ensure you have an **active AlinmaPay merchant account**.

---

## ⚙️ Requirements

- **PHP Version:** 8.2.12
- **WordPress Version:** 6.8.3
- **WooCommerce Version:** 10.1.2

---

## 🔑 Prerequisites

- AlinmaPay Merchant Dashboard account
- Understanding of payment flow
- API credentials generated from AlinmaPay Dashboard

### Required Credentials
| Attribute | Description |
|---------|-------------|
| Terminal ID | Unique terminal identifier issued by AlinmaPay |
| Terminal Password | Terminal password issued by AlinmaPay |
| Merchant Key | Secret key used for request/response hash (must be kept secure) |

---

## 🔧 Plugin Configuration

1. Log in to **WordPress Admin**
2. Install and activate the **AlinmaPay WooCommerce Plugin**
3. Go to **WooCommerce → Settings → Payments**
4. Select **AlinmaPay Payment Gateway**
5. Enter the required credentials:
   - Terminal ID
   - Terminal Password
   - Merchant Key
  
6. In Metadata pass {"receiptUrl":"https://{{websiteURL}}/wordpress/?wc-api=WC_AlinmaPay_payment"}
7. Save changes

---

## 🌐 Payment Gateway Receipt URL

Configure the default receipt URL in the **AlinmaPay Merchant Portal**:

