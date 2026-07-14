<?php

namespace App\Services;

/**
 * Loads a block type's handler.php and returns its handleLinkType as a
 * callable.
 *
 * Why not just `include` it (the old way)? Every block's handler.php
 * declares the SAME global function, handleLinkType(). With one PHP
 * process per web request that collision is invisible — but in any
 * long-lived process (the test suite, queue workers, a future Octane
 * setup) the second save either fatals with "Cannot redeclare
 * function" (same or different type) or would silently run the WRONG
 * type's handler. The test suite caught this on its first run.
 *
 * Each handler is therefore evaluated once inside its own namespace
 * (BlockHandlers\T<typename>) and cached for the process. Handlers are
 * repo-shipped code — eval here trusts exactly what include trusted.
 *
 * Constraints this imposes on blocks/<type>/handler.php (all 14
 * current handlers already comply):
 *   - plain `<?php` file declaring handleLinkType() (no namespace, no
 *     declare(strict_types=1) — the namespace wrapper must come first)
 *   - `__DIR__` is rewritten to the handler's real directory before
 *     eval, so `require_once __DIR__ . '/currencies.php'` keeps
 *     working (inside eval it would otherwise resolve to THIS file's
 *     directory)
 */
class BlockHandler
{
    /** @var array<string, callable-string> typename → fully-qualified function */
    private static array $loaded = [];

    /**
     * @return callable-string|null  Fully-qualified handleLinkType for
     *                               the type, or null when the block
     *                               ships no handler.php.
     */
    public static function for(string $typename): ?string
    {
        if (isset(self::$loaded[$typename])) {
            return self::$loaded[$typename];
        }

        $path = base_path("blocks/{$typename}/handler.php");
        if (!is_file($path)) {
            return null;
        }

        // Namespace-safe identifier: typenames are [a-z0-9_] directory
        // names, but don't assume — hash anything unexpected.
        $safe = preg_match('/^[A-Za-z0-9_]+$/', $typename) ? $typename : md5($typename);
        $ns = "BlockHandlers\\T{$safe}";
        $fq = "\\{$ns}\\handleLinkType";

        if (!function_exists($fq)) {
            $code = (string) file_get_contents($path);
            $code = preg_replace('/^\s*<\?php/', '', $code, 1);
            $code = str_replace('__DIR__', var_export(dirname($path), true), $code);
            eval("namespace {$ns};{$code}");
        }

        return self::$loaded[$typename] = $fq;
    }
}
