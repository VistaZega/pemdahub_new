<?php

if (!function_exists('balanceHtmlTags')) {
    /**
     * Autocorrect and balance unclosed HTML tags.
     *
     * @param string|null $html
     * @return string|null
     */
    function balanceHtmlTags($html) {
        if (empty(trim($html ?? ''))) return $html;
        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $balanced = $dom->saveHTML();
            libxml_clear_errors();
            $balanced = str_replace(['<?xml encoding="utf-8" ?>', '<html>', '</html>', '<body>', '</body>'], '', $balanced);
            return trim($balanced);
        } catch (\Throwable $e) {
            return $html;
        }
    }
}
