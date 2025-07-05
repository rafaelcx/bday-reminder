<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Class StaticScope
 *
 * A lightweight, static-scoped registry for managing application-wide state in a more controlled
 * and structured manner than native PHP globals. This is especially useful for:
 *
 * -- Simple, centralized dependency injection.
 * -- Singleton creation
 * -- Lightweight, static, per-process in-memory caching.
 *
 * Each value is stored under a two-level key: a `namespace` and a `key`, which together form a
 * fully qualified string key (e.g., `logger.default`, `config.db`).
 *
 * Example usage:
 *
 * Register a singleton dependency:
 * StaticScope::getOrCreate('self::class', 'key', fn() => new Klass(...));
 *
 * Retrieve it later
 * $mailer = StaticScope::get('self::class', 'key');
 * 
 *
 * This class is **not thread-safe** and should be used in single-process environments
 * (e.g., typical PHP web requests or CLI scripts).
 */
class StaticScope {
    
    /**
     * Internal storage for all scoped values.
     * Keys are fully qualified (e.g., `namespace.key`).
     */
    private static array $scope = [];

    /**
     * Retrieves a previously stored value from the static scope or null.
     */
    public static function get(string $namespace, string $key): mixed {
        $full_key = self::createScopeKey($namespace, $key);
        return self::$scope[$full_key] ?? null;
    }

    /**
     * Retrieves a value if it exists, or computes and stores it using the given factory callback.
     * Useful for lazy initialization of singletons.
     */
    public static function getOrCreate(string $namespace, string $key, callable $create_fn): mixed {
        $scope_index = self::createScopeKey($namespace, $key);

        if (!isset(self::$scope[$scope_index])) {
            self::$scope[$scope_index] = $create_fn();
        }

        return self::$scope[$scope_index];
    }

    /**
     * Initializes or overwrite a value for a given two-level key.
     */
    public static function set(string $namespace, string $key, mixed $value): void {
        $scope_index = self::createScopeKey($namespace, $key);
        self::$scope[$scope_index] = $value;
    }

    /**
     * Clears all stored values from the static scope.
     * 
     * This should only be called at test teardowns, since its use can be disatrous throughout normal
     * code logic due to singleton or caching mishehaving. 
     */
    public static function clear(): void {
        self::$scope = [];
    }

    private static function createScopeKey(string $namespace, string $key): string {
        return $namespace . '.' . $key;
    }

}
