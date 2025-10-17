<?php
// Server-side rendering for Paragraph block

$content = $attributes['content'] ?? '';
$fontClass = $attributes['fontClass'] ?? 'paragraph-normal';
$textColor = $attributes['textColor'] ?? 'black';
$marginBottom = $attributes['marginBottom'] ?? 'mb-4';
$fontFamily = $attributes['fontFamily'] ?? 'forza';
$enableAnimation = $attributes['enableAnimation'] ?? true;
$animationDelay = $attributes['animationDelay'] ?? 0;
$animationType = $attributes['animationType'] ?? 'fade-up';

$block_data = [
    'content' => $content,
    'fontClass' => $fontClass,
    'textColor' => $textColor,
    'marginBottom' => $marginBottom,
    'fontFamily' => $fontFamily,
    'enableAnimation' => $enableAnimation,
    'animationDelay' => $animationDelay,
    'animationType' => $animationType,
    'slug' => 'paragraph'
];

echo view('blocks.paragraph', $block_data)->render();