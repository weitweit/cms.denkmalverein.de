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

        if ($page->migrate()->isTrue()) {
            continue;
        }

        // delete expired reservations
        if ((string)$page->intendedTemplate() === "reservation") {
            $dateEvent = $page->parent()->time()->toDate();

            if ($dateEvent < time()) {
                $page->delete();
                continue;
            }
        }

        // delete all network-reservation pages
        if ((string)$page->intendedTemplate() === "network-reservation") {
            $page->delete();
            continue;
        }


        /*
        * builder to block
        */
        $builder = $page->builder();

        if ($builder) {

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

            if (count($blocks) > 0) {

                ray((string)$page->title())->label('migrated blocks');

                $page = $page->update([
                    'builder' => $blocks,
                ]);
            }
        }

        // migrate markdown to writer
        if ($page->text()->isNotEmpty()) {
            $text = $page->text()->kt();
            $page = $page->update([
                'text' => $text,
            ]);
        }

        // migrate markdown to writer
        if ($page->introText()->isNotEmpty()) {
            $text = $page->introText()->kt();
            $page = $page->update([
                'introText' => $text,
            ]);
        }

        if ($page->intro_text()->isNotEmpty()) {
            $text = $page->intro_text()->kt();
            $page = $page->update([
                'intro_text' => $text,
            ]);
        }

        // migrate markdown to writer
        if ($page->footer_text()->isNotEmpty()) {
            $text = $page->footer_text()->kt();
            $page = $page->update([
                'footer_text' => $text,
            ]);
        }

        // migrate markdown to writer
        if ($page->descriptionAccount()->isNotEmpty()) {
            $text = $page->descriptionAccount()->kt();
            $page = $page->update([
                'descriptionAccount' => $text,
            ]);
        }

        // migrate markdown to writer
        if ($page->description()->isNotEmpty()) {
            $text = $page->description()->kt();
            $page = $page->update([
                'description' => $text,
            ]);
        }

        $page = $page->update([
            'migrate' => true,
        ]);


        // if page intendedTemplate is donate change it to default
        if ((string)$page->intendedTemplate() === "donate") {
            $page = $page->changeTemplate('default');
        }

        if ((string)$page->intendedTemplate() === "safed-list") {
            $page = $page->changeTemplate('safed-top');
        }

        if ((string)$page->intendedTemplate() === "losses") {
            $page = $page->changeTemplate('losses-top');
        }

        if ((string)$page->intendedTemplate() === "notices") {
            $page = $page->changeTemplate('notices-top');
        }

        if ((string)$page->intendedTemplate() === "press-articles") {
            $page = $page->changeTemplate('press-articles-top');
        }
    }
}

function migratebodytextBlock($block)
{
    //ray($block)->label('bodytextBlock');
    $value = kirbytext($block['text']);
    //ray($value)->label('bodytextBlock')->die();
    return [
        'content' => ['text' => $value],
        'type' => $block['_fieldset'],
    ];
}

function migrategalleryBlock($block)
{
    return [
        'content' => ['pictures' => $block['pictures']],
        'type' => $block['_fieldset'],
    ];
}

function migratebodyimageBlock($block)
{
    //ray($block)->label('bodyimageBlock');
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
    //ray($block)->label('podcastEmbedBlock');
    return [
        'content' => ['podcasturl' => $block['podcasturl']],
        'type' => $block['_fieldset'],
    ];
}


function migratevideoBlock($block)
{
    //ray($block)->label('videoBlock');
    return [
        'content' => ['url' => $block['url']],
        'type' => $block['_fieldset'],
    ];
}

function migratedownloadBlock($block)
{
    ray($block)->label('downloadBlock');
    return [
        'content' => ['file' => $block['file']],
        'type' => $block['_fieldset'],
    ];
}
