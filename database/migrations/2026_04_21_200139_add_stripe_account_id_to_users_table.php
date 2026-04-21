<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds `stripe_account_id` to users so a LinkStack user can connect
// their own Stripe account via Stripe Connect OAuth and have payments
// on the public page routed to their account (transfer_data destination,
// on_behalf_of, zero application fee per product decision).

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable()->after('custom_domain');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_account_id');
        });
    }
};
