<?php

namespace Crear\TdaCf\Core;

interface ServiceProvider {
    /**
     * @return void
     */
    public function init(): void;

    /**
     * @return array
     */
    public static function definitions(): array;
}