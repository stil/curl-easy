<?php

namespace cURL;

interface RequestsQueueInterface
{
    public function getDefaultOptions(): Options;

    public function setDefaultOptions(Options $defaultOptions): void;

    public function attach(Request $request): self;

    public function detach(Request $request): self;

    public function send(): void;

    public function socketPerform(): bool;

    public function socketSelect($timeout): bool;
}
