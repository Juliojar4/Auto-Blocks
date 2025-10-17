<?php
// Server-side rendering for Heading block

$content = $attributes['content'] ?? '';
$fontClass = $attributes['fontClass'] ?? 'h1-forza';
$textColor = $attributes['textColor'] ?? 'black';
$marginBottom = $attributes['marginBottom'] ?? 'mb-4';
$fontFamily = $attributes['fontFamily'] ?? 'forza';
$enableAnimation = $attributes['enableAnimation'] ?? true;
$animationDelay = $attributes['animationDelay'] ?? 100;
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
    'slug' => 'heading'
];

echo view('blocks.heading', $block_data)->render();