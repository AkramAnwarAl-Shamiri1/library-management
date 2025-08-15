<?php
namespace LibrarySystem;

abstract class User {
    protected static int $nextId = 1;
    private ?int $id = null;
    protected string $name;
    protected string $email;

    public function __construct(string $name, string $email) {
        $this->id = self::$nextId++;
        $this->name = $name;
        $this->email = $email;
    }

    public function getId(): int {
        if ($this->id === null) {
            $this->id = self::$nextId++;
        }
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    abstract public function role(): string;

    public function sendEmailNotification(string $message) {
        $notifier = new EmailNotification($this->email);
        $notifier->notify($message);
    }
    public function log(string $message, string $type = 'info') {
        if (!isset($_SESSION['logs'])) {
            $_SESSION['logs'] = [];
        }

        $color = match($type) {
            'success' => 'green',
            'warning' => 'orange',
            'error' => 'red',
            default => 'gray'
        };

        $_SESSION['logs'][] = [
            'msg' => $message,
            'color' => $color
        ];
    }
}
