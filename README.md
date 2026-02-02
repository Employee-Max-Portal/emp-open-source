# 🏢 EMP — Employee Max Portal
> **A System-First Business Operating System designed to eliminate operational chaos and drive predictable execution.**

[![Framework](https://img.shields.io/badge/Framework-CodeIgniter%203-red.svg)](https://codeigniter.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-Open%20Source-green.svg)](#license)
[![Developed by SOHUB](https://img.shields.io/badge/Developed%20by-SOHUB-orange.svg)](https://sohub.com.bd/)

EMP (Employee Max Portal) is a modular "Business Operating System" built to streamline execution, reduce manual effort, and enforce accountability without micromanagement. By centralizing ownership and automating follow-ups, EMP creates a single source of truth for modern organizations.

---

## 🌟 Executive Summary
Most organizations struggle with fragmented systems for HR, Payroll, and Tasks. **EMP solves this** by providing a centralized portal that automates coordination. Currently powering **20+ production environments**, it helps teams move from reactive "chasing" to proactive "execution."

---

## 🧠 Core Philosophy
- **System First**: Technology carries the responsibility; humans focus on execution.
- **Visibility > Micromanagement**: Real-time dashboards replace constant status meetings.
- **Outcome-Driven**: Success is measured by objective results and KPIs.
- **Predictable Operations**: Automated alerts ensure nothing falls through the cracks.

---

## 🛠️ System Overview & Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Backend** | PHP 7.4+ | CodeIgniter 3.x (MVC) |
| **Database** | MySQL 8.0+ (Optimized Relational Schema) |
| **Frontend** | Bootstrap 3.3.6 | jQuery | Chart.js |
| **Security** | JWT Authentication | RBAC | CSRF & XSS Protection |
| **API** | RESTful Architecture for Mobile & Third-party |

---

## 📦 Core Modules
* **HR Engine**: QR-based attendance, leave management, and organizational hierarchy.
* **Operations**: Task tracking, goal management, and document control.
* **Finance**: Payroll processing, expense approvals, and fund requisitions.
* **Communication**: Multi-channel alerts via **Telegram, Email, and FCM**.
* **Administration**: Granular Role-Based Access Control (RBAC) with 10+ predefined roles.

---

## 🚀 Quick Start Guide

### System Requirements
* **PHP**: 7.4 or higher
* **MySQL**: 8.0 or higher
* **Web Server**: Apache (with `mod_rewrite`) or Nginx
* **Memory**: 2GB RAM minimum (4GB recommended)

### Installation in 5 Steps

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/Employee-Max-Portal/emp-open-source.git](https://github.com/Employee-Max-Portal/emp-open-source.git)
    cd emp-open-source
    ```

2.  **Configure Database**
    Rename `application/config/database.php.example` to `database.php` and add your MySQL credentials.

3.  **Import Schema**
    Import the SQL file located at `sql/emp.sql` into your MySQL database.

4.  **Set Permissions**
    ```bash
    chmod -R 755 uploads/ application/logs/
    ```

5.  **Access the Portal**
    Open your browser at `http://localhost/emp-open-source/`
    * **Username**: `superman`
    * **Password**: `superman`

---

## 🗺️ Roadmap
* **Phase 1 (Open Source Foundation)**: Dockerization, API documentation, and community guidelines.
* **Phase 2 (Scalability)**: Multi-tenant architecture and custom domain support.
* **Phase 3 (SaaS Evolution)**: Automated billing, tenant provisioning, and AI-driven analytics.

---

## 🤝 Contribution & Support
We welcome contributions from the global developer community! Please review our **[Contribution Guidelines](https://github.com/Employee-Max-Portal/emp-open-source/wiki/Contribution-Guidelines)** before submitting a Pull Request.

* **Documentation**: [Visit our Full Wiki](https://github.com/Employee-Max-Portal/emp-open-source/wiki)
* **Community**: Join the conversation in [GitHub Discussions](https://github.com/Employee-Max-Portal/emp-open-source/discussions)
* **Commercial Support**: For enterprise deployment or custom development, visit **[SOHUB](https://sohub.com.bd/)**.

---
**Designed for Execution. Built for Growth.** Developed with ❤️ by **[SOHUB (Solution Hub Technologies)](https://sohub.com.bd/)**
