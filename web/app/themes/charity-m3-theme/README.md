# Charity M3 Theme

A modern, secure, and performant WordPress theme for charities and NGOs, built with Roots Bedrock, Acorn, and Material Design 3.

## Features

*   **Modern Development Workflow:** Uses Vite for fast development and optimized builds.
*   **Component-Based Architecture:** Built with Lit web components and styled with StyleXJS for a maintainable and scalable frontend.
*   **Gutenberg-First:** Provides a rich set of custom Gutenberg blocks for easy content creation.
*   **Donation System:** Includes a robust donation system powered by Stripe, with support for one-time and recurring donations, as well as earmarking and "on behalf of" donations.
*   **Newsletter System:** A complete newsletter system with campaign management, subscriber management, and tracking.
*   **Analytics Dashboard:** An in-depth analytics dashboard that provides insights into donations and newsletter subscriptions.
*   **Secure:** Built with security in mind, with a focus on data validation, sanitization, and nonce verification.

## Getting Started

### Prerequisites

*   [Node.js](https://nodejs.org/) (v16 or higher)
*   [Composer](https://getcomposer.org/)
*   A local WordPress environment (we recommend [Trellis](https://roots.io/trellis/))

### Installation

1.  Clone this repository into your `wp-content/themes` directory.
2.  Navigate to the theme directory in your terminal: `cd web/app/themes/charity-m3-theme`
3.  Install PHP dependencies: `composer install`
4.  Install Node.js dependencies: `npm install`
5.  Run the development server: `npm run dev`

### Configuration

1.  **Stripe:** Add your Stripe API keys and webhook secret to your `.env` file:
    ```
    STRIPE_PUBLIC_KEY=pk_...
    STRIPE_SECRET_KEY=sk_...
    STRIPE_WEBHOOK_SECRET=whsec_...
    ```
2.  **Stripe Webhook:** Create a new webhook in your Stripe dashboard and point it to `https://your-domain.com/wp-json/charitym3/v1/stripe-webhooks`. Select the following events:
    *   `payment_intent.succeeded`
    *   `invoice.payment_succeeded`
    *   `customer.subscription.deleted`

## Documentation

### Components

The theme includes a variety of reusable Lit components, which can be found in `resources/scripts/components`. Each component is in its own file and is responsible for its own template, styles, and logic.

### Gutenberg Blocks

The theme provides a number of custom Gutenberg blocks, which are defined in `app/Blocks`. Each block has a corresponding Blade view in `resources/views/blocks`.

### Services

The theme's backend logic is organized into services, which can be found in `app/Services`. These services are responsible for things like handling donations, managing newsletters, and providing analytics data.

### REST API

The theme exposes a number of custom REST API endpoints, which are defined in the service providers in `app/Providers`. These endpoints are used by the frontend components to communicate with the backend.

### GraphQL

The theme also includes a GraphQL schema with mutations for subscribing to the newsletter. The schema is defined in `app/Providers/NewsletterServiceProvider.php`.

## Security

The theme has been built with security in mind. All user input is validated and sanitized, and all database queries are prepared to prevent SQL injection. Nonces are used to protect against CSRF attacks, and user capabilities are checked to prevent unauthorized access. The Content Security Policy is as restrictive as possible to prevent XSS attacks.
