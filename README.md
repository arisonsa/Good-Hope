# Charity M3 - A Modern WordPress Theme for Non-Profits

## Intent & Vision

This project aims to create a professional, high-performance, and easy-to-manage WordPress theme specifically for charities, NGOs, and non-profit organizations.

The core vision is to empower administrators by providing not just a beautiful design, but a suite of powerful, integrated tools that address the real-world needs of a non-profit. This includes robust systems for fundraising (donations), community engagement (newsletters), and dynamic content presentation.

Technically, the goal is to build this on a cutting-edge, developer-friendly foundation that is both modern and maintainable, utilizing the best of the WordPress ecosystem (Roots.io) and contemporary frontend technologies (Lit, StyleXJS, Vite).

## Key Features

- **Advanced Newsletter System:** Manage subscribers, create and send email campaigns with tracking, and view analytics.
- **Gift Entry Management:** Accept one-time and recurring donations securely via Stripe, with a user account portal for donors to manage their subscriptions.
- **Modern Component Library:** A rich set of Lit/StyleXJS web components for Heroes, Cards, Grids, Carousels, and more, all styled with Material Design 3.
- **Powerful Gutenberg Blocks:** Custom blocks that make it easy for admins to use the component library to build complex page layouts without writing code.
- **Block Patterns:** Pre-designed page layouts that can be inserted with a single click.
- **Headless Ready:** A comprehensive WP-GraphQL schema exposes all custom data for use with decoupled frontends.
- **Developer Experience:** Built on Bedrock and Acorn, with a modern TypeScript and Vite.js build process.

## Inspiration

This project draws structural and functional inspiration from leading non-profit websites like **[Americares.org](https://www.americares.org/)**.

Key patterns adopted from such sites include:
-   **Prominent Crisis Alerts:** The ability to feature an urgent appeal on the homepage.
-   **Clear Program Showcases:** Using card-based grids to present different areas of work.
-   **Distinct Calls to Action:** Strong, clear buttons and CTA sections to encourage donations and engagement.
-   **Focus on Trust:** Providing components (like stats and testimonials) to build visitor trust and confidence.

## Theme Structure

This theme follows a modern, decoupled architecture:

-   **Backend:** WordPress with a **Bedrock** folder structure for better organization. **Acorn** is used to structure theme logic into Service Providers (e.g., `DonationServiceProvider`, `CPTServiceProvider`).
-   **Frontend Rendering:** A hybrid approach. **Blade** templates (`.blade.php`) are used for overall page structure and server-rendered views. **Lit** is used to create modern, interactive Web Components for the dynamic parts of the UI.
-   **Styling:** **StyleXJS** is the engine for styling all components, using a design token system based on Google's **Material Design 3 (M3)**. This ensures type-safe, optimized, and consistent styling.
-   **Build Process:** **Vite.js** is used for fast, modern frontend asset bundling and development (HMR).
-   **Content Management:** The **Gutenberg block editor** is the primary tool for admins. The theme provides a rich library of custom blocks that render the theme's Lit Web Components.

## Documentation

For full details on setup, usage, and development, please see the `/docs` directory:

- **[User Guide](./docs/user-guide.md):** For WordPress administrators and content editors.
- **[Developer Guide](./docs/developer-guide.md):** For developers working on or extending the theme.

## Quick Start

1.  **Prerequisites:** Bedrock-based WordPress installation, Composer, Node.js, npm.
2.  **Installation:**
    - Clone this repository into your Bedrock `web/app/themes/` directory.
    - Run `composer install` in the theme directory.
    - Run `npm install` in the theme directory.
3.  **Configuration:**
    - Copy `.env.example` to `.env` in your Bedrock project root.
    - Add your Stripe API keys (`STRIPE_PUBLISHABLE_KEY`, `STRIPE_SECRET_KEY`) and Stripe Webhook Secret (`STRIPE_WEBHOOK_SECRET`) to your `.env` file.
4.  **Build Assets:**
    - For development: `npm run dev`.
    - For production: `npm run build`.
5.  **Activate:** Activate the "Charity M3" theme in your WordPress admin.

---
This project was developed with the assistance of Jules, an AI Engineer.