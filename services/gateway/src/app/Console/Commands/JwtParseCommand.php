<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Console\Command;

use function config;

/**
 * This command is meant to be used for testing purposes.
 * When testing the ESB connection, this command can be useful as the payload of the request is included in the token.
 */
final class JwtParseCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'jwt:parse';

    /**
     * @var string
     */
    protected $description = 'Parses a given JWT and prints the contents into the console';

    public function handle(): int
    {
        /** @var string $rawToken */
        $rawToken = $this->ask('Please provide the JWT token');

        if (!$this->confirm('Do you want to use the default JWT secret? [yes|no]', true)) {
            /** @var string $jwtSecret */
            $jwtSecret = $this->secret('Please provide the JWT secret');
        } else {
            /** @var string $jwtSecret */
            $jwtSecret = config('services.jwt.secret');
        }

        $this->output->writeln('Parsing JWT token...');

        $jwtContents = (array) JWT::decode($rawToken, new Key($jwtSecret, 'HS256'));
        $this->output->writeln($jwtContents);

        return self::SUCCESS;
    }
}
