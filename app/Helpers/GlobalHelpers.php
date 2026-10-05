<?php

if (!function_exists('fix_url')) {
    /**
     * Fixes company logo/banner URLs by handling migration from /companies/ to /uploads/companies/
     * and ensuring proper relative path structure.
     * 
     * @param string|null $url The URL to fix
     * @return string|null The fixed URL
     */
    function fix_url($url)
    {
        if (!$url) return $url;
        if (preg_match('#^https?://#i', $url)) return $url;
        
        // Normalize backslashes to forward slashes
        $url = str_replace('\\', '/', $url);
        
        // Migration fix: /companies/ or /company/ -> /uploads/companies/ or /uploads/company/
        // If it starts with /companies/ or /company/ but not /uploads/
        if ((strpos($url, '/companies/') === 0 || strpos($url, 'companies/') === 0 || 
             strpos($url, '/company/') === 0 || strpos($url, 'company/') === 0) && 
            strpos($url, 'uploads/') === false) {
            $url = '/uploads/' . ltrim($url, '/');
        }
        
        return '/' . ltrim($url, '/');
    }
}
