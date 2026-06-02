<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function toast(
        string $type = 'success',
        string $title = ''
    ): array {
        return [
            'type' => $type,
            'title' => $title,
        ];
    }

    protected function alert(
        string $type = 'success',
        string $title = '',
        string $message = ''
    ): array {
        return [
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
        ];
    }
}
