<?php

    namespace cURL;

    interface RequestInterface
    {
        public function getOptions(): Options;

        public function setOptions(Options $options): void;

        public function getUID(): int;

        public function socketPerform(): bool;

        public function socketSelect($timeout): bool;

        public function send(): Response;
    }
