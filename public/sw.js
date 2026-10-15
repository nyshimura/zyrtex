/**
 * Zyrtex SaaS - Progressive Web App Service Worker
 * Estratégia: Network-First com Cache Fallback e Suporte Offline
 */

const CACHE_NAME = 'zyrtex-pwa-v1';

// Recursos essenciais para shell offline
const PRECACHE_ASSETS = [
    './manifest.json',
    './assets/images/pwa-icon.svg',
    './assets/images/pwa-icon-192.png',
    './assets/images/pwa-icon-512.png'
];

// 1. Instalação do Service Worker & Pre-cache
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('[PWA] Precache warning:', err);
            });
        })
    );
    self.skipWaiting();
});

// 2. Ativação & Limpeza de Caches Antigos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// 3. Estratégia de Fetch (Network-First para dados dinâmicos de OS e Cache Fallback para imagens e shell)
self.addEventListener('fetch', (event) => {
    // Apenas requisições GET
    if (event.request.method !== 'GET') return;

    // Ignora chamadas de API de terceiros ou webhooks
    const url = new URL(event.request.url);
    if (url.pathname.includes('/webhook') || url.pathname.includes('/api/')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // Se a resposta for válida, armazena no cache para contingência
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone).catch(() => {});
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Se estiver sem internet, busca do cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }

                    // Se for navegação de página HTML, exibe tela de contingência offline elegante
                    if (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html')) {
                        return new Response(
                            `<!DOCTYPE html>
                            <html lang="pt-BR">
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>Zyrtex - Modo Offline</title>
                                <style>
                                    body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; text-align: center; }
                                    .card { background: #1e293b; border: 1px solid #334155; padding: 36px 28px; border-radius: 24px; max-width: 420px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
                                    .icon { font-size: 48px; margin-bottom: 16px; display: inline-block; }
                                    h1 { font-size: 20px; font-weight: 800; margin: 0 0 8px; color: #fff; }
                                    p { font-size: 13px; color: #94a3b8; line-height: 1.5; margin: 0 0 24px; }
                                    .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 12px 20px; border-radius: 14px; background: #4f46e5; color: #fff; font-weight: 700; font-size: 13px; text-decoration: none; border: none; cursor: pointer; transition: 0.2s; }
                                    .btn:hover { background: #4338ca; }
                                </style>
                            </head>
                            <body>
                                <div class="card">
                                    <span class="icon">📶</span>
                                    <h1>Sem Conexão com a Internet</h1>
                                    <p>Não foi possível carregar esta tela. Verifique sua conexão Wi-Fi ou dados móveis e tente novamente.</p>
                                    <button onclick="location.reload()" class="btn">Tentar Recarregar Página</button>
                                </div>
                            </body>
                            </html>`,
                            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                        );
                    }
                });
            })
    );
});
