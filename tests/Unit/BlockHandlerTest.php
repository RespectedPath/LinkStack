<?php

namespace Tests\Unit;

use App\Services\BlockHandler;
use Tests\TestCase;

/**
 * The failure mode that motivated the loader: two different block
 * types' handlers — which all declare the same function name — must
 * coexist in ONE process, and repeated loads must not redeclare.
 */
class BlockHandlerTest extends TestCase
{
    public function test_two_different_handlers_coexist_in_one_process(): void
    {
        $stripe  = BlockHandler::for('stripe_payment');
        $contact = BlockHandler::for('contact_form');

        $this->assertNotNull($stripe);
        $this->assertNotNull($contact);
        $this->assertNotSame($stripe, $contact, 'each type gets its own namespaced function');
        $this->assertTrue(is_callable($stripe));
        $this->assertTrue(is_callable($contact));
    }

    public function test_repeated_loads_return_the_cached_callable(): void
    {
        $first  = BlockHandler::for('stripe_payment');
        $second = BlockHandler::for('stripe_payment');

        $this->assertSame($first, $second);
    }

    public function test_unknown_type_returns_null(): void
    {
        $this->assertNull(BlockHandler::for('no_such_block_type'));
    }
}
