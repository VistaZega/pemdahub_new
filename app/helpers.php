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

if (!function_exists('formatLmsContent')) {
    /**
     * Format LMS material content preserving HTML tags & newlines properly.
     *
     * @param string|null $content
     * @return string
     */
    function formatLmsContent($content) {
        if (empty(trim($content ?? ''))) return '';

        // Check if content already contains HTML block structure tags
        $hasBlockTags = (bool) preg_match('/<(p|div|br|h[1-6]|ul|ol|li|table|blockquote)\b/i', $content);

        if (!$hasBlockTags) {
            // It's either plain text or plain text + inline tags like <img> without block elements
            // Convert newlines (\n) to <br> so paragraphs/linebreaks are preserved
            $content = nl2br($content);
        }

        return balanceHtmlTags($content);
    }
}

