# Uptime Monitoring Tool

A Laravel-based web application for monitoring website uptime and sending webhook notifications.

## Author

**Sathish Kumar M**
- LinkedIn: [pplcallmesatz](https://www.linkedin.com/in/pplcallmesatz/)
- Role: Design Team Lead
- Location: Karur, India

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/MariaDB

## Installation

1. Clone the repositorybash
git clone https://github.com/yourusername/uptime-monitoring-tool.git
cd uptime-monitoring-tool

2. Install PHP dependencies
```bash
composer install
```

3. Install NPM packages
```bash
npm install
```

4. Environment Setup
```bash
# Copy the example env file
cp .env.example .env

# Generate application key
php artisan key:generate
```

5. Configure Database
Update your `.env` file with your database credentials:
```env
DB_HOST=127.0.0.1
DB_DATABASE=monitoring_tool
DB_USERNAME=root
DB_PASSWORD=
```

6. Run Migrations
```bash
php artisan migrate
```

## Running the Application

1. Start the Development Server
```bash
php artisan serve --port=4000
```

2. Compile Assets
```bash
npm run dev
```

3. Start the Scheduler (in a separate terminal)
```bash
php artisan schedule:work
```

Your application will be available at: http://127.0.0.1:4000

## Features

- Website uptime monitoring
- Configurable check intervals
- Webhook notifications for downtime
- Response time tracking
- Status code monitoring
- Detailed logging system

## Configuration

### Site Monitoring Settings
- Check Interval: Set how often to check each site (in minutes)
- Cooling Time: Set delay between webhook notifications (in seconds)
- Webhook URL: URL to receive downtime notifications
- Retry Count: Number of retries before marking site as down

### Logging
- Enable/disable logging per site
- Configure logs retention period
- Set logs per page for pagination

## Troubleshooting

If you encounter any issues:

1. Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

2. Verify database connectivity:
```bash
php artisan db:monitor
```

3. Clear application cache:
```bash
php artisan config:clear
php artisan cache:clear
```

## License

[MIT License](LICENSE.md)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
