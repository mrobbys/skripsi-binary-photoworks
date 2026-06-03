<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Helper notifikasi dengan toast
     */
    protected function toast(
        string $type = 'success',
        string $title = ''
    ): array {
        return [
            'type' => $type,
            'title' => $title,
        ];
    }

    /**
     * Helper notifikasi dengan alert
     */
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
