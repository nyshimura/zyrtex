<?php

declare(strict_types=1);

namespace Zyrtex\Services;

use Zyrtex\Core\Env;

/**
 * Serviço de Inteligência Artificial Descentralizada (BYOK - Bring Your Own Key)
 * Suporta Hugging Face Serverless Inference API (gratuito) com fallback para OpenAI, Groq e regras heurísticas.
 */
class AIService {

    /**
     * Traduz e simplifica um laudo técnico para uma linguagem clara e acessível ao cliente final (RF09)
     */
    public static function simplifyDiagnosis(string $technicalDiagnosis, string $equipmentInfo, ?array $tenant = null): array {
        $technicalDiagnosis = trim($technicalDiagnosis);
        if (empty($technicalDiagnosis)) {
            return [
                'success' => false,
                'error' => 'Laudo técnico vazio.',
                'text' => ''
            ];
        }

        $provider = $tenant['ai_provider'] ?? 'huggingface';
        $apiKey = trim((string)($tenant['ai_api_key'] ?? ''));

        // Se o lojista não configurou no painel, tenta pegar fallback global do .env
        if (empty($apiKey)) {
            $apiKey = trim((string)Env::get('HUGGINGFACE_API_KEY', Env::get('HF_TOKEN', '')));
        }

        $systemPrompt = "Você é o assistente virtual de uma assistência técnica especializada. "
                      . "Sua tarefa é ler um laudo técnico com jargões de bancada e reescrevê-lo em uma mensagem "
                      . "curta (máximo 3 a 4 linhas), clara, educada e transparente para o cliente final, explicando de forma leiga "
                      . "o que aconteceu com o aparelho e o que será feito para consertá-lo, transmitindo total segurança.";

        $userPrompt = "Aparelho: {$equipmentInfo}\nLaudo Técnico da Bancada:\n\"{$technicalDiagnosis}\"\n\n"
                    . "Por favor, explique para o cliente o que aconteceu e a solução de forma simples e acolhedora:";

        // 1. Se possuir chave, tenta chamada via API
        if (!empty($apiKey)) {
            if ($provider === 'openai') {
                $result = self::callOpenAI($apiKey, $systemPrompt, $userPrompt);
            } elseif ($provider === 'groq') {
                $result = self::callGroq($apiKey, $systemPrompt, $userPrompt);
            } else {
                // Padrão: Hugging Face Serverless Inference API
                $result = self::callHuggingFace($apiKey, $systemPrompt, $userPrompt);
            }

            if ($result['success'] && !empty($result['text'])) {
                return $result;
            }
        }

        // 2. Fallback Heurístico Inteligente (Offline / Local / Sem Token)
        $fallbackText = self::generateRuleBasedSimplification($technicalDiagnosis, $equipmentInfo);
        return [
            'success' => true,
            'is_fallback' => true,
            'provider' => 'heuristic_engine',
            'text' => $fallbackText
        ];
    }

    /**
     * Gera mensagem persuasiva de remarketing para manutenção preditiva via WhatsApp (RN02)
     */
    public static function generatePredictiveRemarketing(array $os, array $client, ?array $tenant = null): array {
        $clientName = trim((string)($client['name'] ?? 'Cliente'));
        $equipment = trim((string)($os['equipment_info'] ?? 'seu equipamento'));
        $defect = trim((string)($os['defect_reported'] ?? 'reparo'));
        $shopName = trim((string)($tenant['business_name'] ?? 'Nossa Assistência'));

        $provider = $tenant['ai_provider'] ?? 'huggingface';
        $apiKey = trim((string)($tenant['ai_api_key'] ?? Env::get('HUGGINGFACE_API_KEY', Env::get('HF_TOKEN', ''))));

        $systemPrompt = "Você é um consultor de atendimento pós-venda de uma assistência técnica de renome. "
                      . "Gere uma mensagem curta para WhatsApp com tom cordial e profissional convidando o cliente para "
                      . "uma revisão preventiva ou oferecendo uma condição especial para garantir o bom funcionamento do aparelho.";

        $userPrompt = "Cliente: {$clientName}\nAparelho: {$equipment}\nÚltimo serviço: {$defect}\nOficina: {$shopName}\n"
                    . "Gere a mensagem direta com emojis profissionais para enviar pelo WhatsApp:";

        if (!empty($apiKey)) {
            if ($provider === 'openai') {
                $result = self::callOpenAI($apiKey, $systemPrompt, $userPrompt);
            } elseif ($provider === 'groq') {
                $result = self::callGroq($apiKey, $systemPrompt, $userPrompt);
            } else {
                $result = self::callHuggingFace($apiKey, $systemPrompt, $userPrompt);
            }

            if ($result['success'] && !empty($result['text'])) {
                return $result;
            }
        }

        // Fallback Heurístico
        $text = "Olá, {$clientName}! Tudo bem? Aqui é da {$shopName}. "
              . "Faz um tempo que realizamos o serviço no seu {$equipment}. "
              . "Gostaríamos de saber se está tudo funcionando perfeitamente! "
              . "Se precisar de uma checagem preventiva gratuita ou de algum novo ajuste, estamos com condições especiais em nossa bancada nesta semana. Conte sempre conosco! 🔧⭐";

        return [
            'success' => true,
            'is_fallback' => true,
            'provider' => 'heuristic_engine',
            'text' => $text
        ];
    }

    /**
     * Chamada à API Serverless do Hugging Face via cURL
     */
    private static function callHuggingFace(string $token, string $system, string $user): array {
        // Modelo open-source veloz e de alta acurácia em português
        $model = 'Qwen/Qwen2.5-7B-Instruct';
        $url = "https://api-inference.huggingface.co/models/{$model}";

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user]
        ];

        $payload = [
            'inputs' => "<|im_start|>system\n{$system}<|im_end|>\n<|im_start|>user\n{$user}<|im_end|>\n<|im_start|>assistant\n",
            'parameters' => [
                'max_new_tokens' => 280,
                'temperature' => 0.4,
                'return_full_text' => false
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $generatedText = '';
            if (is_array($data)) {
                if (isset($data[0]['generated_text'])) {
                    $generatedText = trim($data[0]['generated_text']);
                } elseif (isset($data['generated_text'])) {
                    $generatedText = trim($data['generated_text']);
                }
            }

            if (!empty($generatedText)) {
                // Limpa tags eventuais de chat template
                $cleaned = preg_replace('/<\|im_end\|>|<\|im_start\|>assistant/i', '', $generatedText);
                return [
                    'success' => true,
                    'is_fallback' => false,
                    'provider' => 'huggingface',
                    'model' => $model,
                    'text' => trim($cleaned)
                ];
            }
        }

        return ['success' => false, 'error' => "HTTP {$httpCode} no Hugging Face", 'text' => ''];
    }

    /**
     * Chamada à API da OpenAI (Fallback opcional)
     */
    private static function callOpenAI(string $token, string $system, string $user): array {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user]
            ],
            'temperature' => 0.5,
            'max_tokens' => 250
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            if (!empty($content)) {
                return ['success' => true, 'is_fallback' => false, 'provider' => 'openai', 'text' => trim($content)];
            }
        }

        return ['success' => false, 'error' => "HTTP {$httpCode} na OpenAI", 'text' => ''];
    }

    /**
     * Chamada à API da Groq (Fallback ultrarrápido)
     */
    private static function callGroq(string $token, string $system, string $user): array {
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'llama-3.1-8b-instant',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user]
            ],
            'temperature' => 0.4,
            'max_tokens' => 250
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            if (!empty($content)) {
                return ['success' => true, 'is_fallback' => false, 'provider' => 'groq', 'text' => trim($content)];
            }
        }

        return ['success' => false, 'error' => "HTTP {$httpCode} na Groq", 'text' => ''];
    }

    /**
     * Motor Heurístico de Simplificação de Laudo Técnico (Regras de Bancada)
     */
    private static function generateRuleBasedSimplification(string $diag, string $eq): string {
        $d = mb_strtolower($diag);
        $eqName = !empty($eq) ? "do seu " . trim($eq) : "do seu equipamento";

        if (str_contains($d, 'tela') || str_contains($d, 'display') || str_contains($d, 'touch') || str_contains($d, 'vidro')) {
            return "Identificamos que o display {$eqName} sofreu danos físicos que afetam a exibição de imagem e a resposta ao toque. Realizaremos a substituição completa por uma nova tela com calibração e garantia de 90 dias.";
        }

        if (str_contains($d, 'bateria') || str_contains($d, 'ciclo') || str_contains($d, 'estufad') || str_contains($d, 'autonomia')) {
            return "Constatamos que a bateria {$eqName} atingiu o limite de vida útil ou sofreu degradação química, ocasionando descarregamento rápido. Faremos a troca por uma bateria nova homologada com saúde 100%.";
        }

        if (str_contains($d, 'conector') || str_contains($d, 'carga') || str_contains($d, 'tipo c') || str_contains($d, 'lightning') || str_contains($d, 'solda fria')) {
            return "O conector de carregamento {$eqName} apresenta desgaste interno nos pinos de contato, impedindo a passagem correta de energia. Faremos a substituição do conector para restabelecer a carga rápida com segurança.";
        }

        if (str_contains($d, 'oxida') || str_contains($d, 'molhad') || str_contains($d, 'umidade') || str_contains($d, 'água') || str_contains($d, 'curto')) {
            return "Detectamos presença de umidade e oxidação em componentes da placa principal {$eqName}. Realizaremos um banho químico ultrassônico para desoxidação profunda, protegendo as trilhas eletrônicas.";
        }

        if (str_contains($d, 'software') || str_contains($d, 'loop') || str_contains($d, 'sistema') || str_contains($d, 'travado') || str_contains($d, 'firmware')) {
            return "Identificamos corrupção no sistema operacional {$eqName}, que causa travamentos ou reinicializações constantes. Faremos a reinstalação limpa do software oficial para restaurar a estabilidade original.";
        }

        // Genérico refinado
        return "Nossa equipe realizou a análise técnica detalhada {$eqName} e localizou o componente com falha. Executaremos a substituição da peça danificada e testes laboratoriais completos para assegurar o funcionamento perfeito com garantia legal.";
    }
}
