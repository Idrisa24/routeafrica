<?php

namespace Saidtech\Routereseller\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DomainCheckerService
{
    protected $routeafricaApiKey;
    protected $routeafricaUsername;
    protected $routeafricaApiUrl;

    public function __construct()
    {
        $this->routeafricaApiKey = config('routereseller.api_key');
        $this->routeafricaUsername = config('routereseller.api_key');
        $this->routeafricaApiUrl = config('routereseller.sandbox', true);
    }

    /**
     * Check domain availability using routeafrica API
     */
    public function checkDomainAvailability(string $domain): array
    {
        return Cache::remember("domain_check_{$domain}", 300, function () use ($domain) {
            try {
                $response = $this->makeRouteAfricaRequest('domains.check', [
                    'DomainList' => $domain
                ]);

                if ($response && isset($response['DomainCheckResult'])) {
                    $result = $response['DomainCheckResult'];
                    return [
                        'domain' => $domain,
                        'available' => $result['Available'] === 'true',
                        'price' => $result['Price'] ?? 0,
                        'currency' => $result['Currency'] ?? 'USD',
                        'error' => null
                    ];
                }

                return [
                    'domain' => $domain,
                    'available' => false,
                    'price' => 0,
                    'currency' => 'USD',
                    'error' => 'Invalid response from domain provider'
                ];
            } catch (\Exception $e) {
                Log::error('Domain availability check failed: ' . $e->getMessage());
                return [
                    'domain' => $domain,
                    'available' => false,
                    'price' => 0,
                    'currency' => 'USD',
                    'error' => 'Unable to check domain availability'
                ];
            }
        });
    }

    /**
     * Check DNS records for a domain
     */
    public function checkDNSRecords(string $domain): array
    {
        return Cache::remember("dns_check_{$domain}", 600, function () use ($domain) {
            try {
                $records = [];

                // Check A record
                $aRecord = dns_get_record($domain, DNS_A);
                if ($aRecord) {
                    $records['A'] = $aRecord[0]['ip'] ?? null;
                }

                // Check CNAME record
                $cnameRecord = dns_get_record($domain, DNS_CNAME);
                if ($cnameRecord) {
                    $records['CNAME'] = $cnameRecord[0]['target'] ?? null;
                }

                // Check MX records
                $mxRecords = dns_get_record($domain, DNS_MX);
                if ($mxRecords) {
                    $records['MX'] = array_map(function ($mx) {
                        return [
                            'priority' => $mx['pri'],
                            'target' => $mx['target']
                        ];
                    }, $mxRecords);
                }

                // Check NS records
                $nsRecords = dns_get_record($domain, DNS_NS);
                if ($nsRecords) {
                    $records['NS'] = array_map(function ($ns) {
                        return $ns['target'];
                    }, $nsRecords);
                }

                // Check TXT records
                $txtRecords = dns_get_record($domain, DNS_TXT);
                if ($txtRecords) {
                    $records['TXT'] = array_map(function ($txt) {
                        return $txt['txt'];
                    }, $txtRecords);
                }

                return [
                    'domain' => $domain,
                    'records' => $records,
                    'has_records' => !empty($records),
                    'error' => null
                ];
            } catch (\Exception $e) {
                Log::error('DNS check failed: ' . $e->getMessage());
                return [
                    'domain' => $domain,
                    'records' => [],
                    'has_records' => false,
                    'error' => 'Unable to check DNS records'
                ];
            }
        });
    }

    /**
     * Get domain suggestions based on a keyword
     */
    public function getDomainSuggestions(string $keyword): array
    {
        return Cache::remember("domain_suggestions_{$keyword}", 3600, function () use ($keyword) {
            try {
                $extensions = ['.com', '.net', '.org', '.info', '.biz', '.co', '.io', '.me'];
                $suggestions = [];

                foreach ($extensions as $extension) {
                    $domain = $keyword . $extension;
                    $availability = $this->checkDomainAvailability($domain);

                    if ($availability['available']) {
                        $suggestions[] = [
                            'domain' => $domain,
                            'extension' => $extension,
                            'price' => $availability['price'],
                            'currency' => $availability['currency']
                        ];
                    }
                }

                return $suggestions;
            } catch (\Exception $e) {
                Log::error('Domain suggestions failed: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Check if domain is pointing to our nameservers
     */
    public function checkNameserverStatus(string $domain): array
    {
        try {
            $dnsInfo = $this->checkDNSRecords($domain);

            if (!isset($dnsInfo['records']['NS'])) {
                return [
                    'domain' => $domain,
                    'pointing_to_us' => false,
                    'nameservers' => [],
                    'error' => 'No nameserver records found'
                ];
            }

            $ourNameservers = [
                'ns1.supperhost.com',
                'ns2.supperhost.com',
                'ns1.yourdomain.com',
                'ns2.yourdomain.com'
            ];

            $domainNameservers = $dnsInfo['records']['NS'];
            $pointingToUs = false;

            foreach ($domainNameservers as $ns) {
                if (in_array(strtolower($ns), array_map('strtolower', $ourNameservers))) {
                    $pointingToUs = true;
                    break;
                }
            }

            return [
                'domain' => $domain,
                'pointing_to_us' => $pointingToUs,
                'nameservers' => $domainNameservers,
                'our_nameservers' => $ourNameservers,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Nameserver check failed: ' . $e->getMessage());
            return [
                'domain' => $domain,
                'pointing_to_us' => false,
                'nameservers' => [],
                'error' => 'Unable to check nameserver status'
            ];
        }
    }

    /**
     * Make routeafrica API request
     */
    protected function makeRouteAfricaRequest(string $command, array $params = []): array
    {
        $baseUrl = $this->routeafricaApiUrl
            ? 'https://api.sandbox.routeafrica.com/xml.response'
            : 'https://routeafrica.net/modules/addons/DomainsReseller/api/index.php';

        $defaultParams = [
            'ApiUser' => $this->routeafricaApiUser,
            'ApiKey' => $this->routeafricaApiKey,
            'UserName' => $this->routeafricaUsername,
            'ClientIp' => $this->routeafricaClientIp,
            'Command' => $command
        ];

        $allParams = array_merge($defaultParams, $params);

        $response = Http::timeout(30)
            ->get($baseUrl, $allParams);

        if ($response->successful()) {
            return $this->parserouteafricaResponse($response->body());
        }

        throw new \Exception('routeafrica API request failed: ' . $response->body());
    }

    /**
     * Parse routeafrica XML response
     */
    protected function parserouteafricaResponse(string $xml): array
    {
        $xml = simplexml_load_string($xml);

        if ($xml === false) {
            throw new \Exception('Invalid XML response from routeafrica');
        }

        // Convert XML to array
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    /**
     * Validate domain format
     */
    public function validateDomain(string $domain): bool
    {
        // Remove protocol if present
        $domain = preg_replace('/^https?:\/\//', '', $domain);

        // Remove www if present
        $domain = preg_replace('/^www\./', '', $domain);

        // Basic domain validation
        return (bool) preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/', $domain);
    }

    /**
     * Extract domain from URL
     */
    public function extractDomain(string $url): string
    {
        // Remove protocol
        $domain = preg_replace('/^https?:\/\//', '', $url);

        // Remove www
        $domain = preg_replace('/^www\./', '', $domain);

        // Remove path
        $domain = parse_url($domain, PHP_URL_HOST) ?: $domain;

        return $domain;
    }
}
