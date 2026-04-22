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

        return redirect()->route('showAppearance')->with('success', 'Appearance saved.');
    }

    public function reset(Request $request)
    {
        $user = User::find(Auth::id());
        $user->theme_customization = null;
        $user->save();
        return redirect()->route('showAppearance')->with('success', 'Appearance reset to defaults.');
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
            'colors.background'  => $hex,
            'colors.text'        => $hex,
            'colors.button_text' => $hex,

            'background.type'               => ['required', 'in:solid,gradient,image'],
            'background.solid'              => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background.gradient_start'     => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background.gradient_end'       => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background.gradient_direction' => ['nullable', 'in:to bottom,to right,to bottom right'],
            'background.image_url'          => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/.+)?$/i'],

            'typography.font' => ['nullable', 'string', 'in:' . implode(',', array_merge([''], self::GOOGLE_FONTS))],

            'buttons.shape' => ['required', 'in:pill,rounded,square'],
            'buttons.style' => ['required', 'in:filled,outline,soft'],

            'avatar.shape' => ['required', 'in:circle,rounded_square'],
        ];

        $validated = $request->validate($rules);

        // Normalise: empty background fields get defaults so the stored
        // blob is always shaped the same way.
        $defaults = self::defaults();
        return self::mergeDeep($defaults, $validated);
    }
}
