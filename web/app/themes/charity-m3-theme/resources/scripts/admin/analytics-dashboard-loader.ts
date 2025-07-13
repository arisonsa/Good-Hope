/**
 * Loader script for the Analytics Dashboard admin page.
 */
import '../components/analytics-dashboard'; // Registers <analytics-dashboard>

document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('analytics-dashboard-root');
    if (rootElement) {
        const dashboard = document.createElement('analytics-dashboard');
        rootElement.appendChild(dashboard);
    }
});
