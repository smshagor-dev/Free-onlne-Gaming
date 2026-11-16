<?php

namespace App\Services;

class GameEmbedPolicy
{
    /**
     * Resolve a safe, origin-specific embed configuration.
     *
     * No browser can access or modify another origin's iframe DOM. The
     * third-party ad-free path is a provider-approved URL contract only.
     *
     * @return array{
     *     url:string,
     *     origin:string,
     *     mode:string,
     *     cleanup_owned_ads:bool,
     *     provider_ad_free:bool,
     *     notice:string
     * }
     */
    public function resolve(string $inputUrl): array
    {
        $url = trim($inputUrl);
        $parts = parse_url($url);
        $scheme = is_array($parts) ? strtolower((string) ($parts['scheme'] ?? '')) : '';
        $host = is_array($parts) ? strtolower(rtrim((string) ($parts['host'] ?? ''), '.')) : '';

        // No scheme-relative, javascript:, data:, ftp:, credentials or bad URLs.
        if (!filter_var($url, FILTER_VALIDATE_URL)
            || !in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || isset($parts['user']) || isset($parts['pass'])) {
            return $this->blocked();
        }

        $site = parse_url((string) config('app.url'));
        $siteScheme = strtolower((string) ($site['scheme'] ?? ''));
        $siteHost = strtolower(rtrim((string) ($site['host'] ?? ''), '.'));
        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        $sitePort = (int) ($site['port'] ?? ($siteScheme === 'https' ? 443 : 80));
        $sameOrigin = $scheme === $siteScheme && $host === $siteHost && $port === $sitePort;

        if ($sameOrigin) {
            $settings = SiteCms::section('embeds');

            return [
                'url' => $url,
                'origin' => $this->origin($scheme, $host, $port),
                'mode' => 'same_origin',
                'cleanup_owned_ads' => (bool) $settings['owned_ad_cleanup_enabled'],
                'provider_ad_free' => false,
                'notice' => 'First-party game. Only explicitly marked site-owned ad elements may be hidden.',
            ];
        }

        // Remote insecure HTTP game content is never embedded.
        if ($scheme !== 'https') {
            return $this->blocked();
        }

        $settings = SiteCms::section('embeds');
        $providers = config('game_embeds.approved_ad_free_providers', []);
        $contract = is_array($providers) ? ($providers[$host] ?? null) : null;
        $parameter = is_array($contract) ? ($contract['parameter'] ?? null) : null;
        $value = is_array($contract) ? ($contract['value'] ?? null) : null;

        // Config is controlled by deployment, not editable via URL or request.
        // Explicit verification, exact hostname and safe parameter syntax are
        // required in addition to enabling the setting from Admin CMS.
        $verified = (bool) ($settings['prefer_provider_ad_free'] ?? false)
            && is_array($contract)
            && ($contract['verified'] ?? false) === true
            && is_string($parameter)
            && preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,39}$/D', $parameter)
            && is_string($value)
            && preg_match('/^[a-zA-Z0-9_-]{1,40}$/D', $value);

        if ($verified) {
            $query = [];
            parse_str((string) ($parts['query'] ?? ''), $query);
            $query[$parameter] = $value;
            $base = $scheme.'://'.$parts['host']
                .(isset($parts['port']) ? ':'.$parts['port'] : '')
                .($parts['path'] ?? '');
            $url = $base.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986)
                .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        }

        return [
            'url' => $url,
            'origin' => $this->origin($scheme, $host, $port),
            'mode' => 'third_party',
            'cleanup_owned_ads' => false,
            'provider_ad_free' => $verified,
            'notice' => $verified
                ? 'Provider-approved ad-free embed configuration is enabled.'
                : 'Third-party ads remain under the provider’s control.',
        ];
    }

    private function origin(string $scheme, string $host, int $port): string
    {
        $defaultPort = $scheme === 'https' ? 443 : 80;

        return $scheme.'://'.$host.($port === $defaultPort ? '' : ':'.$port);
    }

    private function blocked(): array
    {
        return [
            'url' => '',
            'origin' => '',
            'mode' => 'blocked',
            'cleanup_owned_ads' => false,
            'provider_ad_free' => false,
            'notice' => 'This game embed URL is not supported.',
        ];
    }
}
