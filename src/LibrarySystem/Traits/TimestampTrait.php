<?php
namespace LibrarySystem\Traits;

trait TimestampTrait {
    public function timestamp(): string {
        return date('Y-m-d H:i:s');
    }
}
