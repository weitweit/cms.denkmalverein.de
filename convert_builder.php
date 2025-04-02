<?php

function convertBuilderToJson($dir)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'txt') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        // Split content into sections
        $sections = preg_split('/\n----\n/', $content);

        foreach ($sections as &$section) {
            if (preg_match('/^Builder:\s*$/mi', $section)) {
                // Found Builder section
                $lines = explode("\n", $section);
                $builderContent = [];
                $currentItem = null;
                $inItem = false;

                foreach ($lines as $line) {
                    if (preg_match('/^Builder:\s*$/', $line)) {
                        continue;
                    }

                    if (preg_match('/^-\s*$/', trim($line))) {
                        if ($currentItem) {
                            $builderContent[] = $currentItem;
                        }
                        $currentItem = [];
                        $inItem = true;
                        continue;
                    }

                    if ($inItem && preg_match('/^\s+(\w+):\s*(.*)$/', $line, $matches)) {
                        $key = $matches[1];
                        $value = trim($matches[2]);

                        // Handle multiline text
                        if ($value === '|') {
                            $value = '';
                            continue;
                        }

                        // Change _fieldset to type
                        if ($key === '_fieldset') {
                            $key = 'type';
                        }

                        $currentItem[$key] = $value;
                    } elseif ($inItem && !empty(trim($line))) {
                        // Append to previous value for multiline text
                        end($currentItem);
                        $lastKey = key($currentItem);
                        $currentItem[$lastKey] .= "\n" . trim($line);
                    }
                }

                if ($currentItem) {
                    $builderContent[] = $currentItem;
                }

                // Convert to JSON
                $section = "Builder: " . json_encode($builderContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        // Join sections back together
        $newContent = implode("\n----\n", $sections);

        // Backup original file
        copy($file->getPathname(), $file->getPathname() . '.bak');

        // Save new content
        file_put_contents($file->getPathname(), $newContent);

        echo "Processed: " . $file->getPathname() . "\n";
    }
}

// Path to your content directory
$contentDir = __DIR__ . '/content';

// Create backup of content directory
$backupDir = __DIR__ . '/content_backup_' . date('Y-m-d_H-i-s');
shell_exec("cp -r '$contentDir' '$backupDir'");
echo "Created backup at: $backupDir\n";

// Run the conversion
convertBuilderToJson($contentDir);
echo "Conversion completed!\n";
