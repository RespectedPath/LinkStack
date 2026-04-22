<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds a "temporary redirect" pair to users: a boolean switch and a
// destination URL. When both are set, visitors to /@{littlelink} are
// 302'd to the URL before any page content renders.

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('redirect_enabled')->default(false)->after('google_analytics_id');
            $table->text('redirect_url')->nullable()->after('redirect_enabled');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['redirect_enabled', 'redirect_url']);
        });
    }
};
