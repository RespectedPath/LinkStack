<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Live-preview Appearance editor — colors, background, typography,
 * button + avatar shape. Saves to users.theme_customization (JSON).
 * Public rendering picks this up in UserController::littlelink() and
 * injects override CSS into the linkstack-head-end stack.
 *
 * This is a per-user customization layer on top of the existing
 * theme system (users.theme). The selected theme still provides
 * layout + structure; values here override visual properties.
 */
class AppearanceController extends Controller
{
    /** Canonical list of Google Fonts available in the font picker. */
    public const GOOGLE_FONTS = [
        'Outfit', 'Inter', 'Poppins', 'Playfair Display', 'Merriweather',
        'Roboto Mono', 'DM Sans', 'Lora', 'Nunito', 'Raleway',
    ];

    /**
     * Real brand hexes for the "Brand colors" mode of the social
     * icons panel. Render emits per-brand CSS rules from this map.
     * Names match the Font Awesome class suffixes used in the icons
     * (e.g. fa-instagram → 'instagram').
     */
    public const BRAND_COLORS = [
        'instagram' => '#E4405F',
        'facebook'  => '#1877F2',
        'x-twitter' => '#000000',
        'github'    => '#181717',
        'linkedin'  => '#0A66C2',
        'tiktok'    => '#000000',
        'youtube'   => '#FF0000',
        'threads'   => '#000000',
        'twitch'    => '#9146FF',
        'pinterest' => '#E60023',
        'snapchat'  => '#FFFC00',
        'reddit'    => '#FF4500',
        'telegram'  => '#26A5E4',
        'behance'   => '#1769FF',
        'dribbble'  => '#EA4C89',
        'mastodon'  => '#6364FF',
        'bluesky'   => '#0085FF',
        'whatsapp'  => '#25D366',
        'discord'   => '#5865F2',
    ];

    /** Defaults — the shape every saved blob must conform to. */
    public static function defaults(): array
    {
        return [
            'colors' => [
                'primary'     => '#3b82f6',
                'background'  => '#ffffff',
                'text'        => '#111111',
                'button_text' => '#ffffff',
            ],
            'background' => [
                'type'               => 'solid',     // solid | gradient | image
                'solid'              => '#ffffff',
                'gradient_start'     => '#3b82f6',
                'gradient_end'       => '#8b5cf6',
                'gradient_direction' => 'to bottom', // to bottom | to right | to bottom right
                'image_url'          => '',
            ],
            'typography' => [
                'font' => '',  // empty = theme default
            ],
            'buttons' => [
                'shape' => 'rounded',  // pill | rounded | square
                'style' => 'filled',   // filled | outline | soft
            ],
            'avatar' => [
                'shape' => 'circle',   // circle | rounded_square
            ],
            'social_icons' => [
                'color'            => 'auto',    // auto | brand | custom
                'color_custom'     => '#111111', // hex (only used when color === 'custom')
                'size'             => 'medium',  // small | medium | large | xl
                'spacing'          => 'normal',  // tight | normal | loose
                'background_style' => 'none',    // none | circle | rounded | solid
                'hover'            => 'lift',    // none | lift | glow | scale | colorshift
            ],
        ];
    }

    public function show(Request $request)
    {
        $user = User::find(Auth::id());
        $saved = self::loadForUser($user);
        return view('studio.appearance', [
            'user'    => $user,
            'saved'   => $saved,
            'fonts'   => self::GOOGLE_FONTS,
        ]);
    }

    public function save(Request $request)
    {
        $data = $this->validated($request);

        $user = User::find(Auth::id());
        $user->theme_customization = json_encode($data, JSON_UNESCAPED_SLASHES);
        $user->save();

        return redirect('/studio/edit#appearance')->with('success', 'Appearance saved.');
    }

    public function reset(Request $request)
    {
        $user = User::find(Auth::id());
        $user->theme_customization = null;
        $user->save();
        return redirect('/studio/edit#appearance')->with('success', 'Appearance reset to defaults.');
    }

    /**
     * Upload a background image. The browser has already resized the
     * file to ≤1920px longest dimension at JPEG q=0.82 (see
     * appearance.js), so the max:2048 server cap is just a safety net.
     * Writes the file to /assets/img/background-img/ and stores the
     * leading-slash path on users.theme_customization JSON under
     * background.image_url. Also flips background.type to 'image' so
     * the upload immediately takes effect.
     */
    public function uploadBackgroundImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], [
            'image.image' => __('messages.The selected file must be an image'),
            'image.mimes' => __('messages.The image must be') . ' JPEG, JPG, PNG, webP.',
            'image.max'   => __('messages.The image size should not exceed 2MB'),
        ]);

        $userId = Auth::id();
        $dir = base_path('assets/img/background-img');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $this->removeBackgroundFileIfPresent($userId);

        $file = $request->file('image');
        $fileName = $userId . '_' . time() . '.' . $file->extension();
        $file->move($dir, $fileName);

        $user = User::find($userId);
        $cfg  = self::loadForUser($user);
        $cfg['background']['type']      = 'image';
        $cfg['background']['image_url'] = '/assets/img/background-img/' . $fileName;
        $user->theme_customization = json_encode($cfg, JSON_UNESCAPED_SLASHES);
        $user->save();

        return response()->json(['ok' => true, 'image_url' => $cfg['background']['image_url']]);
    }

    /**
     * Remove the user's uploaded background. Deletes the file on disk
     * and clears background.image_url in the JSON blob. Type is reset
     * to 'solid' so the page falls through to the solid color rather
     * than showing a broken image URL.
     */
    public function removeBackgroundImage(Request $request)
    {
        $userId = Auth::id();
        $this->removeBackgroundFileIfPresent($userId);

        $user = User::find($userId);
        if ($user->theme_customization) {
            $cfg = self::loadForUser($user);
            $cfg['background']['image_url'] = '';
            if (($cfg['background']['type'] ?? '') === 'image') {
                $cfg['background']['type'] = 'solid';
            }
            $user->theme_customization = json_encode($cfg, JSON_UNESCAPED_SLASHES);
            $user->save();
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Delete any existing background file(s) for this user. Same
     * scandir-directly pattern the avatar cleanup uses so a stale
     * reference never spins us into an infinite loop.
     */
    private function removeBackgroundFileIfPresent($userId): void
    {
        $dir = base_path('assets/img/background-img');
        if (!is_dir($dir)) return;
        $prefix = $userId . '_';
        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            if (strpos($entry, $prefix) !== 0) continue;
            $full = $dir . '/' . $entry;
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }

    /**
     * Returns the user's saved customization merged against defaults so
     * consumers can always reach every key without null-checking. Used
     * by both the editor view and (public-side) by UserController.
     */
    public static function loadForUser($user): array
    {
        $defaults = self::defaults();
        if (!$user || empty($user->theme_customization)) {
            return $defaults;
        }
        $parsed = json_decode($user->theme_customization, true);
        if (!is_array($parsed)) {
            return $defaults;
        }
        return self::mergeDeep($defaults, $parsed);
    }

    /**
     * Returns true if the user has any customization saved (vs falling
     * through to pure theme defaults). Used by public rendering to
     * skip injection entirely when nothing's customized.
     */
    public static function isConfigured($user): bool
    {
        return $user && !empty($user->theme_customization);
    }

    private static function mergeDeep(array $a, array $b): array
    {
        foreach ($b as $k => $v) {
            if (is_array($v) && isset($a[$k]) && is_array($a[$k])) {
                $a[$k] = self::mergeDeep($a[$k], $v);
            } else {
                $a[$k] = $v;
            }
        }
        return $a;
    }

    private function validated(Request $request): array
    {
        $hex = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];

        $rules = [
            'colors.primary'     => $hex,
            // colors.background is no longer in the UI (the Background
            // section's solid / gradient covers it); it stays in storage
            // from defaults() so the image-type fallback color survives.
            'colors.text'        => $hex,
            'colors.button_text' => $hex,

            'background.type'               => ['required', 'in:solid,gradient,image'],
            'background.solid'              => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background.gradient_start'     => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background.gradient_end'       => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background.gradient_direction' => ['nullable', 'in:to bottom,to right,to bottom right'],
            // Accept http(s) URLs (legacy / external) or local paths
            // starting with '/assets/' (uploaded backgrounds). Empty
            // clears the field.
            'background.image_url'          => ['nullable', 'string', 'max:2048', 'regex:#^(|https?://.+|/assets/img/background-img/.+)$#i'],

            'typography.font' => ['nullable', 'string', 'in:' . implode(',', array_merge([''], self::GOOGLE_FONTS))],

            'buttons.shape' => ['required', 'in:pill,rounded,square'],
            'buttons.style' => ['required', 'in:filled,outline,soft'],

            'avatar.shape' => ['required', 'in:circle,rounded_square,off'],

            'social_icons.color'            => ['required', 'in:auto,brand,custom'],
            'social_icons.color_custom'     => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'social_icons.size'             => ['required', 'in:small,medium,large,xl'],
            'social_icons.spacing'          => ['required', 'in:tight,normal,loose'],
            'social_icons.background_style' => ['required', 'in:none,circle,rounded,solid'],
            'social_icons.hover'            => ['required', 'in:none,lift,glow,scale,colorshift'],
        ];

        $validated = $request->validate($rules);

        // Normalise: empty background fields get defaults so the stored
        // blob is always shaped the same way.
        $defaults = self::defaults();
        return self::mergeDeep($defaults, $validated);
    }
}
