<?php

class Controllers
{
    public function __invoke()
    {
        return [
            "site" => function ($page, $kirby) {
                $intendedTemplate = $page->intendedTemplate()->name();
                $language = (string) $kirby->languageCode();
                $uri = (string) $page->uri();
                $resultUriHome = "/";
                $resultUri = "/{$uri}";

                if ($language) {
                    $resultUriHome = "/{$language}";
                    $resultUri = "/{$language}/{$uri}";
                }

                $isFooterAnimation = $page->animation()->getOption();

                return [
                    "json" => [
                        "intendedTemplate" => $intendedTemplate,
                        "title" => (string) $page->title(),
                        "uri" => $uri === "home" ? $resultUriHome : $resultUri,
                        "language" => $language ? $language : "de",
                        "meta" => $page->getMeta(),
                        "footerAnimation" => $isFooterAnimation !== "no" ? $isFooterAnimation : null,
                    ],
                ];

                return $result;
            },
        ];
    }
}
