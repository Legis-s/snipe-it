<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Process;
use Throwable;

class TimewebInvoiceRecognizer
{
    private const MAX_DOCUMENT_CHARACTERS = 40000;

    public function recognize(UploadedFile $file): array
    {
        $url = rtrim((string) config('services.timeweb_ai.url'), '/');
        $token = (string) config('services.timeweb_ai.token');

        if ($url === '' || $token === '') {
            throw new RuntimeException('Не настроены OPEN_AI_URL и OPEN_AI_TOKEN.');
        }

        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ],
            [
                'role' => 'user',
                'content' => $this->documentContent($file),
            ],
        ];

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $response = $this->sendRequest($url, $token, $messages, $attempt === 1 ? 4000 : 7000);
            $content = $this->messageContent($response);

            if ($content !== '') {
                return $this->decodeJson($content);
            }

            Log::warning('Timeweb invoice recognition returned empty content', array_merge(
                ['attempt' => $attempt],
                $this->responseDiagnostics($response)
            ));

            $messages[] = [
                'role' => 'user',
                'content' => 'Предыдущий ответ был пустым. Верни полный JSON строго по указанной схеме.',
            ];
        }

        throw new RuntimeException('Timeweb AI дважды вернул пустой ответ.');
    }

    private function sendRequest(string $url, string $token, array $messages, int $maxTokens): Response
    {
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(90)
                ->retry(2, 500, throw: false)
                ->post($url.'/chat/completions', [
                    'model' => 'agent',
                    'messages' => $messages,
                    'max_completion_tokens' => $maxTokens,
                    'stream' => false,
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Could not connect to Timeweb invoice recognition', [
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Не удалось подключиться к Timeweb AI.');
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new RuntimeException('Timeweb AI отклонил OPEN_AI_TOKEN.');
        }

        if ($response->failed()) {
            Log::warning('Timeweb invoice recognition failed', [
                'status' => $response->status(),
                'response' => mb_substr($response->body(), 0, 1000),
            ]);

            throw new RuntimeException('Timeweb AI временно недоступен.');
        }

        return $response;
    }

    private function messageContent(Response $response): string
    {
        $content = $response->json('choices.0.message.content');
        if (is_string($content)) {
            return trim($content);
        }

        if (! is_array($content)) {
            return '';
        }

        return trim(collect($content)
            ->map(fn ($part) => is_array($part) ? ($part['text'] ?? $part['content'] ?? '') : '')
            ->filter('is_string')
            ->implode(''));
    }

    private function responseDiagnostics(Response $response): array
    {
        $message = $response->json('choices.0.message');

        return [
            'status' => $response->status(),
            'response_id' => $response->json('id'),
            'model' => $response->json('model'),
            'finish_reason' => $response->json('choices.0.finish_reason'),
            'message_keys' => is_array($message) ? array_keys($message) : [],
            'usage' => $response->json('usage'),
        ];
    }

    private function documentContent(UploadedFile $file): string|array
    {
        if ($file->getMimeType() === 'application/pdf') {
            $text = $this->extractPdfText($file);

            $text = trim(preg_replace('/[ \t]+/', ' ', $text) ?? '');
            if (mb_strlen($text) < 20) {
                throw new RuntimeException('PDF не содержит распознаваемого текста. Загрузите счёт как JPG или PNG.');
            }

            return "Распознай данные этого счёта:\n\n".mb_substr($text, 0, self::MAX_DOCUMENT_CHARACTERS);
        }

        $mimeType = $file->getMimeType() ?: 'image/jpeg';

        return [
            [
                'type' => 'text',
                'text' => 'Распознай данные этого счёта.',
            ],
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:'.$mimeType.';base64,'.base64_encode((string) file_get_contents($file->getRealPath())),
                ],
            ],
        ];
    }

    private function extractPdfText(UploadedFile $file): string
    {
        $errors = [];

        if (is_executable('/usr/bin/pdftotext')) {
            $process = new Process([
                '/usr/bin/pdftotext',
                '-layout',
                $file->getRealPath(),
                '-',
            ]);
            $process->setTimeout(30);

            try {
                $process->run();
                if ($process->isSuccessful() && trim($process->getOutput()) !== '') {
                    return $process->getOutput();
                }

                $errors['pdftotext'] = trim($process->getErrorOutput()) ?: 'empty output';
            } catch (Throwable $exception) {
                $errors['pdftotext'] = $exception->getMessage();
            }
        } else {
            $errors['pdftotext'] = 'executable is unavailable';
        }

        try {
            $text = (new Parser)->parseFile($file->getRealPath())->getText();
            if (trim($text) !== '') {
                return $text;
            }

            $errors['pdfparser'] = 'empty output';
        } catch (Throwable $exception) {
            $errors['pdfparser'] = $exception->getMessage();
        }

        Log::warning('Could not extract invoice PDF text', [
            'errors' => $errors,
        ]);

        throw new RuntimeException('Не удалось прочитать PDF. Попробуйте сохранить его повторно или загрузить счёт как JPG/PNG.');
    }

    private function decodeJson(string $content): array
    {
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $content) ?? $content;
        }

        $data = json_decode($content, true);
        if (! is_array($data) || ! isset($data['items']) || ! is_array($data['items'])) {
            throw new RuntimeException('Timeweb AI вернул некорректный формат счёта.');
        }

        return $data;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Ты распознаёшь российские счета на оплату для системы складских закупок.
Верни только валидный JSON без markdown:
{
  "purchase_name": "краткое название закупки",
  "invoice_number": "номер счёта или null",
  "final_price": 0,
  "delivery_cost": 0,
  "supplier": "название поставщика или null",
  "supplier_inn": "ИНН поставщика или null",
  "buyer": "название покупателя или null",
  "buyer_inn": "ИНН покупателя или null",
  "comment": "краткое описание счёта",
  "items": [
    {
      "type": "asset или consumable",
      "name": "наименование позиции",
      "model_number": "артикул или модель, если указан",
      "quantity": 1,
      "unit_price": 0,
      "vat_percent": 0,
      "warranty_months": 0
    }
  ]
}
Правила:
- purchase_name — краткое и конкретное название на русском языке из 3–10 слов по основным позициям счёта, например «Закупка SSD Netac и ленты Evolis».
- Не включай в purchase_name номер счёта, название поставщика, стоимость и общие слова без указания предмета закупки.
- supplier и supplier_inn бери из реквизитов продавца/поставщика.
- buyer и buyer_inn бери из реквизитов покупателя/плательщика. Не путай покупателя с грузополучателем.
- Денежные значения возвращай числами без пробелов и символов валют.
- unit_price — цена одной единицы с НДС.
- type=asset для оборудования с индивидуальным учётом; type=consumable для расходных материалов и комплектующих.
- Не придумывай отсутствующие реквизиты: используй null или 0.
- Каждую строку счёта верни отдельным элементом items.
PROMPT;
    }
}
