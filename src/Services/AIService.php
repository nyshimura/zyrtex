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
            if ($provider === 'gemini' || str_starts_with($apiKey, 'AIza')) {
                $result = self::callGemini($apiKey, $systemPrompt, $userPrompt);
            } elseif ($provider === 'openai' || str_starts_with($apiKey, 'sk-')) {
                $result = self::callOpenAI($apiKey, $systemPrompt, $userPrompt);
            } elseif ($provider === 'groq' || str_starts_with($apiKey, 'gsk_')) {
                $result = self::callGroq($apiKey, $systemPrompt, $userPrompt);
            } else {
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
            if ($provider === 'gemini' || str_starts_with($apiKey, 'AIza')) {
                $result = self::callGemini($apiKey, $systemPrompt, $userPrompt);
            } elseif ($provider === 'openai' || str_starts_with($apiKey, 'sk-')) {
                $result = self::callOpenAI($apiKey, $systemPrompt, $userPrompt);
            } elseif ($provider === 'groq' || str_starts_with($apiKey, 'gsk_')) {
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
     * Chamada à API do Google Gemini (Totalmente Gratuita no Google AI Studio)
     */
    private static function callGemini(string $apiKey, string $system, string $user): array {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . urlencode($apiKey);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $system . "\n\n" . $user]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 300
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!empty($text)) {
                return [
                    'success' => true,
                    'is_fallback' => false,
                    'provider' => 'gemini',
                    'model' => 'gemini-1.5-flash',
                    'text' => trim($text)
                ];
            }
        }

        return ['success' => false, 'error' => "HTTP {$httpCode} no Gemini", 'text' => ''];
    }

    /**
     * Chamada à API do Hugging Face via Router v1
     */
    private static function callHuggingFace(string $token, string $system, string $user): array {
        $url = 'https://router.huggingface.co/v1/chat/completions';
        $model = 'meta-llama/Llama-3.1-8B-Instruct';

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user]
            ],
            'max_tokens' => 280,
            'temperature' => 0.4
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            if (!empty($content)) {
                return [
                    'success' => true,
                    'is_fallback' => false,
                    'provider' => 'huggingface',
                    'model' => $model,
                    'text' => trim($content)
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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
     * Chamada à API da Groq (Ultrarrápida e Gratuita)
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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
     * Motor Heurístico Inteligente de Simplificação de Laudo Técnico (Regras de Bancada & NLP Leve)
     */
    private static function generateRuleBasedSimplification(string $diag, string $eq): string {
        $d = mb_strtolower(trim($diag));
        $eqName = !empty($eq) ? "do seu " . trim($eq) : "do seu equipamento";

        // Variações de Abertura
        $openers = [
            "Nossa análise laboratorial detalhada {$eqName} identificou que ",
            "Avaliamos minuciosamente o seu equipamento e constatamos que ",
            "Durante os testes especializados na bancada técnica, detectamos que ",
            "Concluímos a avaliação técnica {$eqName} e localizamos que "
        ];
        $opener = $openers[array_rand($openers)];

        // Variações de Fechamento / Garantia
        $closers = [
            " Faremos os testes laboratoriais completos para assegurar o funcionamento perfeito com garantia legal de 90 dias.",
            " O procedimento inclui calibração completa e testes rigorosos para devolver seu aparelho 100% confiável e com garantia.",
            " Todas as intervenções seguem nosso padrão técnico de alta precisão com garantia legal e total segurança.",
            " Realizaremos a revisão e conferência de todas as funções para garantir máxima durabilidade com garantia."
        ];
        $closer = $closers[array_rand($closers)];

        // 1. Tela / Vidro / Display / Touch / AMOLED / OLED
        if (str_contains($d, 'tela') || str_contains($d, 'display') || str_contains($d, 'touch') || str_contains($d, 'vidro') || str_contains($d, 'amoled') || str_contains($d, 'oled') || str_contains($d, 'frontal')) {
            $desc = "o display/vidro sofreu avarias físicas que prejudicam a nitidez da imagem e a sensibilidade ao toque. Realizaremos a substituição completa da peça por um novo módulo original/homologado com calibração.";
            return $opener . $desc . $closer;
        }

        // 2. Bateria / Autonomia / Desligando
        if (str_contains($d, 'bateria') || str_contains($d, 'ciclo') || str_contains($d, 'estufad') || str_contains($d, 'autonomia') || str_contains($d, 'descarregando') || str_contains($d, 'saúde') || str_contains($d, 'drenando')) {
            $desc = "a bateria atingiu o limite de ciclos úteis ou apresentou degradação química, causando descarregamento rápido e instabilidade. Efetuaremos a troca por uma bateria nova de alta capacidade e saúde 100%.";
            return $opener . $desc . $closer;
        }

        // 3. Conector / Carga / Entrada Tipo-C / Lightning
        if (str_contains($d, 'conector') || str_contains($d, 'carga') || str_contains($d, 'dock') || str_contains($d, 'tipo c') || str_contains($d, 'type-c') || str_contains($d, 'lightning') || str_contains($d, 'não carrega') || str_contains($d, 'mau contato')) {
            $desc = "o conector de carregamento apresenta desgaste físico ou folga nos pinos de contato, impedindo a passagem regular de energia. Faremos a troca da sub-placa/conector para restabelecer a recarga rápida com total segurança.";
            return $opener . $desc . $closer;
        }

        // 4. Oxidação / Contato com Líquido / Água
        if (str_contains($d, 'oxida') || str_contains($d, 'molhad') || str_contains($d, 'umidade') || str_contains($d, 'água') || str_contains($d, 'banho químico') || str_contains($d, 'corrosão')) {
            $desc = "houve entrada de umidade, provocando oxidação em trilhas e componentes internos da placa. Executaremos desoxidação química em cuba ultrassônica, secagem térmica e restauração das linhas eletrônicas.";
            return $opener . $desc . $closer;
        }

        // 5. Placa Principal / Curto / CI / Microssolda
        if (str_contains($d, 'placa') || str_contains($d, 'curto') || str_contains($d, 'ci') || str_contains($d, 'pmic') || str_contains($d, 'solda') || str_contains($d, 'reballing') || str_contains($d, 'tristar') || str_contains($d, 'circuito') || str_contains($d, 'capacitor') || str_contains($d, 'não liga')) {
            $desc = "há um componente em curto-circuito na placa principal que impede o fluxo normal de energia ou a inicialização. Realizaremos o reparo avançado de microssolda para substituir o componente eletrônico danificado.";
            return $opener . $desc . $closer;
        }

        // 6. Áudio / Alto-falante / Microfone / Auricular
        if (str_contains($d, 'áudio') || str_contains($d, 'audio') || str_contains($d, 'som') || str_contains($d, 'alto-falante') || str_contains($d, 'alto falante') || str_contains($d, 'microfone') || str_contains($d, 'auricular') || str_contains($d, 'mudo') || str_contains($d, 'chiando') || str_contains($d, 'viva-voz')) {
            $desc = "o módulo de áudio/microfone apresenta falha de diafragma ou obstrução nos canais acústicos. Realizaremos a substituição do componente sonoro para que chamadas e mídias voltem a tocar com clareza cristalina.";
            return $opener . $desc . $closer;
        }

        // 7. Câmeras / Lente / Foco
        if (str_contains($d, 'câmera') || str_contains($d, 'camera') || str_contains($d, 'foco') || str_contains($d, 'lente') || str_contains($d, 'vidro da câmera')) {
            $desc = "o módulo óptico ou a lente de proteção sofreu impacto, gerando fotos embaçadas ou trepidação no foco. Realizaremos a troca do sensor/lente com vedação anti-poeira para restabelecer fotos perfeitas.";
            return $opener . $desc . $closer;
        }

        // 8. Software / Sistema / Travamento / Vírus
        if (str_contains($d, 'software') || str_contains($d, 'loop') || str_contains($d, 'sistema') || str_contains($d, 'travado') || str_contains($d, 'firmware') || str_contains($d, 'formata') || str_contains($d, 'reiniciando')) {
            $desc = "o sistema operacional corrompeu arquivos essenciais de inicialização, provocando travamentos ou reinicializações. Faremos a restauração limpa do software original com atualização de segurança.";
            return $opener . $desc . $closer;
        }

        // 9. Botões / Carcaça / Tampa Traseira
        if (str_contains($d, 'botão') || str_contains($d, 'botao') || str_contains($d, 'power') || str_contains($d, 'volume') || str_contains($d, 'carcaça') || str_contains($d, 'tampa') || str_contains($d, 'chassi')) {
            $desc = "há desgaste mecânico ou deformação física nos botões ou na estrutura externa do aparelho. Faremos a troca dos flex de acionamento e alinhamento do chassi para restabelecer o clique suave original.";
            return $opener . $desc . $closer;
        }

        // 10. Dinâmico: incorpora o texto real digitado pelo técnico
        $cleanDiag = preg_replace('/\s+/', ' ', trim($diag));
        if (mb_strlen($cleanDiag) > 130) {
            $cleanDiag = mb_substr($cleanDiag, 0, 127) . '...';
        }

        return $opener . "a necessidade de intervenção especializada referente a: \"{$cleanDiag}\". Nossa equipe executará a substituição pontual dos itens avariados e revisará os circuitos de apoio." . $closer;
    }
}
