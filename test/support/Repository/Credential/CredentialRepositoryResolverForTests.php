<?php

declare(strict_types=1);

namespace Test\Support\Repository\Credential;

use App\Repository\Credential\CredentialRepository;
use App\Repository\Credential\CredentialRepositoryResolver;
use App\Utils\StaticScope;

class CredentialRepositoryResolverForTests extends CredentialRepositoryResolver {

    public static function override(CredentialRepository $repository): void {
        StaticScope::set(parent::class, 'instance', $repository);
    }

}
