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
            $page = $page->update([
                'text' => $text,
            ]);
        }

        if ($page->introText()->isNotEmpty()) {
            $text = $page->introText()->kt();
            $page = $page->update([
                'introText' => $text,
            ]);
        }

        if ((string)$page->intendedTemplate() === "reservation") {
            $dateEvent = $page->parent()->time()->toDate();

            if ($dateEvent < time()) {
                $page->delete();
                continue;
            }
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
                ray($block, $page->intendedTemplate())->die();
            }
        }

        if (count($blocks) === 0) {
            continue;
        }

        $migratedPage = $page->update([
            'builder' => $blocks,
        ]);
    }
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

function migratebodyimageBlock($block)
{
    ray($block)->label('bodyimageBlock');
    return [
        'content' => [
            'picture' => $block['picture'],
            'size' => isset($block['size']) ? $block['size'] : 'default'
        ],
        'type' => $block['_fieldset'],
    ];
}

function migratepodcastEmbedBlock($block)
{
    ray($block)->label('podcastEmbedBlock');
    return [
        'content' => ['podcasturl' => $block['podcasturl']],
        'type' => $block['_fieldset'],
    ];
}


function migrateVideoBlock($block)
{
    ray($block)->label('videoBlock');
    return [
        'content' => ['url' => $block['url']],
        'type' => $block['_fieldset'],
    ];
}
