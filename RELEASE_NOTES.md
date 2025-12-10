# Release Notes v1.1.0

**Title:** v1.1.0 - Enhanced Threat Detection, Notifications & Developer Experience

**Description:**

This release brings significant enhancements to S1b Passport Guard, focusing on observability, developer experience, and robust threat detection.

## ✨ New Features

-   **Native Notification Channels:** Added support for Slack, Discord, and Email notifications for real-time threat alerts.
-   **JSON Output Support:** All CLI commands now support JSON output for easier programmatic consumption and integration with external tools.
-   **Enhanced Dashboard:** Improved CLI dashboard with better visualization of token metrics and threats.
-   **Composite Indexes:** Optimized database performance with new composite indexes on `oauth_token_metrics`.

## 📚 Documentation & Community

-   **New Project Banner:** Added a visual identity to the project.
-   **Comprehensive Guide:** Added `GUIDE.md` covering architecture, security philosophy, and advanced usage.
-   **Roadmap:** Added `ROADMAP.md` to outline future development plans.
-   **SEO & Badges:** Improved README visibility and metadata.

## 🛠 Fixes & Improvements

-   **CI/CD:** Fixed GitHub Actions workflow failures and test directory tracking.
-   **License:** Clarified "Source Available" license terms.
-   **Code Quality:** Addressed various static analysis and linting issues.

## 📦 Installation

```bash
composer require s1b-team/s1b-passport-guard
```
