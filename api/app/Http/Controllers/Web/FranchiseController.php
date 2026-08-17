<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FranchiseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FranchiseController extends Controller
{
    public function index(Request $request)
    {
        return view('franchise.index');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['franchise', 'callback'])],
            'budget' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $franchiseRequest = FranchiseRequest::create($validated);

        $this->sendToBitrix24($franchiseRequest);

        return response()->json([
            'success' => true,
            'message' => $validated['type'] === 'callback'
                ? 'Мы перезвоним вам в ближайшее время.'
                : 'Заявка отправлена. Наш менеджер свяжется с вами.',
        ]);
    }

    protected function sendToBitrix24(FranchiseRequest $franchiseRequest): void
    {
        $webhookUrl = config('services.bitrix24.webhook_url');

        if (empty($webhookUrl)) {
            return;
        }

        try {
            Http::post($webhookUrl, [
                'fields' => [
                    'TITLE' => $franchiseRequest->type === 'callback'
                        ? 'Обратный звонок с франшизы fr.gbsale.ru'
                        : 'Заявка на франшизу fr.gbsale.ru',
                    'NAME' => $franchiseRequest->name,
                    'PHONE' => [['VALUE' => $franchiseRequest->phone, 'VALUE_TYPE' => 'WORK']],
                    'EMAIL' => [['VALUE' => $franchiseRequest->email ?? '', 'VALUE_TYPE' => 'WORK']],
                    'COMMENTS' => $this->buildComment($franchiseRequest),
                    'SOURCE_ID' => 'WEB',
                ],
                'params' => ['REGISTER_SONET_EVENT' => 'Y'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Bitrix24 franchise lead error: ' . $e->getMessage());
        }
    }

    protected function buildComment(FranchiseRequest $franchiseRequest): string
    {
        $lines = [];
        $lines[] = 'Тип: ' . ($franchiseRequest->type === 'callback' ? 'Обратный звонок' : 'Заявка на франшизу');
        if ($franchiseRequest->city) {
            $lines[] = 'Город: ' . $franchiseRequest->city;
        }
        if ($franchiseRequest->budget) {
            $lines[] = 'Бюджет: ' . $franchiseRequest->budget;
        }
        if ($franchiseRequest->message) {
            $lines[] = 'Сообщение: ' . $franchiseRequest->message;
        }

        return implode("\n", $lines);
    }
}
