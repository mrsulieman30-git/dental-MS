<?php

namespace DentalOS\DB;

/**
 * Shared database logic and schema helpers
 */
class Schema {
    public static function dentalTable(string $name, callable $callback) {
        // Shared schema logic (e.g. multi-tenant columns)
    }
}
