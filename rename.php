#!/usr/bin/env php
<?php

/**
 * Modularity Boilerplate Renamer
 * 
 * Usage: php rename.php <NewModuleName>
 * Example: php rename.php LinkCards
 * 
 * This will rename:
 * - Boilerplate → LinkCards (PascalCase)
 * - boilerplate → link-cards (slug/kebab-case)  
 * - boilerplate → linkcards (lowercase)
 * - BOILERPLATE → LINKCARDS (constants)
 */

if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

if (!isset($argv[1]) || empty($argv[1])) {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║         Modularity Boilerplate Renamer                       ║\n";
    echo "╠══════════════════════════════════════════════════════════════╣\n";
    echo "║  Usage: php rename.php <NewModuleName>                       ║\n";
    echo "║                                                              ║\n";
    echo "║  Example: php rename.php LinkCards                           ║\n";
    echo "║                                                              ║\n";
    echo "║  This will transform:                                        ║\n";
    echo "║    • Boilerplate  → LinkCards     (classes)                  ║\n";
    echo "║    • boilerplate  → link-cards    (slugs)                    ║\n";
    echo "║    • BOILERPLATE  → LINKCARDS     (constants)                ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    exit(1);
}

$newName = $argv[1];

// Validate input - must be PascalCase
if (!preg_match('/^[A-Z][a-zA-Z0-9]+$/', $newName)) {
    echo "Error: Module name must be PascalCase (e.g., LinkCards, MyModule)\n";
    exit(1);
}

// Generate name variants
$variants = [
    'pascal'    => $newName,                                    // LinkCards
    'kebab'     => strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $newName)), // link-cards
    'lower'     => strtolower($newName),                        // linkcards
    'upper'     => strtoupper($newName),                        // LINKCARDS
    'snake'     => strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $newName)), // link_cards
];

$oldVariants = [
    'pascal'    => 'Boilerplate',
    'kebab'     => 'boilerplate',
    'lower'     => 'boilerplate', 
    'upper'     => 'BOILERPLATE',
    'snake'     => 'boilerplate',
];

echo "\n";
echo "Renaming module from 'Boilerplate' to '{$newName}'...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  PascalCase:  Boilerplate  → {$variants['pascal']}\n";
echo "  kebab-case:  boilerplate  → {$variants['kebab']}\n";
echo "  lowercase:   boilerplate  → {$variants['lower']}\n";
echo "  UPPERCASE:   BOILERPLATE  → {$variants['upper']}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$basePath = __DIR__;

// Files to process (content replacement)
$filesToProcess = [
    'modularity-boilerplate.php',
    'source/php/App.php',
    'source/php/Module/Boilerplate.php',
    'source/php/Module/views/boilerplate.blade.php',
    'source/php/AcfFields/json/boilerplate-module.json',
    'source/php/Helper/Utils.php',
    'source/php/Helper/CacheBust.php',
    'source/sass/modularity-boilerplate.scss',
    'composer.json',
    'package.json',
    'vite.config.mjs',
    'README.md',
];

// Process file contents
echo "Processing file contents...\n";
foreach ($filesToProcess as $file) {
    $filePath = $basePath . '/' . $file;
    if (!file_exists($filePath)) {
        echo "  ⚠ Skipping (not found): {$file}\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Replace in specific order to avoid partial replacements
    $replacements = [
        // Constants (UPPERCASE)
        'MODULARITYBOILERPLATE' => 'MODULARITY' . $variants['upper'],
        
        // Namespaces and class names (PascalCase)
        'ModularityBoilerplate' => 'Modularity' . $variants['pascal'],
        'Boilerplate' => $variants['pascal'],
        
        // Slugs, text domains, file references (kebab-case)
        'modularity-boilerplate' => 'modularity-' . $variants['kebab'],
        'mod-boilerplate' => 'mod-' . $variants['kebab'],
        
        // ACF/other lowercase references
        'boilerplate-module' => $variants['kebab'] . '-module',
        'boilerplate' => $variants['kebab'],
        
        // Composer package name (vendor stays the same)
        'considbrs-webdev/modularity-boilerplate' => 'considbrs-webdev/modularity-' . $variants['kebab'],
    ];
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "  ✓ Updated: {$file}\n";
    } else {
        echo "  - No changes: {$file}\n";
    }
}

// Rename files
echo "\nRenaming files...\n";

$filesToRename = [
    'source/php/Module/Boilerplate.php' => "source/php/Module/{$variants['pascal']}.php",
    'source/php/Module/views/boilerplate.blade.php' => "source/php/Module/views/{$variants['kebab']}.blade.php",
    'source/php/AcfFields/json/boilerplate-module.json' => "source/php/AcfFields/json/{$variants['kebab']}-module.json",
    'source/sass/modularity-boilerplate.scss' => "source/sass/modularity-{$variants['kebab']}.scss",
    'modularity-boilerplate.php' => "modularity-{$variants['kebab']}.php",
];

foreach ($filesToRename as $oldFile => $newFile) {
    $oldPath = $basePath . '/' . $oldFile;
    $newPath = $basePath . '/' . $newFile;
    
    if (!file_exists($oldPath)) {
        echo "  ⚠ Skipping (not found): {$oldFile}\n";
        continue;
    }
    
    if ($oldFile === $newFile) {
        echo "  - Same name: {$oldFile}\n";
        continue;
    }
    
    if (rename($oldPath, $newPath)) {
        echo "  ✓ Renamed: {$oldFile} → {$newFile}\n";
    } else {
        echo "  ✗ Failed to rename: {$oldFile}\n";
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  ✓ Done! Your module is now named: {$newName}" . str_repeat(' ', max(0, 23 - strlen($newName))) . "║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  Next steps:                                                 ║\n";
echo "║  1. Run: composer dump-autoload                              ║\n";
echo "║  2. Delete this script: del rename.php                       ║\n";
echo "║  3. Update composer.lock if needed                           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

