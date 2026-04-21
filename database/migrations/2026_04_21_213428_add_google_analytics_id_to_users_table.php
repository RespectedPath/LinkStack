<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds `google_analytics_id` to users so each user can configure a
// GA4 measurement ID for their own public bio page, loaded alongside
// any platform-level tracking ID from .env.

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_analytics_id')->nullable()->after('stripe_account_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_analytics_id');
        });
    }
};
