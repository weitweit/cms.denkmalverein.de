<?php

Kirby::plugin('weitweit/migrate', [
    'routes' => [
        [
            'pattern' => 'migrate',
            'action' => function () {
                migrate();

                return 'Migration completed';
            },
        ],
    ],
]);

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

                /*foreach ($lines as $line) {
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
                $section = "Builder: " . json_encode($builderContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);*/
            }
        }

        // Join sections back together
        $newContent = implode("\n----\n", $sections);

        ray($newContent)->die();

        // Backup original file
        //copy($file->getPathname(), $file->getPathname() . '.bak');

        // Save new content
        //file_put_contents($file->getPathname(), $newContent);

        echo "Processed: " . $file->getPathname() . "\n";
    }
}


function migrate()
{
    // Path to your content directory
    $kirby = kirby();
    $contentDir = $kirby->roots()->content;
    $kirby->impersonate('kirby');

    if (!file_exists($contentDir)) {
        return false;
    }

    // Create backup of content directory
    $backupDir = $contentDir . '/../content_backup_' . date('Y-m-d_H-i-s');

    if (!file_exists($backupDir)) {
        //shell_exec("cp -r '$contentDir' '$backupDir'");
    }

    $site = site()->index();
    foreach ($site as $page) {


        if ($page->text()->isNotEmpty()) {
            $text = $page->text()->kt();
            /*$page = $page->update([
                'text' => $text,
            ]);*/
        }

        /*
        * builder to block
        */
        $builder = $page->builder();

        if (!$builder) {
            continue;
        }

        ray((string)$page->title());

        $builder = $builder->yaml();
        $blocks = [];
        foreach ($builder as $block) {
            if (!isset($block['_fieldset'])) {
                continue;
            }

            $migrateFunction = 'migrate' . $block['_fieldset'] . 'Block';

            if (function_exists($migrateFunction)) {
                $blocks[] = $migrateFunction($block);
            } else {
                ray($block)->die();
            }
        }

        if (count($blocks) === 0) {
            continue;
        }

        $migratedPage = $page->update([
            'builder' => $blocks,
        ]);
    }

    //convertBuilderToJson($contentDir);
}

function migratebodytextBlock($block)
{
    ray($block)->label('bodytextBlock');
    $value = kirbytext($block['text']);
    return [
        'content' => ['text' => $value],
        'type' => $block['_fieldset'],
    ];
}

function migrategalleryBlock($block)
{
    ray($block)->label('galleryBlock');
    return [
        'content' => ['pictures' => $block['pictures']],
        'type' => $block['_fieldset'],
    ];
}
