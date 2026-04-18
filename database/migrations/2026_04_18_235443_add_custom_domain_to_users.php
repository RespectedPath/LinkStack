<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds `custom_domain` to users so Mail Minted's per-domain provisioning
// can map a customer's apex (e.g. janesmith.com) to a LinkStack user.
// Upstream LinkStack stores these mappings in a PHP config file, which
// doesn't scale for API-driven provisioning.

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->unique()->after('littlelink_description');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['custom_domain']);
            $table->dropColumn('custom_domain');
        });
    }
};
