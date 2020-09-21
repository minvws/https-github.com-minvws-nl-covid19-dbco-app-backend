<?php
declare(strict_types=1);

namespace App\Application\Helpers;

use Psr\Container\ContainerInterface;

final class RandomKeyGenerator implements RandomKeyGeneratorInterface {
    protected string $allowedChars;

    public function __construct(array $options)
    {
        $this->allowedChars = $options['allowedChars'];
    }

    public function generateToken(int $length = 6): string
    {
        $token = '';

        $all = str_split($this->allowedChars);
        for ($i = 0; $i < $length; $i++) {
            $token .= $all[self::csarray_rand($all)];
        }
        $token = str_shuffle($token);
        return $token;
    }

    private static function csarray_rand(array $arr){
        $keys = array_keys($arr);
        $count = count($keys);
        if($count === 0){
            return NULL;
        }
        $csrand = random_int(0, count($keys) - 1);
        return $keys[$csrand];
    }

    public function generateKey(int $length = 32): string
    {
        $bytes = random_bytes($length);
        return base64_encode($bytes);
    }
}
