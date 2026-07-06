<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

if (!function_exists('preloadDirectoryFiles')) {
    /**
     * Preload all files in a directory and optionally cache in Redis or Laravel cache.
     *
     * @param string $directory
     * @param string $cacheKey Unique cache key
     * @param int $ttl Cache time in seconds
     * @return array Array of filenames
     */
    function preloadDirectoryFiles(string $directory, string $cacheKey, int $ttl = 3600): array
    {
        static $memoryCache = [];

        // Return from memory if already loaded
        if (isset($memoryCache[$directory])) {
            return $memoryCache[$directory];
        }

        $files = [];
        $fingerprint = md5(json_encode(scandir($directory)));

        // Try Redis first
        if (class_exists('Redis')) {
            try {
                $cached = Redis::get($cacheKey);
                if ($cached) {
                    $cached = json_decode($cached, true);
                    if (isset($cached['fingerprint']) && $cached['fingerprint'] === $fingerprint) {
                        $memoryCache[$directory] = $cached['files'];
                        return $cached['files'];
                    }
                }
            } catch (\Exception $e) {
                // Redis not available, fallback to local memory
            }
        }

        // Scan directory and cache
        $files = scandir($directory);

        // Cache in Redis if possible
        $data = [
            'fingerprint' => $fingerprint,
            'files' => $files
        ];
        if (class_exists('Redis')) {
            try {
                Redis::setex($cacheKey, $ttl, json_encode($data));
            } catch (\Exception $e) {
                // fallback silently
            }
        } else {
            // Laravel cache fallback
            Cache::put($cacheKey, $data, $ttl);
        }

        $memoryCache[$directory] = $files;

        return $files;
    }
}

function findFile($name)
{
    $directory = base_path("/assets/linkstack/images/");
    $files = preloadDirectoryFiles($directory, 'linkstack_images_files');

    $pattern = '/^' . preg_quote($name, '/') . '(_\w+)?\.\w+$/i';
    foreach ($files as $file) {
        if (preg_match($pattern, $file)) {
            return $file;
        }
    }
    return "error.error";
}

function findAvatar($name)
{
    $directory = base_path("assets/img");
    $files = preloadDirectoryFiles($directory, 'assets_img_files');

    $pattern = '/^' . preg_quote($name, '/') . '(_\w+)?\.\w+$/i';
    foreach ($files as $file) {
        if (preg_match($pattern, $file)) {
            return "assets/img/" . $file;
        }
    }
    return "error.error";
}

function findBackground($name)
{
    $directory = base_path("assets/img/background-img/");
    $files = preloadDirectoryFiles($directory, 'assets_img_background_files');

    $pattern = '/^' . preg_quote($name, '/') . '(_\w+)?\.\w+$/i';
    foreach ($files as $file) {
        if (preg_match($pattern, $file)) {
            return $file;
        }
    }
    return "error.error";
}

function analyzeImageBrightness($file) {
    try {
    $file = base_path('assets/img/background-img/'.$file);
  
    // Get image information using getimagesize
    $imageInfo = getimagesize($file);
    if (!$imageInfo) {
      return 'dark';
    }
  
    // Get the image type
    $type = $imageInfo[2];
  
    // Load the image based on its type
    switch ($type) {
      case IMAGETYPE_JPEG:
      case IMAGETYPE_JPEG2000:
        $img = imagecreatefromjpeg($file);
        break;
      case IMAGETYPE_PNG:
        $img = imagecreatefrompng($file);
        break;
      default:
        return 'dark';
    }
  
    // Get image dimensions
    $width = imagesx($img);
    $height = imagesy($img);
  
    // Calculate the average brightness of the image
    $total_brightness = 0;
    for ($x=0; $x<$width; $x++) {
      for ($y=0; $y<$height; $y++) {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $brightness = (int)(($r + $g + $b) / 3);
        $total_brightness += $brightness;
      }
    }
    $avg_brightness = $total_brightness / ($width * $height);
  
    // Determine if the image is more dark or light
    if ($avg_brightness < 128) {
      return 'dark';
    } else {
      return 'light';
    }
      } catch (\Throwable $th) {
          return null;
      }
  }
  
  function infoIcon($tip) {
    echo '
      <div class="d-flex justify-content-center align-items-center">
        <a data-bs-toggle="tooltip" data-bs-placement="bottom" title="' . $tip . '">
          <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.67 1.99927H16.34C19.73 1.99927 22 4.37927 22 7.91927V16.0903C22 19.6203 19.73 21.9993 16.34 21.9993H7.67C4.28 21.9993 2 19.6203 2 16.0903V7.91927C2 4.37927 4.28 1.99927 7.67 1.99927ZM11.99 9.06027C11.52 9.06027 11.13 8.66927 11.13 8.19027C11.13 7.70027 11.52 7.31027 12.01 7.31027C12.49 7.31027 12.88 7.70027 12.88 8.19027C12.88 8.66927 12.49 9.06027 11.99 9.06027ZM12.87 15.7803C12.87 16.2603 12.48 16.6503 11.99 16.6503C11.51 16.6503 11.12 16.2603 11.12 15.7803V11.3603C11.12 10.8793 11.51 10.4803 11.99 10.4803C12.48 10.4803 12.87 10.8793 12.87 11.3603V15.7803Z" fill="currentColor"></path>
          </svg>
        </a>
      </div>
    ';
  }

function external_file_get_contents($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0');
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function uri($path) {
    $url = str_replace(['http://', 'https://'], '', url(''));
    return "//" . $url . "/" . $path;
}

function footer($key)
{
    $upperStr = strtoupper($key);
    if (env('TITLE_FOOTER_'.$upperStr) == "") {
        $title = __('messages.footer.'.$key);
    } else {
        $title = env('TITLE_FOOTER_'.$upperStr);
    }
    return $title;
}

function strip_tags_except_allowed_protocols($str) {
    preg_match_all('/<a[^>]+>(.*?)<\/a>/i', $str, $matches, PREG_SET_ORDER);

    foreach ($matches as $val) {
        if (!preg_match('/href=["\'](http:|https:|mailto:|tel:)[^"\']*["\']/', $val[0])) {
            $str = str_replace($val[0], $val[1], $str);
        }
    }

    return $str;
}

if (!function_exists('purify_user_html')) {
    /**
     * Sanitize user-supplied HTML for safe rendering on public pages.
     *
     * Replaces the old strip_tags() + strip_tags_except_allowed_protocols()
     * combination, which was bypassable: PHP's strip_tags never removes
     * ATTRIBUTES from allowed tags, and the protocol helper only inspected
     * <a> elements. So `<a href="https://x" onclick="…">` and
     * `<p onmouseover="…">` both survived and rendered as live script
     * when ALLOW_USER_HTML=true — a stored-XSS hole (customer content
     * runs on the same origin as the dashboard/admin).
     *
     * HTMLPurifier drops every tag, attribute, and URI scheme not on its
     * allowlist below (all on* event handlers, style, javascript:/data:
     * URIs, etc.), so the output is safe to echo with {!! !!}.
     *
     * Allowlist mirrors the tags the studio's rich-text controls emit
     * (bold / italic / links / lists / headings / quotes).
     */
    function purify_user_html($html) {
        static $purifier = null;

        if ($purifier === null) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed',
                'a[href|title],p,br,strong,b,em,i,u,s,ul,ol,li,blockquote,h2,h3,h4');
            $config->set('URI.AllowedSchemes', [
                'http' => true, 'https' => true, 'mailto' => true, 'tel' => true,
            ]);
            // External links get target=_blank + rel=noopener automatically.
            $config->set('HTML.TargetBlank', true);

            // HTMLPurifier writes a small serializer cache; point it at a
            // writable storage dir (created if missing) so it never tries
            // to write inside vendor/.
            $cacheDir = storage_path('app/htmlpurifier');
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }
            $config->set('Cache.SerializerPath', $cacheDir);

            $purifier = new \HTMLPurifier($config);
        }

        return $purifier->purify((string) $html);
    }
}

if (!function_exists('purge_user_uploads')) {
    /**
     * Delete every uploaded file belonging to a user — their avatar
     * (assets/img) and background image (assets/img/background-img).
     *
     * Single source of truth for "remove this user's files". Called on
     * account deletion (admin panel, self-serve, and the Mail Minted
     * deprovision API) so a cancelled/removed customer doesn't leave
     * orphaned images on disk, and by the storage:reconcile command.
     *
     * File naming matches findAvatar()/findBackground(): "<id>.<ext>"
     * or "<id>_<suffix>.<ext>". The pattern anchors <id> followed by
     * "_" or "." so id "1" never matches "10_x.jpg" (collision-safe).
     *
     * @return int number of files deleted
     */
    function purge_user_uploads($userId): int
    {
        $userId = (string) $userId;
        if ($userId === '' || !ctype_digit($userId)) {
            return 0;
        }

        $dirs = [
            base_path('assets/img'),
            base_path('assets/img/background-img'),
        ];
        $pattern = '/^' . preg_quote($userId, '/') . '(_\w+)?\.\w+$/i';
        $deleted = 0;

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach (scandir($dir) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                if (!preg_match($pattern, $entry)) {
                    continue;
                }
                $full = $dir . '/' . $entry;
                if (is_file($full) && @unlink($full)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}

if(!function_exists('setBlockAssetContext')) {
  function setBlockAssetContext($type = null) {
      static $currentType = null;
      if ($type !== null) {
          $currentType = $type;
      }
      return $currentType;
  }
}

// Get custom block assets
if(!function_exists('block_asset')) {
  function block_asset($file) {
      $type = setBlockAssetContext(); // Retrieve the current type context
      return url("block-asset/$type?asset=$file");
  }
}

if(!function_exists('get_block_file_contents')) {
  function get_block_file_contents($file) {
      $type = setBlockAssetContext(); // Retrieve the current type context
      return file_get_contents(base_path("blocks/$type/$file"));
  }
}

function block_text_translation_check($text) {
  if (empty($text)) {
    return false;
  }
  $translate = __("messages.$text");
  return $translate === "messages.$text" ? true : false;
}

function block_text($text) {
  $translate = __("messages.$text");
  return $translate === "messages.$text" ? $text : $translate;
}

function bt($text) {
  return block_text($text);
}
/**
 * Per-block appearance overrides for rich blocks (Phase 6,
 * THEME-APPEARANCE-PLAN.md). Returns a scoped <style> block ('' when
 * the block has no styling of its own) applying the block's saved
 * choices to selectors INSIDE its wrapper:
 *
 *   'id'      — the wrapper element's id (required)
 *   'button'  — action-button selector(s); receives the block's
 *               custom_css string (generated by the block editor's
 *               visual controls, every declaration !important)
 *   'heading' — colored by type_params.appearance_heading
 *   'text'    — colored by type_params.appearance_color ('' selector
 *               targets the wrapper itself)
 *
 * The #id scoping plus the generator's !important outrank both theme
 * CSS and the page-wide appearance override, so a per-block choice
 * always wins — but only for this one block. Blocks with no overrides
 * emit nothing and keep following the theme.
 */
if (!function_exists('block_appearance_style')) {
  function block_appearance_style($link, array $selectors): string
  {
      $wrapId = (string) ($selectors['id'] ?? '');
      if ($wrapId === '') {
          return '';
      }
      $tp = json_decode($link->type_params ?? '{}', true);
      $tp = is_array($tp) ? $tp : [];
      $rules = [];

      $btnCss = trim((string) ($link->custom_css ?? ''));
      if ($btnCss !== '' && !empty($selectors['button'])) {
          // Neutralize anything that could escape the <style> element.
          $btnCss = str_replace(['<', '>'], '', $btnCss);
          $scoped = implode(', ', array_map(
              fn ($s) => "#{$wrapId} {$s}",
              (array) $selectors['button']
          ));
          $rules[] = "{$scoped} { {$btnCss} }";
      }

      foreach (['heading' => 'appearance_heading', 'text' => 'appearance_color'] as $slot => $key) {
          $hex = trim((string) ($tp[$key] ?? ''));
          if ($hex !== '' && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) && !empty($selectors[$slot] ?? null)) {
              $scoped = implode(', ', array_map(
                  fn ($s) => $s === '' ? "#{$wrapId}" : "#{$wrapId} {$s}",
                  (array) $selectors[$slot]
              ));
              $rules[] = "{$scoped} { color: {$hex} !important; }";
          }
      }

      return $rules ? "<style>\n" . implode("\n", $rules) . "\n</style>" : '';
  }
}
