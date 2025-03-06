<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Site</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-6">Create New Site</h1>

                <form action="{{ route('sites.store') }}" method="POST">
                    @csrf

                    <!-- Name Field -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL Field -->
                    <div class="mb-4">
                        <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                        <input type="url" name="url" id="url" value="{{ old('url') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Check Interval Field -->
                    <div class="mb-4">
                        <label for="check_interval" class="block text-sm font-medium text-gray-700">Check Interval (minutes)</label>
                        <input type="number" name="check_interval" id="check_interval" value="{{ old('check_interval', 5) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('check_interval')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Webhook URL Field -->
                    <div class="mb-4">
                        <label for="webhook_url" class="block text-sm font-medium text-gray-700">Webhook URL (optional)</label>
                        <input type="url" name="webhook_url" id="webhook_url" value="{{ old('webhook_url') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('webhook_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Enable Logging Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Enable Logging</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="hidden" name="enable_logging" value="0">
                                <input type="checkbox" 
                                       name="enable_logging" 
                                       value="1" 
                                       {{ old('enable_logging', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2">Enable Logging</span>
                            </label>
                        </div>
                        @error('enable_logging')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Logs Per Page Field -->
                    <div class="mb-4" id="logsPerPageField">
                        <label for="logs_per_page" class="block text-sm font-medium text-gray-700">Logs Per Page</label>
                        <input type="number" name="logs_per_page" id="logs_per_page" value="{{ old('logs_per_page', 10) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('logs_per_page')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cooling Time Field -->
                    <div class="mb-4">
                        <label for="cooling_time" class="block text-sm font-medium text-gray-700">Webhook Cooling Time (minutes)</label>
                        <input type="number" name="cooling_time" id="cooling_time" value="{{ old('cooling_time', 3) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Time to wait between webhook retries</p>
                        @error('cooling_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Max Retries Field -->
                    <div class="mb-4">
                        <label for="max_retries" class="block text-sm font-medium text-gray-700">Maximum Webhook Retries</label>
                        <input type="number" name="max_retries" id="max_retries" value="{{ old('max_retries', 3) }}"
                               min="1" max="10"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Maximum number of webhook retry attempts (1-10)</p>
                        @error('max_retries')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hidden Active Field -->
                    <input type="hidden" name="is_active" value="1">

                    <!-- Submit Button -->
                    <div class="flex justify-between">
                        <a href="{{ route('sites.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Create Site
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/hide logs per page field based on enable logging checkbox
        document.querySelector('input[type="checkbox"][name="enable_logging"]').addEventListener('change', function() {
            document.getElementById('logsPerPageField').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html> 