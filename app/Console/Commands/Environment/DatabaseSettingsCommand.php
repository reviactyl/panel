<?php

namespace App\Console\Commands\Environment;

use App\Exceptions\PanelException;
use App\Traits\Commands\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\DatabaseManager;

class DatabaseSettingsCommand extends Command
{
    use EnvironmentWriterTrait;

    private const SUPPORTED_DRIVERS = [
        'sqlite' => 'SQLite',
        'mysql' => 'MySQL',
        'mariadb' => 'MariaDB',
        'pgsql' => 'PostgreSQL',
    ];

    protected $description = 'Configure database settings for the Panel.';

    protected $signature = 'p:environment:database
                            {--driver= : The database driver to use (sqlite, mysql, mariadb, pgsql).}
                            {--host= : The connection address for the database server.}
                            {--port= : The connection port for the database server.}
                            {--database= : The database to use.}
                            {--username= : Username to use when connecting.}
                            {--password= : Password to use for this database.}';

    protected array $variables = [];

    /**
     * DatabaseSettingsCommand constructor.
     */
    public function __construct(private DatabaseManager $database, private Kernel $console)
    {
        parent::__construct();
    }

    /**
     * Handle command execution.
     *
     * @throws PanelException
     */
    public function handle(): int
    {
        $driver = $this->normalizeDriver($this->option('driver'));
        $defaultDriver = config('database.default', 'sqlite');
        if (! empty($driver) && ! array_key_exists($driver, self::SUPPORTED_DRIVERS)) {
            $this->output->error(sprintf('Unsupported database driver "%s" provided. Supported drivers are: %s.', $driver, implode(', ', array_keys(self::SUPPORTED_DRIVERS))));

            return 1;
        }

        if (empty($driver)) {
            $driver = $this->choice(
                'Database Driver',
                self::SUPPORTED_DRIVERS,
                array_key_exists($defaultDriver, self::SUPPORTED_DRIVERS) ? $defaultDriver : 'sqlite'
            );
        }

        $this->variables['DB_CONNECTION'] = $driver;

        if ($driver === 'sqlite') {
            $this->setupSqliteConfiguration();
        } else {
            $this->setupServerConfiguration($driver);
        }

        try {
            $this->testConnection();
        } catch (\PDOException $exception) {
            $message = $driver === 'sqlite'
                ? sprintf('Unable to open the SQLite database at "%s". The error returned was "%s".', $this->variables['DB_DATABASE'], $exception->getMessage())
                : sprintf('Unable to connect to the %s server using the provided credentials. The error returned was "%s".', self::SUPPORTED_DRIVERS[$driver], $exception->getMessage());

            $this->output->error($message);
            $this->output->error('Your connection credentials have NOT been saved. You will need to provide valid connection information before proceeding.');

            if ($this->confirm('Go back and try again?')) {
                $this->database->disconnect('_panel_command_test');

                return $this->handle();
            }

            return 1;
        }

        $this->writeToEnvironment($this->variables);

        $this->info($this->console->output());

        return 0;
    }

    /**
     * Setup environment variables for a sqlite connection.
     */
    private function setupSqliteConfiguration(): void
    {
        $this->variables['DB_DATABASE'] = $this->option('database') ?? $this->ask(
            'SQLite Database Path',
            config('database.connections.sqlite.database', database_path('panel.sqlite'))
        );
    }

    /**
     * Setup environment variables for server-backed connections.
     */
    private function setupServerConfiguration(string $driver): void
    {
        $configPath = sprintf('database.connections.%s', $driver);

        $this->output->note('It is highly recommended to not use "localhost" as your database host as we have seen frequent socket connection issues. If you want to use a local connection you should be using "127.0.0.1".');
        $this->variables['DB_HOST'] = $this->option('host') ?? $this->ask(
            'Database Host',
            config($configPath.'.host', '127.0.0.1')
        );

        $this->variables['DB_PORT'] = $this->option('port') ?? $this->ask(
            'Database Port',
            config($configPath.'.port', $driver === 'pgsql' ? 5432 : 3306)
        );

        $this->variables['DB_DATABASE'] = $this->option('database') ?? $this->ask(
            'Database Name',
            config($configPath.'.database', 'panel')
        );

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->output->note('Using the "root" account for MySQL connections is not only highly frowned upon, it is also not allowed by this application. You\'ll need to have created a MySQL user for this software.');
        }

        $this->variables['DB_USERNAME'] = $this->option('username') ?? $this->ask(
            'Database Username',
            config($configPath.'.username', 'reviactyl')
        );

        $askForDatabasePassword = true;
        if (! empty(config($configPath.'.password')) && $this->input->isInteractive()) {
            $this->variables['DB_PASSWORD'] = config($configPath.'.password');
            $askForDatabasePassword = $this->confirm('It appears you already have a database connection password defined, would you like to change it?');
        }

        if ($askForDatabasePassword) {
            $this->variables['DB_PASSWORD'] = $this->option('password') ?? $this->secret('Database Password');
        }
    }

    /**
     * Normalize command input aliases to Laravel connection driver names.
     */
    private function normalizeDriver(?string $driver): string
    {
        return match (strtolower((string) $driver)) {
            'postgresql', 'postgres', 'pgsql' => 'pgsql',
            default => strtolower((string) $driver),
        };
    }

    /**
     * Test that we can connect to the provided database instance.
     */
    private function testConnection(): void
    {
        $driver = $this->variables['DB_CONNECTION'];

        if ($driver === 'sqlite') {
            config()->set('database.connections._panel_command_test', [
                'driver' => 'sqlite',
                'database' => $this->variables['DB_DATABASE'],
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            config()->set('database.connections._panel_command_test', [
                'driver' => $driver,
                'host' => $this->variables['DB_HOST'],
                'port' => $this->variables['DB_PORT'],
                'database' => $this->variables['DB_DATABASE'],
                'username' => $this->variables['DB_USERNAME'],
                'password' => $this->variables['DB_PASSWORD'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'strict' => true,
            ]);
        } else {
            config()->set('database.connections._panel_command_test', [
                'driver' => 'pgsql',
                'host' => $this->variables['DB_HOST'],
                'port' => $this->variables['DB_PORT'],
                'database' => $this->variables['DB_DATABASE'],
                'username' => $this->variables['DB_USERNAME'],
                'password' => $this->variables['DB_PASSWORD'],
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
                'search_path' => 'public',
                'sslmode' => config('database.connections.pgsql.sslmode', 'prefer'),
            ]);
        }

        $this->database->connection('_panel_command_test')->getPdo();
    }
}
