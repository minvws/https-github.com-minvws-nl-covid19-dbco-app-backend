<?php

declare(strict_types=1);

namespace App\Auth\Guard;

use App\Ldap\LdapDnParser;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use RuntimeException;

use function is_string;

class X509Guard implements Guard
{
    use GuardHelpers;

    public const DEFAULT_SUBJECT_DN_HEADER_NAME = 'ssl-client-subject-dn';
    public const DEFAULT_SUBJECT_DN_ATTRIBUTE = 'CN';
    public const DEFAULT_STORAGE_KEY = 'x509_subject_dn_common_name';

    public function __construct(
        UserProvider $provider,
        private readonly Request $request,
        private readonly LdapDnParser $ldapDnParser,
        private readonly string $subjectDnHeaderName = self::DEFAULT_SUBJECT_DN_HEADER_NAME,
        private readonly string $subjectDnAttribute = self::DEFAULT_SUBJECT_DN_ATTRIBUTE,
        private readonly string $storageKey = self::DEFAULT_STORAGE_KEY,
    ) {
        $this->provider = $provider;
    }

    public function user(): ?Authenticatable
    {
        if ($this->hasUser()) {
            return $this->user;
        }

        $value = $this->getSubjectAttribute();
        if ($value === null) {
            return null;
        }

        $user = $this->provider->retrieveByCredentials([$this->storageKey => $value]);
        if ($user === null) {
            return null;
        }

        $this->user = $user;
        return $user;
    }

    private function getSubjectAttribute(): ?string
    {
        $header = $this->request->header($this->subjectDnHeaderName);
        if (!is_string($header) || empty($header)) {
            return null;
        }

        return $this->ldapDnParser->extractAttribute($header, $this->subjectDnAttribute);
    }

    public function validate(array $credentials = []): bool
    {
        throw new RuntimeException('Unsupported');
    }
}
