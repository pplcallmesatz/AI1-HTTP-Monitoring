<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="10">
    <title>Monitored Sites</title>
    @vite('resources/css/app.css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.10.7/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.10.7/plugin/relativeTime.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Monitored Sites</h1>
            <a href="{{ route('sites.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Add New Site
            </a>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input type="text" 
                       id="searchInput" 
                       placeholder="Search sites..." 
                       class="w-full px-4 py-2 pl-10 pr-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="sitesGrid">
            @foreach($sites as $site)
            <div class="bg-white rounded-lg shadow-md overflow-hidden site-card" 
                 data-site-id="{{ $site->id }}"
                 data-site-name="{{ $site->name }}"
                 data-site-url="{{ $site->url }}">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $site->name }}</h2>
                        <div class="flex space-x-2">
                            <span class="site-status px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $site->is_down ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $site->is_down ? 'Down' : 'Up' }}
                            </span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $site->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $site->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <a href="{{ $site->url }}" target="_blank" 
                           class="text-indigo-600 hover:text-indigo-800 break-all">
                            {{ $site->url }}
                        </a>
                    </div>

                    <div class="text-sm text-gray-600 mb-4">
                        <p>Check Interval: {{ $site->check_interval }} minutes</p>
                        <p>Last Checked: 
                            <span class="last-checked" data-timestamp="{{ $site->last_check_at ? $site->last_check_at->toISOString() : '' }}">
                                {{ $site->last_checked }}
                            </span>
                        </p>
                        @if($site->enable_logging)
                            <p>
                                <a href="{{ route('sites.show', $site) }}" class="text-indigo-600 hover:text-indigo-800">
                                    <span class="logs-count">{{ $site->logs_count }}</span> log entries
                                </a>
                            </p>
                        @else
                            <p class="text-gray-400">Logging disabled</p>
                        @endif
                    </div>

                    <div class="mt-2">
                        <span class="text-gray-600">Webhook Cooling Time:</span>
                        <span class="font-medium">{{ $site->cooling_time }} minutes</span>
                    </div>

                    <!-- Add Webhook Retry Information -->
                    @if($site->is_down)
                        <div class="mt-2 space-y-2">
                            <!-- Retry Count -->
                            <div class="flex items-center">
                                <span class="text-gray-600">Webhook Retries:</span>
                                <span class="ml-1 font-medium {{ $site->webhook_retry_count >= $site->max_retries ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $site->webhook_retry_count }}/{{ $site->max_retries }}
                                </span>
                            </div>

                            <!-- Cooling Period Status -->
                            @if($site->last_webhook_sent_at)
                                @php
                                    $secondsSinceLastWebhook = now()->diffInSeconds($site->last_webhook_sent_at);
                                    $coolingPeriod = ($site->cooling_time ?? 2) * 60;
                                    $remainingCooling = max(0, $coolingPeriod - $secondsSinceLastWebhook);
                                @endphp
                                <div class="flex items-center">
                                    <span class="text-gray-600">Cooling Status:</span>
                                    @if($remainingCooling > 0)
                                        <span class="ml-1 text-yellow-600">{{ ceil($remainingCooling/60) }}m remaining</span>
                                    @else
                                        <span class="ml-1 text-green-600">Ready</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('sites.edit', $site) }}" 
                           class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-1 rounded text-sm">
                            Edit
                        </a>
                        <form action="{{ route('sites.destroy', $site) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded text-sm"
                                    onclick="return confirm('Are you sure you want to delete this site?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const siteCards = document.querySelectorAll('.site-card');

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();

            siteCards.forEach(card => {
                const siteName = card.dataset.siteName.toLowerCase();
                const siteUrl = card.dataset.siteUrl.toLowerCase();
                const isVisible = siteName.includes(searchTerm) || siteUrl.includes(searchTerm);
                card.style.display = isVisible ? 'block' : 'none';
            });
        });

        // Update timestamps
        function updateTimestamps() {
            document.querySelectorAll('.last-checked').forEach(element => {
                const timestamp = element.dataset.timestamp;
                if (timestamp) {
                    const timeAgo = dayjs(timestamp).fromNow();
                    const formattedDate = dayjs(timestamp).format('DD-MM-YYYY hh:mm A');
                    element.textContent = `${timeAgo} (${formattedDate})`;
                }
            });
        }

        // Listen for real-time updates
        window.Echo.channel('sites')
            .listen('SiteStatusUpdated', (e) => {
                const card = document.querySelector(`[data-site-id="${e.site.id}"]`);
                if (card) {
                    const statusBadge = card.querySelector('.site-status');
                    statusBadge.className = `site-status px-2 py-1 text-xs font-semibold rounded-full ${
                        e.site.is_down ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                    }`;
                    statusBadge.textContent = e.site.is_down ? 'Down' : 'Up';

                    const lastChecked = card.querySelector('.last-checked');
                    if (lastChecked) {
                        lastChecked.dataset.timestamp = e.site.last_check_at;
                        updateTimestamps();
                    }

                    const logsCount = card.querySelector('.logs-count');
                    if (logsCount) {
                        logsCount.textContent = e.site.logs_count;
                    }
                }
            });

        // Update timestamps every minute
        setInterval(updateTimestamps, 60000);

        // Initial update
        updateTimestamps();
    </script>
</body>
</html> 