<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client;

final class Avatar
{
    /** @var string */
    private $content;

    /** @var string */
    private $mimeType;

    public function __construct(string $data, string $mimeType)
    {
        $this->content = $data;
        $this->mimeType = $mimeType;
    }

    public function image(): string
    {
        return $this->content;
    }

    public function imageBase64(): string
    {
        return base64_encode($this->content);
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function toHtml(array $attributes = []): string
    {
        unset($attributes['src']);
        $htmlAttributes = [];
        foreach ($attributes as $key => $value) {
            $htmlAttributes[] = "$key=\"$value\"";
        }

        return
            sprintf(
                "<img src=\"data:%s;base64,%s\" %s></img>",
                $this->mimeType(),
                $this->imageBase64(),
                implode(' ', $htmlAttributes)
            );
    }
}
