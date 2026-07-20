<?php
// ============================================================
//  NexVest — Shared Components
//  views/components/helpers.php
// ============================================================

// ── SVG Icon helper ───────────────────────────────────────────
function svgIcon(string $name, int $size = 16, string $color = 'currentColor'): string {
    $icons = [
        'overview'  => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'building'  => '<path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>',
        'chart'     => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
        'wallet'    => '<path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><circle cx="17" cy="13" r="1" fill="' . $color . '" stroke="none"/>',
        'bell'      => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'headset'   => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
        'user'      => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'gift'      => '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        'doc'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'calc'      => '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/>',
        'arrowLR'   => '<path d="M7 16V4m0 0L3 8m4-4 4 4"/><path d="M17 8v12m0 0 4-4m-4 4-4-4"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'ticket'    => '<path d="M21 5H3a2 2 0 0 0-2 2v3a2 2 0 0 1 0 4v3a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2v-3a2 2 0 0 1 0-4V7a2 2 0 0 0-2-2z"/>',
        'log'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'file'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
        'menu'      => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'check'     => '<polyline points="20 6 9 17 4 12"/>',
        'x'         => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'trendUp'   => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'arrowUp'   => '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>',
        'arrowDown' => '<line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/>',
        'pin'       => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'download'  => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'send'      => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
        'copy'      => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'eye'       => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'edit'      => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'trash'     => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
        'info'      => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'filter'    => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
        'login'     => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>',
        'bank'      => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'clock'     => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'star'      => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'plus'      => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'refresh'   => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'share'     => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
        'upload'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
    ];
    $paths = $icons[$name] ?? '';
    return "<svg class=\"icon\" width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"1.6\" stroke-linecap=\"round\" stroke-linejoin=\"round\">{$paths}</svg>";
}

// ── Badge helper ──────────────────────────────────────────────
function badge(string $status, string $label = ''): string {
    $label = $label ?: ucfirst(str_replace('_', ' ', $status));
    $class = 'badge badge-' . htmlspecialchars($status, ENT_QUOTES);
    return "<span class=\"{$class}\">" . htmlspecialchars($label, ENT_QUOTES) . "</span>";
}

// ── Alert helper ──────────────────────────────────────────────
function alertBox(string $type, string $message, string $icon = 'info'): string {
    return "<div class=\"alert alert-{$type}\">" . svgIcon($icon, 14) . "<span>" . htmlspecialchars($message, ENT_QUOTES) . "</span></div>";
}

// ── Flash messages ────────────────────────────────────────────
function renderFlash(): void {
    foreach (['ok' => 'success', 'err' => 'error', 'warn' => 'warning', 'info' => 'info'] as $type => $key) {
        $msg = get_flash($key) ?? get_flash($type);
        if ($msg) {
            $iconMap = ['ok' => 'check', 'err' => 'x', 'warn' => 'info', 'info' => 'info'];
            echo '<div class="alert alert-' . $type . '" data-dismiss="5000">' . svgIcon($iconMap[$type] ?? 'info', 14) . '<span>' . htmlspecialchars($msg, ENT_QUOTES) . '</span></div>';
        }
    }
}

// ── Pagination ────────────────────────────────────────────────
function renderPagination(int $page, int $pages, string $baseUrl): void {
    if ($pages <= 1) return;
    echo '<div class="pagination">';
    $prev = $page > 1 ? "<a href=\"{$baseUrl}page=" . ($page-1) . "\" class=\"pg-item\">‹</a>" : '<span class="pg-item disabled">‹</span>';
    echo $prev;
    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    if ($start > 1) echo "<a href=\"{$baseUrl}page=1\" class=\"pg-item\">1</a>" . ($start > 2 ? '<span class="pg-item disabled">…</span>' : '');
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $page ? ' active' : '';
        echo "<a href=\"{$baseUrl}page={$i}\" class=\"pg-item{$active}\">{$i}</a>";
    }
    if ($end < $pages) echo ($end < $pages - 1 ? '<span class="pg-item disabled">…</span>' : '') . "<a href=\"{$baseUrl}page={$pages}\" class=\"pg-item\">{$pages}</a>";
    $next = $page < $pages ? "<a href=\"{$baseUrl}page=" . ($page+1) . "\" class=\"pg-item\">›</a>" : '<span class="pg-item disabled">›</span>';
    echo $next . '</div>';
}

// ── NexVest v2 icon set (used by main/auth layouts + investor views) ──
function nv_icon(string $name): string {
    $icons = [
        'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'building'  => '<path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
        'calc'      => '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/>',
        'wallet'    => '<path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><circle cx="17" cy="13" r="1" fill="currentColor" stroke="none"/>',
        'swap'      => '<path d="M7 16V4m0 0L3 8m4-4 4 4"/><path d="M17 8v12m0 0 4-4m-4 4-4-4"/>',
        'doc'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'user'      => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'gift'      => '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        'headset'   => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
        'menu'      => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'bell'      => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'eye'       => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'check'     => '<polyline points="20 6 9 17 4 12"/>',
        'deposit'   => '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>',
        'invest'    => '<path d="M3 21h18M5 21V7l7-4 7 4v14"/>',
        'kyc'       => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'return'    => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'withdraw'  => '<line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/>',
        'plus'      => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'arrow-up'  => '<polyline points="18 15 12 9 6 15"/>',
        'chart'     => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'inbox'     => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'pin'       => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
    ];
    return $icons[$name] ?? $icons['bell'];
}
function nv_notif_icon(string $type): array {
    return match ($type) {
        'deposit'    => ['deposit', 'var(--em-600)', 'var(--em-50)'],
        'investment' => ['invest', 'var(--blue-600)', 'var(--blue-50)'],
        'kyc'        => ['kyc', 'var(--blue-600)', 'var(--blue-50)'],
        'return'     => ['return', 'var(--em-600)', 'var(--em-50)'],
        'withdrawal' => ['withdraw', 'var(--mist-600)', 'var(--mist-100)'],
        'referral'   => ['gift', 'var(--em-600)', 'var(--em-50)'],
        default      => ['bell', 'var(--mist-600)', 'var(--mist-100)'],
    };
}
function nv_tx_icon(string $type): array {
    return match ($type) {
        'deposit'             => ['deposit', 'var(--em-600)', 'var(--em-50)'],
        'investment'          => ['invest', 'var(--blue-600)', 'var(--blue-50)'],
        'return'              => ['return', 'var(--em-600)', 'var(--em-50)'],
        'referral_commission' => ['gift', 'var(--em-600)', 'var(--em-50)'],
        'withdrawal'          => ['withdraw', 'var(--mist-600)', 'var(--mist-100)'],
        default               => ['swap', 'var(--mist-600)', 'var(--mist-100)'],
    };
}

// ── Investment card hero art (used when no listing photo is set) ──
function nv_hero_art(string $type, int $seed = 0): string {
    if ($type === 'real_estate') {
        $variants = [
            '<rect x="40" y="120" width="34" height="110" fill="#1E3A5F"/><rect x="80" y="90" width="28" height="140" fill="#26416B"/><rect x="114" y="60" width="40" height="170" fill="#2E4D7A"/><rect x="160" y="100" width="30" height="130" fill="#1E3A5F"/><rect x="196" y="40" width="46" height="190" fill="#345580"/><rect x="248" y="75" width="32" height="155" fill="#26416B"/><rect x="286" y="110" width="28" height="120" fill="#1E3A5F"/><rect x="320" y="55" width="40" height="175" fill="#2E4D7A"/><g opacity="0.55" fill="#5B8AC2"><rect x="120" y="75" width="6" height="6"/><rect x="132" y="75" width="6" height="6"/><rect x="120" y="92" width="6" height="6"/><rect x="132" y="92" width="6" height="6"/><rect x="202" y="55" width="7" height="7"/><rect x="218" y="55" width="7" height="7"/><rect x="202" y="75" width="7" height="7"/><rect x="218" y="75" width="7" height="7"/><rect x="202" y="95" width="7" height="7"/><rect x="218" y="95" width="7" height="7"/><rect x="328" y="70" width="6" height="6"/><rect x="343" y="70" width="6" height="6"/><rect x="328" y="88" width="6" height="6"/><rect x="343" y="88" width="6" height="6"/></g>',
            '<rect x="20" y="140" width="60" height="90" fill="#26416B"/><rect x="90" y="100" width="50" height="130" fill="#2E4D7A"/><rect x="150" y="160" width="200" height="70" fill="#1E3A5F"/><rect x="170" y="175" width="24" height="22" fill="#5B8AC2" opacity=".5"/><rect x="205" y="175" width="24" height="22" fill="#5B8AC2" opacity=".5"/><rect x="240" y="175" width="24" height="22" fill="#5B8AC2" opacity=".5"/><rect x="275" y="175" width="24" height="22" fill="#5B8AC2" opacity=".5"/><rect x="310" y="175" width="24" height="22" fill="#5B8AC2" opacity=".5"/>',
        ];
        return '<svg class="hero-art" viewBox="0 0 400 230" preserveAspectRatio="xMidYMax slice" fill="none">' . $variants[$seed % count($variants)] . '</svg>';
    }
    $variants = [
        ['M0 70 L40 62 L80 68 L120 40 L160 48 L200 22 L240 30 L280 8 L320 14 L360 6 L400 12', 'g1'],
        ['M0 140 L40 150 L80 130 L120 145 L160 100 L200 120 L240 85 L280 95 L320 60 L360 75 L400 45', 'g2'],
    ];
    [$path, $gid] = $variants[$seed % count($variants)];
    return '<svg class="hero-art" viewBox="0 0 400 230" preserveAspectRatio="none" fill="none">'
        . '<path d="' . $path . '" stroke="#34D399" stroke-width="2.5" fill="none" stroke-linecap="round"/>'
        . '<path d="' . $path . ' L400 230 L0 230 Z" fill="url(#' . $gid . ')"/>'
        . '<defs><linearGradient id="' . $gid . '" x1="0" y1="0" x2="0" y2="230"><stop offset="0%" stop-color="#10B981" stop-opacity="0.35"/><stop offset="100%" stop-color="#10B981" stop-opacity="0"/></linearGradient></defs>'
        . '</svg>';
}

// ── Progress bar ──────────────────────────────────────────────
function progressBar(float $raised, float $target): void {
    if ($target <= 0) return;
    $pct   = min(100, round(($raised / $target) * 100));
    $full  = $pct >= 100 ? ' full' : '';
    $fmtR  = fmt_currency($raised);
    $fmtT  = fmt_currency($target);
    echo "<div class=\"prog-wrap\">
            <div class=\"prog-top\"><span class=\"prog-pct\">{$pct}% Funded</span><span class=\"prog-lbl\">{$fmtR} of {$fmtT}</span></div>
            <div class=\"prog-bar\"><div class=\"prog-fill{$full}\" style=\"width:{$pct}%\"></div></div>
          </div>";
}
