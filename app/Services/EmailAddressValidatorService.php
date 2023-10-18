<?php

namespace App\Services;

class EmailAddressValidatorService
{
    public function checkEmail(&$username): bool
    {
        $username = strtolower($username);
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        // QQ Mail
        if (preg_match('/^\d{5,11}@qq\.com$/', $username)) {
            return true;
        }
        // 163 Mail
        if (preg_match('/^[a-z0-9_-]{1,32}@163\.com$/', $username)) {
            return true;
        }
        // 126 Mail
        if (preg_match('/^[a-z0-9_-]{1,32}@126\.com$/', $username)) {
            return true;
        }
        // Gmail
        if (preg_match('/^[a-z0-9_-]{1,32}@gmail\.com$/', $username)) {
            return true;
        }
        // Hotmail
        if (preg_match('/^[a-z0-9_-]{1,32}@hotmail\.com$/', $username)) {
            return true;
        }
        // Outlook
        if (preg_match('/^[a-z0-9_-]{1,32}@outlook\.com$/', $username)) {
            return true;
        }
        // Domain Mail end with com/org
        if (preg_match('/^[a-z0-9_-]{1,32}@([a-z0-9_-]{1,32}\.(com|org))$/', $username, $matches)) {
            if ($this->checkDomain($matches[1])) {
                return true;
            }
        }
        return false;
    }

    private function checkDomain(string $domain): bool
    {
        $condition1 = $this->checkA($domain);
        $condition2 = $this->checkMX($domain);
        $condition3 = $this->checkDMARC($domain);
        $condition4 = $this->checkSPF($domain);
        return $condition1 && $condition2 && $condition3 && $condition4;
    }

    private function checkA(string $domain): bool
    {
        $ips = dns_get_record($domain, DNS_A);
        if (empty($ips)) {
            return false;
        }
        foreach ($ips as $ip) {
            if (filter_var($ip['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return true;
            }
        }
        return false;
    }

    private function checkMX(string $domain): bool
    {
        $mxs = dns_get_record($domain, DNS_MX);
        if (empty($mxs)) {
            return false;
        }
        foreach ($mxs as $mx) {
            if (filter_var($mx['target'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return true;
            } else {
                if ($this->checkA($mx['target'])) {
                    return true;
                }
            }
        }
        return false;
    }

    private function checkDMARC(string $domain): bool
    {
        $txts = dns_get_record('_dmarc.' . $domain, DNS_TXT);
        if (empty($txts)) {
            return false;
        }
        foreach ($txts as $txt) {
            if (str_starts_with($txt['txt'], 'v=DMARC1;')) {
                return true;
            }
        }
        return false;
    }

    private function checkSPF(string $domain): bool
    {
        $txts = dns_get_record($domain, DNS_TXT);
        if (empty($txts)) {
            return false;
        }
        foreach ($txts as $txt) {
            if (str_starts_with($txt['txt'], 'v=spf1')) {
                return true;
            }
        }
        return false;
    }
}
