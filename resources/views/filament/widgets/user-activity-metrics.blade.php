<div class="activity-card">
    <style>
        .activity-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            width: 100%;
            color: #0f172a;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .card-header .icon-svg {
            width: 20px;
            height: 20px;
            color: #3b82f6;
        }

        .card-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }

        .country-item {
            margin-bottom: 18px;
            position: relative;
        }

        .country-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .flag-img {
            width: 20px;
            height: auto;
            border-radius: 2px;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.05);
        }

        .flag-placeholder {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .count {
            position: absolute;
            right: 0;
            top: 0;
            font-size: 13px;
            color: #64748b;
        }

        .bar {
            height: 6px;
            background: #f1f5f9;
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
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .country-item.top .name {
            color: #059669;
        }
    </style>

    <div class="card-header">
        <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m0 18c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
        </svg>
        <h3>User Activity Metrics</h3>
    </div>

    <div class="country-list">
        @foreach($topCountries as $index => $data)
            <div class="country-item {{ $index === 0 ? 'top' : '' }}">
                <div class="country-info">
                    @if($data['flag_url'])
                        <img src="{{ $data['flag_url'] }}" alt="{{ $data['country'] }}" class="flag-img">
                    @else
                        <span class="flag-placeholder">🌐</span>
                    @endif
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
