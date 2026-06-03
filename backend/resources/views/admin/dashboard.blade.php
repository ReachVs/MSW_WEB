@extends('layouts.admin')

@section('title', 'Dashboard')
@section('eyebrow', 'Operations Center')
@section('page-title', 'Admin Dashboard')

@section('content')
    @php
        $metrics = [
            ['label' => 'Today Bookings', 'value' => '18', 'note' => '6 waiting confirmation'],
            ['label' => 'Active Work Orders', 'value' => '42', 'note' => '12 due today'],
            ['label' => 'Ready For Pickup', 'value' => '09', 'note' => '3 unpaid invoices'],
            ['label' => 'Low Stock Parts', 'value' => '07', 'note' => 'needs reorder'],
        ];

        $stages = [
            ['name' => 'Booking Request', 'count' => 12, 'tone' => 'blue'],
            ['name' => 'Inspection', 'count' => 8, 'tone' => 'amber'],
            ['name' => 'Repair In Progress', 'count' => 15, 'tone' => 'red'],
            ['name' => 'Quality Check', 'count' => 5, 'tone' => 'violet'],
            ['name' => 'Ready / Closed', 'count' => 9, 'tone' => 'green'],
        ];

        $bookings = [
            ['time' => '08:30', 'customer' => 'Sok Dara', 'service' => 'Oil change and inspection', 'status' => 'Pending'],
            ['time' => '10:00', 'customer' => 'Chan Mesa', 'service' => 'Brake system repair', 'status' => 'Confirmed'],
            ['time' => '13:30', 'customer' => 'Vannak Kim', 'service' => 'Engine diagnostic', 'status' => 'Waiting'],
            ['time' => '15:00', 'customer' => 'Rina Pich', 'service' => 'Tire replacement', 'status' => 'Confirmed'],
        ];

        $activities = [
            'Work order #MSW-1042 moved to quality check.',
            'Invoice #INV-883 marked as paid.',
            'Rear brake pad stock reached reorder level.',
            'Technician assigned to booking #BK-209.',
        ];
    @endphp

    <section class="metrics-grid" aria-label="Workshop metrics">
        @foreach ($metrics as $metric)
            <article class="metric-card">
                <p class="label">{{ $metric['label'] }}</p>
                <div class="metric-row">
                    <span class="metric-value">{{ $metric['value'] }}</span>
                    <span class="metric-note">{{ $metric['note'] }}</span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="dashboard-grid">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Service Flow</p>
                    <h2 class="panel-title">Workshop process status</h2>
                </div>
                <button class="btn" type="button">View board</button>
            </div>

            <div class="flow-grid">
                @foreach ($stages as $stage)
                    <article class="flow-card tone-{{ $stage['tone'] }}">
                        <span class="flow-bar"></span>
                        <div>
                            <p class="flow-name">{{ $stage['name'] }}</p>
                            <p class="flow-count">{{ $stage['count'] }}</p>
                            <p class="flow-unit">jobs</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="panel">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Activity</p>
                    <h2 class="panel-title">Recent updates</h2>
                </div>
            </div>
            <div class="activity-list">
                @foreach ($activities as $activity)
                    <div class="activity-item">
                        <span class="activity-dot"></span>
                        <span>{{ $activity }}</span>
                    </div>
                @endforeach
            </div>
        </aside>
    </section>

    <section class="panel table-panel">
        <div class="table-toolbar">
            <div>
                <p class="eyebrow">Booking Queue</p>
                <h2 class="panel-title">Today service appointments</h2>
            </div>
            <div class="toolbar-actions">
                <button class="btn" type="button">Filter</button>
                <button class="btn btn-dark" type="button">Add appointment</button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        @php
                            $statusClass = 'status-' . strtolower($booking['status']);
                        @endphp
                        <tr>
                            <td class="cell-strong">{{ $booking['time'] }}</td>
                            <td>{{ $booking['customer'] }}</td>
                            <td class="cell-muted">{{ $booking['service'] }}</td>
                            <td>
                                <span class="status-pill {{ $statusClass }}">{{ $booking['status'] }}</span>
                            </td>
                            <td class="text-right">
                                <button class="link-button" type="button">Open</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
