<?php
// Avatar gradient palette — keep keys stable; admin picks from these.
function testimonial_palette(): array {
    return [
        'blue'   => ['#0f4c81', '#1a6bb5'],
        'green'  => ['#00a86b', '#00c97f'],
        'purple' => ['#7c3aed', '#a855f7'],
        'orange' => ['#f97316', '#fb923c'],
        'red'    => ['#dc2626', '#ef4444'],
        'teal'   => ['#0d9488', '#14b8a6'],
        'slate'  => ['#475569', '#64748b'],
    ];
}

function testimonial_gradient(string $key): string {
    $p = testimonial_palette();
    [$a, $b] = $p[$key] ?? $p['blue'];
    return "linear-gradient(135deg,{$a},{$b})";
}

function testimonial_initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach ($parts as $p) {
        if ($p === '') continue;
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
        if (mb_strlen($out) >= 2) break;
    }
    return $out !== '' ? $out : '?';
}
