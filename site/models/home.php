<?php

use Kirby\Cms\Page;

class HomePage extends Page
{
    public function getJsonData(): array
    {
        $content = [];
        $disruptorUrl = $this->disruptor_url()->getText();
        $disruptorText = $this->disruptor_text()->getText();
        $disruptorColorScheme = $this->disruptor_color_scheme()->getText();
        $disruptorLinkBlank = $this->disruptor_link_blank()->toBool();

        $content["backgroundImages"] = $this->background()->getImages([
            "width" => 2400,
            "height" => null,
            "manipulation" => "resize",
        ]);


        $content["intro"] = [
            "intro" => $this->intro()->getText(),
            "url" => $this->intro_url()->getText(),
            "urlTitle" => $this->intro_url_title()->getText(),
        ];

        $content["cta"] = null;
        if ($disruptorUrl && $disruptorText) {
            $content["cta"] = [
                "url" => $disruptorUrl,
                "text" => $disruptorText,
                "colorScheme" => $disruptorColorScheme,
                "linkBlank" => $disruptorLinkBlank,
            ];
        }

        return $content;
    }
}
