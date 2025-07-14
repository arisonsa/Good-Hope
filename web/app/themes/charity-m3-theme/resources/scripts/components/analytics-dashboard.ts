import { LitElement, html } from 'lit';
import { customElement, state } from 'lit/decorators.js';
import * as stylex from '@stylexjs/stylex';
import apiFetch from '@wordpress/api-fetch';
import { Chart, registerables } from 'chart.js';
import { M3SysColors, M3TypeScale } from '../tokens';

// Register all Chart.js components
Chart.register(...registerables);

const styles = stylex.create({
    dashboardGrid: {
        display: 'grid',
        gridTemplateColumns: 'repeat(1, 1fr)',
        gap: '1.5rem',
        '@media (min-width: 1024px)': {
            gridTemplateColumns: 'repeat(2, 1fr)',
        },
    },
    chartCard: {
        backgroundColor: M3SysColors.surfaceContainer,
        padding: '1.5rem',
        borderRadius: '12px',
    },
    chartTitle: {
        ...M3TypeScale.titleLarge,
        color: M3SysColors.onSurface,
        marginBottom: '1rem',
    },
    loadingState: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        minHeight: '200px',
        color: M3SysColors.onSurfaceVariant,
    },
});

@customElement('analytics-dashboard')
export class AnalyticsDashboard extends LitElement {
    @state() private donationsData: any = null;
    @state() private subscribersData: any = null;
    @state() private earmarkData: any = null;
    @state() private isLoading = true;
    @state() private errorMessage = '';

    private donationsChartInstance?: Chart;
    private subscribersChartInstance?: Chart;
    private earmarkChartInstance?: Chart;

    // Use Light DOM for easier Chart.js canvas interaction and global M3 styling
    createRenderRoot() {
        return this;
    }

    async connectedCallback() {
        super.connectedCallback();
        this.fetchData();
    }

    async fetchData() {
        this.isLoading = true;
        this.errorMessage = '';
        try {
            const [donations, subscribers, donationStats] = await Promise.all([
                apiFetch({ path: '/charitym3/v1/stats/donations-over-time?period=day&limit=30' }),
                apiFetch({ path: '/charitym3/v1/stats/subscribers-over-time?period=day&limit=30' }),
                apiFetch({ path: '/charitym3/v1/stats/donations?days=30' }),
            ]);
            this.donationsData = this.formatChartData(donations, 'Donations', 'total_amount');
            this.subscribersData = this.formatChartData(subscribers, 'New Subscribers', 'total_subscribers');
            this.earmarkData = this.formatPieChartData(donationStats.earmark_breakdown, 'Earmark Breakdown', 'total_amount', 'earmark');
        } catch (error: any) {
            this.errorMessage = error.message || 'Failed to fetch analytics data.';
        } finally {
            this.isLoading = false;
        }
    }

    formatChartData(data: any[], label: string, dataKey: string) {
        if (!data) return null;
        const labels = data.map(item => item.date_key);
        const values = data.map(item => item[dataKey]);
        return {
            labels,
            datasets: [{
                label,
                data: values,
                fill: false,
                borderColor: M3SysColors.primary,
                backgroundColor: M3SysColors.primary,
                tension: 0.1,
            }],
        };
    }

    formatPieChartData(data: any[], label: string, dataKey: string, labelKey: string) {
        if (!data) return null;
        const labels = data.map(item => item[labelKey]);
        const values = data.map(item => item[dataKey]);
        return {
            labels,
            datasets: [{
                label,
                data: values,
                backgroundColor: [
                    M3SysColors.primary,
                    M3SysColors.secondary,
                    M3SysColors.tertiary,
                    M3SysColors.error,
                    '#FFC107',
                    '#4CAF50',
                    '#2196F3',
                ],
                hoverOffset: 4,
            }],
        };
    }

    updated(changedProperties: Map<string | number | symbol, unknown>) {
        if (changedProperties.has('donationsData') && this.donationsData) {
            this.createDonationsChart();
        }
        if (changedProperties.has('subscribersData') && this.subscribersData) {
            this.createSubscribersChart();
        }
        if (changedProperties.has('earmarkData') && this.earmarkData) {
            this.createEarmarkChart();
        }
    }

    private createDonationsChart() {
        const canvas = this.querySelector('#donationsChart') as HTMLCanvasElement;
        if (!canvas || !this.donationsData) return;
        if (this.donationsChartInstance) this.donationsChartInstance.destroy();

        this.donationsChartInstance = new Chart(canvas, {
            type: 'line',
            data: this.donationsData,
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: (value) => `$${value}` } } },
            },
        });
    }

    private createSubscribersChart() {
        const canvas = this.querySelector('#subscribersChart') as HTMLCanvasElement;
        if (!canvas || !this.subscribersData) return;
        if (this.subscribersChartInstance) this.subscribersChartInstance.destroy();

        this.subscribersChartInstance = new Chart(canvas, {
            type: 'bar',
            data: this.subscribersData,
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        });
    }

    private createEarmarkChart() {
        const canvas = this.querySelector('#earmarkChart') as HTMLCanvasElement;
        if (!canvas || !this.earmarkData) return;
        if (this.earmarkChartInstance) this.earmarkChartInstance.destroy();

        this.earmarkChartInstance = new Chart(canvas, {
            type: 'pie',
            data: this.earmarkData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        });
    }

    render() {
        if (this.isLoading) {
            return html`<div ${stylex.props(styles.loadingState)}>Loading Analytics...</div>`;
        }
        if (this.errorMessage) {
            return html`<div ${stylex.props(styles.loadingState)} style="color: ${M3SysColors.error};">${this.errorMessage}</div>`;
        }

        return html`
            <div ${stylex.props(styles.dashboardGrid)}>
                <div class="donations-chart-card" ${stylex.props(styles.chartCard)}>
                    <h2 ${stylex.props(styles.chartTitle)}>Donations (Last 30 Days)</h2>
                    <canvas id="donationsChart"></canvas>
                </div>
                <div class="subscribers-chart-card" ${stylex.props(styles.chartCard)}>
                    <h2 ${stylex.props(styles.chartTitle)}>New Subscribers (Last 30 Days)</h2>
                    <canvas id="subscribersChart"></canvas>
                </div>
                <div class="earmark-chart-card" ${stylex.props(styles.chartCard)}>
                    <h2 ${stylex.props(styles.chartTitle)}>Earmark Breakdown (Last 30 Days)</h2>
                    <canvas id="earmarkChart"></canvas>
                </div>
            </div>
        `;
    }
}

declare global {
    interface HTMLElementTagNameMap {
        'analytics-dashboard': AnalyticsDashboard;
    }
}
