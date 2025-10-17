<?php
 
$content = $content ?? ''; // InnerBlocks content
$addBottomBorder = $attributes['addBottomBorder'] ?? false;
$backgroundColor = $attributes['backgroundColor'] ?? 'transparent';
$useGrid = $attributes['useGrid'] ?? false;
$paddingTop = $attributes['paddingTop'] ?? '40';
$paddingBottom = $attributes['paddingBottom'] ?? '40';
$fullWidth = $attributes['fullWidth'] ?? false;

$block_data = [
    'addBottomBorder' => $addBottomBorder,
    'backgroundColor' => $backgroundColor,
    'useGrid' => $useGrid,
    'paddingTop' => $paddingTop,
    'paddingBottom' => $paddingBottom,
    'fullWidth' => $fullWidth,
    'content' => $content,
];

echo view('blocks.container', $block_data)->render();