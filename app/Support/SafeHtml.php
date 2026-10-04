<?php

namespace App\Support;

class SafeHtml
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><span><div>';

    public static function clean(?string $html): string
    {
        $html = (string) $html;
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = strip_tags($html, self::ALLOWED_TAGS);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/\s+(href|src)\s*=\s*([\'"])\s*(javascript:|data:)[^\'"]*\2/i', '', $html) ?? '';

        return $html;
    }
}
