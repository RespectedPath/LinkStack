<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds `theme_customization` to users — a JSON blob holding the
// Appearance-editor values (colors, background, typography, buttons,
// avatar). Single column per spec. Nullable so users who haven't
// customized anything fall through to their theme's defaults.
//
// Shape:
// {
//   "colors":     { "primary", "background", "text", "button_text" },
//   "background": { "type", "solid", "gradient_start", "gradient_end",
//                   "gradient_direction", "image_url" },
//   "typography": { "font" },          // Google Fonts family name, or empty
//   "buttons":    { "shape", "style" }, // pill|rounded|square, filled|outline|soft
//   "avatar":     { "shape" }           // circle|rounded_square
// }

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Text rather than native json() for SQLite dev compatibility.
            $table->text('theme_customization')->nullable()->after('redirect_url');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('theme_customization');
        });
    }
};
