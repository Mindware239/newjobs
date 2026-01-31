<?php

declare(strict_types=1);

namespace App\Helpers;

class TocHelper
{
    public static function generateWithAnchors(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//h2|//h3');

        $toc = [];
        $ids = [];

        foreach ($nodes as $node) {
            $text = $node->textContent ?? '';
            $base = SlugHelper::slugify($text);
            $id = $base;
            $i = 1;
            while (in_array($id, $ids, true)) {
                $id = $base . '-' . $i;
                $i++;
            }
            $ids[] = $id;
            $node->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'text' => $text, 'level' => strtolower($node->nodeName)];
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $content = '';
        if ($body) {
            foreach ($body->childNodes as $child) {
                $content .= $dom->saveHTML($child);
            }
        }

        return [$content, $toc];
    }
}

