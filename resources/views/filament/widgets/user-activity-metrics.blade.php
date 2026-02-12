<div class="activity-card">
    <style>
        .activity-card {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 14px;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            color: #e2e8f0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .card-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .country-item {
            margin-bottom: 18px;
            position: relative;
        }

        .country-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .flag {
            font-size: 16px;
        }

        .count {
            position: absolute;
            right: 0;
            top: 0;
            font-size: 13px;
            color: #94a3b8;
        }

        .bar {
            height: 6px;
            background: #1e293b;
            border-radius: 4px;
            margin-top: 8px;
            overflow: hidden;
        }

        .fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #6366f1);
            border-radius: 4px;
            transition: width 0.4s ease;
        }

        /* Highlight top country */
        .country-item.top .fill {
            background: linear-gradient(90deg, #22c55e, #16a34a);
        }

        .country-item.top .name {
            color: #22c55e;
        }
    </style>

    <div class="card-header">
        <div class="icon">🌍</div>
        <h3>User Activity Metrics</h3>
    </div>

    <div class="country-list">
        @foreach($topCountries as $index => $data)
            <div class="country-item {{ $index === 0 ? 'top' : '' }}">
                <div class="country-info">
                    <span class="flag">{{ $data['flag'] }}</span>
                    <span class="name">{{ $data['country'] }}</span>
                </div>
                <span class="count">{{ $data['count'] }}</span>
                <div class="bar">
                    <div class="fill" style="width: {{ $data['percentage'] }}%"></div>
                </div>
            </div>
        @endforeach
        
        @if(empty($topCountries))
            <div class="text-sm text-gray-400">No activity data available</div>
        @endif
    </div>
</div>
