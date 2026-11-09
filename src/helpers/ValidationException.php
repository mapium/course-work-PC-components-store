<?php

namespace App\Helpers;

class ValidationException extends \InvalidArgumentException {
    public $errors;

    public function __construct($field, $message) {
        parent::__construct($message);
        $this->errors = [$field => $message];
    }
}
