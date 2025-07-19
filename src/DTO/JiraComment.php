<?php

namespace AHAbid\JiraItTest\DTO;

use DateTime;

readonly class JiraComment
{
    public function __construct(
        public DateTime $createdAt,
        public string $body,
        public string $authorName,
        public ?string $authorEmail = null,
    ) {}
}
