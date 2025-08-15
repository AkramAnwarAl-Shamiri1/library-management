<?php
namespace LibrarySystem\Traits;

trait LoggerTrait {
    public function log(string $message, string $type = 'info'): void {
        if (!isset($_SESSION['logs'])) {
            $_SESSION['logs'] = [];
        }

        $color = match($type) {
            'success' => 'green',
            'warning' => 'orange',
            'error' => 'red',
            default => 'gray',
        };

        $_SESSION['logs'] = [];
        $_SESSION['logs'][] = ['msg' => $message, 'color' => $color];
    }
}
