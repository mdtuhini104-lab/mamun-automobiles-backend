<?php

namespace App\Services;

class NotificationTemplateService
{
    public function render($templateBody, array $variables = [])
    {
        $rendered = $templateBody;
        
        foreach ($variables as $key => $value) {
            $rendered = str_replace("{{" . $key . "}}", $value, $rendered);
        }
        
        return $rendered;
    }
}
