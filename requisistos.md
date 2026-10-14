# Documentação Zyrtex: Sistema de Gestão SaaS para Assistências Técnicas

## 1. Visão Geral
Plataforma SaaS (Software as a Service) multi-tenant de alta performance para gestão de assistências técnicas e oficinas. O sistema opera sob um modelo de negócios **Freemium**: o uso básico é gratuito e monetizado via inserções nativas de Google AdSense. Recursos premium, como Domínio Customizado e Alteração de Nicho, são comercializados separadamente. O ecossistema unifica operações de balcão (PDV), fluxo de oficina (OS) e inteligência artificial preditiva, construído inteiramente em arquitetura server-side robusta.

## 2. Stack de Tecnologia, Arquitetura e UX
- **Backend e Lógica Base:** **PHP Puro (Server-Side Rendering - SSR)**. A arquitetura dispensa o uso de JavaScript ou frameworks client-side para a lógica de negócios, garantindo máxima compatibilidade, segurança nativa contra manipulação no navegador e carregamento instantâneo do HTML.
- **Frontend:** HTML5 puro, PHP e Tailwind CSS. 
- **Identidade Visual Zyrtex (Design System):**
  - **Primária (Zyrtex Indigo):** `indigo-600` (Light) e `indigo-400` (Dark).
  - **Ação/Conversão (Zyrtex Emerald):** `emerald-500` (Light) e `emerald-400` (Dark).
  - **Bases de Contraste:** Fundos em `slate-50` (Light) e `slate-900` (Dark).
- **Comunicação de Dados:** Uso rigoroso de requisições **HTTP POST e GET** padrão. Formulários disparam ações diretas para os controllers PHP, que processam a lógica, interagem com o banco e redirecionam (Header Location) com mensagens de sessão (Flash Messages).
- **UI/UX Global e Fallbacks Visuais:** 
  - Comunicação de erros/sucesso via *Flash Messages* em blocos de alerta (Toasts renderizados nativamente pelo PHP).
  - Modais gerenciados via CSS puro (pseudo-classes `:target` ou checkboxes ocultos).
  - **Avatares Dinâmicos** para usuários sem foto e **Placeholders em SVG** para mídias corrompidas no Drive.
- **Banco de Dados:** MySQL.
- **Integrações Nativas:** Mercado Pago (Lojistas e Super Admin), API WhatsApp (redirecionamento server-side), **IA Descentralizada de Texto (BYOK - Hugging Face Serverless Inference / OpenAI / Groq via cURL)** e Google Drive API (via PHP Client).

## 3. Requisitos Funcionais (Core e Inovações)

### 3.1. Operação, Controle de Acesso e Roteamento
- **RF01 - Roteamento Absoluto (Landing Page do SaaS vs. Vitrine do Lojista vs. Painéis):**
  - **Raiz da Plataforma Zyrtex (ex: `zyrtex.com.br` ou `localhost/zyrtex`):** Renderiza a **Landing Page do SaaS**, voltada para donos de assistência técnica. Apresenta o sistema, convida para cadastro imediato gratuito, detalha o modelo freemium (gratuito para sempre mantido por anúncios do AdSense), oferece o upgrade do plano de Domínio Customizado (sem anúncios) e exibe uma sessão de apoio/doações comunitárias (*"Buy Me a Coffee"* / Pix de apoio).
  - **Raiz do Domínio/Subdomínio do Lojista (ex: `oficinajoao.zyrtex.com.br` ou `www.oficinajoao.com.br`):** Renderiza a **Vitrine Comercial e Landing Page da Oficina** voltada para o cliente final daquela loja (apresentação, promoções, rastreio de OS e prontuário).
  - **Subdiretório `/admin`:** Painel administrativo restrito do lojista e de sua equipe técnica.
  - **Subdiretório `/superadmin`:** Painel exclusivo do dono da Zyrtex protegido por master password.
- **RF02 - Trava de Nicho de Atuação:** O nicho do sistema (Celulares, PCs, Automotivo, etc.) é escolhido **apenas uma vez, no momento do cadastro**. Toda a estrutura de banco de dados, checklists e formulários será montada com base nessa escolha.
- **RF03 - Controle de Acesso Granular (ACL):** Dentro do `/admin`, o Administrador do Lojista pode convidar funcionários e ligar/desligar o acesso a módulos específicos.
- **RF04 - PDV Totem e Motor Dinâmico de OS:** Interface ágil renderizada em PHP para vendas e geração de OS. O PDV deve ser desenhado no estilo totem de fast-food (otimizado para telas grandes e touch screen com cards amplos e toque facilitado), e a OS deve ser gerada em PDF (layout profissional pronto para impressão física ou envio digital).

### 3.2. Monetização do SaaS (Super Admin)
- **RF05 - Integração Google AdSense Nativa:** Blocos de anúncios gerados dinamicamente via PHP e injetados nas views do sistema (meio de tabelas, catálogos).
- **RF06 - Assinatura Premium de Domínio (Anual):** Módulo para o lojista comprar o direito de usar seu próprio domínio apontado via DNS.
- **RF07 - Taxa de Alteração de Nicho (Monetização Adicional):** Caso o lojista decida mudar de nicho (ex: mudar de "Celulares" para "Automotivo") após o cadastro, o sistema exige o pagamento de uma taxa única via Mercado Pago, **independentemente de ele já possuir a assinatura Premium de domínio ativa**.

### 3.3. Relacionamento, Gestão e Configurações
- **RF08 - HUB Financeiro do Lojista (Toggle Pix Manual vs. Mercado Pago Automático):** O lojista escolhe e alterna de forma intuitiva como deseja receber de seus clientes através de uma chave seletora (*Toggle*):
  - *Modo Pix Manual:* O lojista informa sua chave Pix (CPF, CNPJ, E-mail, Celular ou Aleatória) e o Nome do Favorecido. O sistema gera a chave copia-e-cola e QR Code estático nas OSs e no balcão. O lojista confirma o pagamento manualmente.
  - *Modo Mercado Pago Automático:* Gera QR Code Pix dinâmico ou link de pagamento via API cURL do Mercado Pago com baixa automática por Webhook.
  - *UX Intuitiva:* Badges visuais destacados indicam em tempo real qual modo está ativo no momento (`[MODO ATIVO: PIX MANUAL]` ou `[MODO ATIVO: MERCADO PAGO AUTOMÁTICO]`).
- **RF09 - Inteligência Artificial Descentralizada (BYOK com Hugging Face):** Tradução e simplificação de laudos técnicos em linguagem acessível ao cliente final via requisição cURL do servidor PHP para a API de Inferência do Hugging Face (usando token gratuito `hf_...` com modelos open-source de texto como `Qwen/Qwen2.5-7B-Instruct` ou `meta-llama/Llama-3.1-8B-Instruct`), com fallback/opção para OpenAI e Groq.
- **RF10 - Armazenamento via Google Drive (BYOS):** Upload de mídias direto do servidor PHP para a pasta compartilhada do Google Drive do lojista.
- **RF11 - Vitrine Comercial e Promoções na Landing Page (`/`):** A página inicial pública da loja funciona como vitrine de conversão contendo:
  - Cabeçalho com Logotipo da oficina, slogan e dados de contato.
  - Grade de Serviços e Promoções em Destaque (até 6 cards gerenciáveis com título, descrição, valor promocional e botão direto para o WhatsApp).
  - Horários de funcionamento (dias da semana e sábados/domingos) e endereço físico completo com botão de rota no Google Maps/Waze.
  - Botão flutuante de WhatsApp com mensagem contextualizada.
  - Seção de Rastreio de OS em tempo real e Prontuário Antifraude.
- **RF12 - Central de Configurações, Tokens e Tips de Autoatendimento (`/admin/configuracoes`):** Painel em abas onde o lojista gerencia sua empresa, vitrine, formas de pagamento e integrações técnicas:
  - **Mini Tutoriais Embutidos ("Tips"):** Para cada integração técnica (Token de IA do Hugging Face, Credenciais e Webhook do Mercado Pago, Pasta do Google Drive), a tela disponibiliza cards com instruções mastigadas e links diretos (ex: link para `huggingface.co/settings/tokens` e `mercadopago.com.br/developers`), permitindo autonomia total do lojista sem necessidade de suporte técnico.
  - Botões de "Testar Conexão" para feedback instantâneo de validade de tokens.
- **RF14 - Gerador de Etiquetas Adesivas & Selo QR Code (Lacre de Garantia):** Impressão de mini-etiquetas adesivas para fixação em aparelhos ou peças trocadas (suporte a tamanhos padrão como 50x30mm, 40x25mm e grade A4). Contém o QR Code direto para o rastreio da OS, número da OS, data de saída e aviso de lacre de garantia. Ao escanear o QR Code pelo celular, cliente ou técnico acessam na hora a ficha do serviço.
- **RF15 - Portal do Cliente (Área do Cliente com Aceite de Orçamento & Histórico Financeiro):** Link seguro e direto para o cliente final da oficina acessar seu portal exclusivo:
  - **Aprovação Digital de Orçamento em 1 Clique:** Visualização discriminada de peças e mão de obra com botão "Aprovar Orçamento". O PHP registra no banco: IP, Data/Hora e User-Agent do cliente como comprovante legal de autorização.
  - **Histórico de Equipamentos & OSs:** Consulta de todas as intervenções anteriores realizadas na oficina.
  - **Histórico Financeiro & Pagamento Online:** Lista de faturas em aberto e histórico de pagamentos, com opção de pagar imediatamente via Pix (Copia-e-Cola / QR Code) ou Mercado Pago.
- **RF16 - Motor de Impressão Multiformato e Gestão de Impressoras (Estilo iFood / Keeta):** Central dedicada em `/admin/configuracoes` (Aba Impressão) permitindo que o lojista escolha e formate sua saída de impressão de acordo com o equipamento físico que possui:
  - **Bobina Térmica 80mm** (padrão balcão largo).
  - **Bobina Térmica 58mm** (padrão mini impressora bluetooth / maquininha portátil).
  - **Folha A4 Completa** (1 via inteira ou 2 vias compactas na mesma folha).
  - **Etiquetas Adesivas Térmicas** (50x30mm / 40x25mm).
  - Configurações do cupom: ligar/desligar logotipo, incluir chave Pix no rodapé, incluir QR Code de rastreio e botão de "Imprimir Teste de Alinhamento".
- **RF17 - Notificação Ativa no WhatsApp em 1 Clique:** Botões dinâmicos na tela da OS para gerar e disparar mensagens formatadas com link direto para o Portal do Cliente (Avisos de: "Orçamento Pronto para Aprovação", "Aparelho Pronto para Retirada", "Lembrete de Pagamento").

## 4. Requisitos Não Funcionais (Performance e Segurança)
- **RNF01 - Multi-Tenant e SSR:** Isolamento absoluto de dados filtrados por `tenant_id` no backend. Todo HTML é devolvido já processado pelo servidor.
- **RNF02 - Compressão Server-Side (PHP GD/Imagick):** Sem o uso de JS, as imagens anexadas às OSs são enviadas em seu formato original via formulário (`multipart/form-data`). O PHP no servidor recebe, comprime, gera a thumbnail em base64 e envia o original para o Google Drive do cliente, apagando o arquivo temporário do servidor em seguida. Limite rígido de 15MB por arquivo.
- **RNF03 - Segurança Global:** Proteção contra CSRF nativa do PHP (tokens em formulários), Rate Limiting contra abusos de login e backups via Cron Jobs.
- **RNF04 - Privacidade (LGPD):** Ofuscação de dados e registro minucioso de logs de auditoria.

## 5. Regras de Negócio e Diferenciais
- **RN01 - Prontuário Antifraude:** Rastreamento do histórico do equipamento (Serial/IMEI).
- **RN02 - Manutenção Preditiva com IA:** IA (Hugging Face) analisa o ciclo de vida do equipamento e gera sugestões/textos automáticos de remarketing para o lojista enviar via WhatsApp.
- **RN03 - Política de Estorno do SaaS (Procon):** Botão automatizado de estorno (para domínio e mudança de nicho) habilitado **apenas durante os primeiros 7 dias** (Art. 49 do CDC).
- **RN04 - Blindagem Jurídica e Termo de Abandono no PDF da OS:** Impressão automática de cláusulas de proteção legal no PDF oficial da Ordem de Serviço:
  - Garantia legal de 90 dias para peças e serviços (Art. 26 do CDC).
  - Cláusula de abandono de equipamento (autorização de destinação/descarte após 90 dias da notificação de conclusão sem retirada).
  - Check-in de avarias prévias com espaço para assinatura física do cliente no balcão.
- **RN05 - Baixa Automática de Estoque na OS:** Ao mudar o status da OS para "Aprovado" ou "Em Andamento", o sistema abate automaticamente a quantidade das peças utilizadas da tabela de produtos do PDV, evitando venda de itens que já estão em bancada.

## 6. Proteção do Modelo de Negócios e Blindagem Jurídica (SaaS Owner Shield)
- **RN-Shield01 - Purge de Contas Fantasmas:** Rotina automatizada (Cron Job) programada para varrer o banco de dados. Contas Freemium inativas por 90 dias recebem e-mail; aos 120 dias, são excluídas.
- **RN-Shield02 - Isenção de Responsabilidade Solidária:** Termo de Serviço obrigatório eximindo a Zyrtex de responsabilidades civis ou fiscais sobre os orçamentos da loja.
- **RN-Shield03 - Fallback de Domínio, Quarentena de Disco e Concorrência:**
  - *Fallback Gracioso de Domínio:* Em caso de não renovação da anuidade de domínio customizado após 365 dias, o roteador reverte automaticamente a loja para o subdomínio grátis (`{slug}.zyrtex.com.br`) sem derrubar as operações da oficina.
  - *Quarentena de Disco:* Remoção incondicional de arquivos temporários de upload em bloco `try/finally` e rejeição de arquivos superiores a 15MB no PHP.
  - *Bloqueio de Edição Simultânea:* Verificação de concorrência com timestamp `updated_at` na OS para evitar que dois funcionários sobrescrevam o trabalho um do outro.

## 7. Estrutura Base do Banco de Dados (SaaS Architecture)
*Nota: Operacionais possuem `tenant_id`.*

- `tenants (Lojistas)`: id, business_name, default_url, custom_domain, expires_at, is_premium, theme_color, business_type, active, logo_path, address_json, opening_hours_json, promotions_json, printer_settings_json, payment_mode (manual_pix, mercadopago), pix_key, pix_key_type, pix_beneficiary, mp_public_key, mp_access_token, gdrive_folder_id, gdrive_token_json, ai_provider, ai_api_key.
- `users`: id, tenant_id, name, email, password_hash, role, permissions_json.
- `clients` e `service_orders`: Estrutura padrão com isolamento de tenant. Na `service_orders`: os_number, identifier_code, checklist_json, technical_diagnosis, approval_status, approval_ip, approval_date, approval_user_agent, payment_status, payment_method, updated_at.
- `os_media`: id, tenant_id, os_id, media_type, gdrive_file_id, thumbnail_base64.
- `saas_subscriptions`: id, tenant_id, mp_payment_id, plan_type (annual_domain, niche_change), amount, status, purchased_at, expires_at, is_refunded.
- `saas_settings`: id, config_key, config_value (preços dos planos, credenciais MP da Zyrtex, scripts AdSense, flags).

## 8. Padrões de Arquitetura e Estrutura de Arquivos (PHP Puro Isolado)
Para garantir manutenção e escalabilidade sem "código espaguete", o padrão MVC clássico server-side é estritamente aplicado:
- **Separação Lógica Absoluta:**
  - `db.php` (Conexão via PDO lendo do `.env`).
  - `/controllers/`: Arquivos PHP que apenas recebem POST/GET, validam regras, operam o banco e redirecionam (`header('Location: ...')`). Não há `echo` de HTML aqui.
  - `/views/`: Arquivos PHP que misturam HTML/Tailwind e apenas imprimem variáveis já processadas (ex: `<?= $user['name'] ?>`).
- **Isolamento de Roteamento:**
  - `public/index.php`: Roteador da Landing Page do Lojista.
  - `public/admin/index.php`: Roteador restrito ao Lojista e seus técnicos.
  - `public/superadmin/index.php`: Roteador isolado e exclusivo do dono do SaaS (Zyrtex).

## 9. Painel do Super Admin (SaaS Owner)
A interface exclusiva do dono da Zyrtex (`/superadmin`) faz parte da mesma base de código para reaproveitamento de rotinas, mas roda sob um namespace/diretório completamente isolado para não misturar com a lógica das oficinas. Acessível via rota secreta e protegida por Master Password.

### 9.1. Métricas e KPIs (Visão Executiva)
- **Tração e Engajamento:** Gráficos server-side de Lojas Cadastradas, Taxa de Churn e Lojas Ativas.
- **Financeiro e Monetização:** Receita recorrente (Domínios e Taxas de Nicho) e painel do AdSense.

### 9.2. CRM Interno e Suporte
- **Sistema de Tickets Integrado:** Lojistas abrem chamados (`/admin`); Zyrtex responde (`/superadmin`).
- **Central de Broadcast:** Sistema injeta avisos via PHP diretamente nas views `/admin` dos lojistas ativos.

### 9.3. Ferramentas de Facilitação e Controle de Crise
- **Shadow Login (Impersonation):** O Super Admin clica em um lojista e o PHP injeta a sessão (`$_SESSION['tenant_id'] = $alvo`), redirecionando para `/admin` como se fosse o cliente.
- **Feature Toggles (Kill Switches):** Interruptores globais no painel para desligar módulos.
- **Gerenciador Dinâmico de Ads:** Campo no painel para atualizar o script do AdSense globalmente através da tabela `saas_settings`.