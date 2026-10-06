<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Icons;

/**
 * Demo ürünleri için vektörel (SVG) ürün görselleri üretir.
 */
final class DemoImage
{
    /**
     * @param string $icon    Icons::PATHS içindeki çizim adı
     * @param array  $palette [açık arka plan, ikinci arka plan, vurgu rengi]
     * @param int    $variant Galeri için farklı kompozisyonlar (0-2)
     */
    public static function product(string $icon, array $palette, int $variant = 0): string
    {
        [$bg1, $bg2, $ink] = $palette;
        $paths = Icons::PATHS[$icon] ?? Icons::PATHS['package'];
        $id = substr(md5($icon . implode('', $palette) . $variant), 0, 8);

        $angle = [135, 45, 200][$variant % 3];
        $scale = [20, 17, 23][$variant % 3];
        $offset = (800 - 24 * $scale) / 2;
        $rotate = [-8, 6, 0][$variant % 3];
        $blobs = [
            '<circle cx="640" cy="160" r="120" fill="' . $ink . '" opacity=".07"/><circle cx="140" cy="660" r="80" fill="' . $ink . '" opacity=".06"/>',
            '<rect x="80" y="80" width="220" height="220" rx="60" fill="#fff" opacity=".35" transform="rotate(18 190 190)"/><circle cx="660" cy="640" r="110" fill="' . $ink . '" opacity=".06"/>',
            '<circle cx="400" cy="400" r="330" fill="none" stroke="' . $ink . '" stroke-opacity=".08" stroke-width="2" stroke-dasharray="6 14"/><circle cx="120" cy="140" r="50" fill="#fff" opacity=".4"/>',
        ][$variant % 3];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="800" height="800">
  <defs>
    <linearGradient id="g$id" gradientTransform="rotate($angle .5 .5)">
      <stop offset="0" stop-color="$bg1"/>
      <stop offset="1" stop-color="$bg2"/>
    </linearGradient>
    <radialGradient id="s$id" cx=".5" cy=".5" r=".5">
      <stop offset="0" stop-color="#000" stop-opacity=".18"/>
      <stop offset="1" stop-color="#000" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="800" height="800" fill="url(#g$id)"/>
  $blobs
  <circle cx="400" cy="392" r="250" fill="#fff" opacity=".55"/>
  <ellipse cx="400" cy="676" rx="210" ry="26" fill="url(#s$id)"/>
  <g transform="rotate($rotate 400 400) translate($offset $offset) scale($scale)" fill="none" stroke="$ink" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
    $paths
  </g>
</svg>
SVG;
    }

    public static function slide(string $icon, array $palette): string
    {
        [$bg1, $bg2, $ink] = $palette;
        $paths = Icons::PATHS[$icon] ?? Icons::PATHS['package'];
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">
  <defs>
    <linearGradient id="sg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="$bg1"/>
      <stop offset="1" stop-color="$bg2"/>
    </linearGradient>
  </defs>
  <circle cx="300" cy="300" r="270" fill="url(#sg)"/>
  <circle cx="300" cy="300" r="200" fill="#fff" opacity=".18"/>
  <circle cx="300" cy="300" r="285" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="2" stroke-dasharray="4 12"/>
  <g transform="translate(108 108) scale(16)" fill="none" stroke="$ink" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
    $paths
  </g>
</svg>
SVG;
    }
}
