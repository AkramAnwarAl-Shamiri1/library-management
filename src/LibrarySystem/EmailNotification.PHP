<?php
namespace LibrarySystem;

class EmailNotification implements NotificationInterface {
    private string $to;

    public function __construct(string $toEmail = "") {
        $this->to = $toEmail;
    }

    public function notify(string $message) {
      
        if (!$this->to) return;

        if (!isset($_SESSION['logs'])) $_SESSION['logs'] = [];
  
        $_SESSION['logs'][] = [
            'msg' => "Email to {$this->to}: $message",
            'color' => 'gray'
        ];
    }
}
