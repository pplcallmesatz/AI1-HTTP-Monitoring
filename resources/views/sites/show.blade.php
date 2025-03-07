<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $site->name }} - Logs</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">{{ $site->name }} - Logs</h1>
            <a href="{{ route('sites.index') }}" 
               class="text-gray-600 hover:text-gray-800">
                Back to List
            </a>
        </div>

        <div class="mb-6 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Monitoring Information</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Last Check:</p>
                    <p class="font-medium">{{ $site->last_check_at ? $site->last_check_at->format('d-m-Y h:i A') : 'Never' }}</p>
                </div>
                
                <div>
                    <p class="text-gray-600">Current Status:</p>
                    <p class="font-medium">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $site->is_down ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $site->is_down ? 'DOWN' : 'UP' }}
                        </span>
                    </p>
                </div>

                @if($site->is_down)
                    <div>
                        <p class="text-gray-600">Webhook Retries:</p>
                        <p class="font-medium {{ $site->webhook_retry_count >= $site->max_retries ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $site->webhook_retry_count }}/{{ $site->max_retries }} retries used
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-600">Last Webhook Sent:</p>
                        <p class="font-medium">
                            {{ $site->last_webhook_sent_at ? $site->last_webhook_sent_at->format('d-m-Y h:i A') : 'Never' }}
                        </p>
                    </div>

                    @if($site->last_webhook_sent_at)
                        <div>
                            <p class="text-gray-600">Cooling Period Status:</p>
                            @php
                                $secondsSinceLastWebhook = now()->diffInSeconds($site->last_webhook_sent_at);
                                $coolingPeriod = ($site->cooling_time ?? 2) * 60;
                                $remainingCooling = max(0, $coolingPeriod - $secondsSinceLastWebhook);
                            @endphp
                            <p class="font-medium">
                                @if($remainingCooling > 0)
                                    <span class="text-yellow-600">{{ ceil($remainingCooling/60) }} minutes remaining</span>
                                @else
                                    <span class="text-green-600">Ready for next webhook</span>
                                @endif
                            </p>
                        </div>
                    @endif
                @endif

                <div>
                    <p class="text-gray-600">Check Interval:</p>
                    <p class="font-medium">Every {{ $site->check_interval }} minutes</p>
                </div>

                <div>
                    <p class="text-gray-600">Webhook Configuration:</p>
                    <p class="font-medium">
                        Max Retries: {{ $site->max_retries }}<br>
                        Cooling Time: {{ $site->cooling_time }} minutes
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Response Code
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Response Time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Message
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Webhook Sent
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $log->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->status_code !== 0 ? $log->status_code : 'Connection Failed' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($log->response_time, 2) }}ms
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $log->message }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $log->webhook_sent ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $log->webhook_sent ? 'Yes' : 'No' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</body>
</html> 
