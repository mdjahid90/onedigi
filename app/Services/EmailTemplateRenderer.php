<?php

namespace App\Services;

use App\Models\EmailTemplate;

class EmailTemplateRenderer
{
    public function render(string $templateName, array $vars): array
    {
        $template = EmailTemplate::query()->where('name', $templateName)->first();

        if (!$template) {
            return [
                'subject' => $templateName,
                'body' => '',
            ];
        }

        $subject = $this->replaceVars($template->subject, $vars);
        $body = $this->replaceVars($template->body, $vars);

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    private function replaceVars(string $text, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $text = str_replace('{{'.$key.'}}', (string) $value, $text);
        }

        return $text;
    }
}
