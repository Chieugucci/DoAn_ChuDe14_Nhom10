<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-950 text-slate-100 font-sans">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chủ đề 14: SSE AI Streaming Response trong PHP (Real-time Chat & Protocol Inspector)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['Fira Code', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(51, 65, 85, 0.8);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(71, 85, 105, 1);
        }

        .typing-cursor::after {
            content: '❚';
            display: inline-block;
            margin-left: 2px;
            animation: blink 0.8s infinite;
            color: #38bdf8;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        .glow-green {
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.6);
        }

        .glow-amber {
            box-shadow: 0 0 12px rgba(245, 158, 11, 0.6);
        }

        .glow-red {
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
        }
    </style>
</head>

<body class="h-full flex flex-col overflow-hidden bg-slate-950 text-slate-100">

    <!-- Top Header -->
    <header class="h-14 border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md px-4 flex items-center justify-between shrink-0 z-20">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-emerald-500/10 border border-emerald-500/30 rounded-lg text-emerald-400">
                <i data-lucide="zap" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-sm font-semibold tracking-wide text-slate-100 flex items-center gap-2">
                    Chủ đề 14: SSE Real-time AI Chat in PHP
                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800 font-mono">Production Ready</span>
                </h1>
                <p class="text-[11px] text-slate-400">Server-Sent Events (SSE) Output Buffering & Real-time Chunk Visualizer</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-full">
                <span id="statusLed" class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                <span id="statusText" class="text-xs font-medium font-mono text-slate-400">IDLE / READY</span>
            </div>

            <button onclick="clearAllLogs()" class="text-xs text-slate-400 hover:text-slate-200 transition flex items-center gap-1.5 px-2.5 py-1.5 rounded bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear Visualizer
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:flex-row overflow-hidden relative">

        <!-- LEFT PANEL: AI Chat Client -->
        <section class="w-full md:w-1/2 flex flex-col border-r border-slate-800/80 bg-slate-950 relative">
            <div class="p-3 bg-slate-900/40 border-b border-slate-800/60 flex items-center justify-between text-xs text-slate-300 font-medium">
                <div class="flex items-center gap-2">
                    <i data-lucide="bot" class="w-4 h-4 text-emerald-400"></i>
                    <span>AI Assistant Client View</span>
                </div>

                <div class="flex items-center gap-3 bg-slate-900 border border-slate-800 px-3 py-1 rounded-lg">
                    <label for="delaySlider" class="text-[11px] text-slate-400 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3 text-amber-400"></i> Delay:
                        <span id="delayValue" class="font-mono text-amber-400 font-bold">40ms</span>
                    </label>
                    <input type="range" id="delaySlider" min="5" max="200" value="40" step="5" class="w-24 accent-amber-500 cursor-pointer h-1.5 bg-slate-700 rounded-lg">
                </div>
            </div>

            <div id="chatHistory" class="flex-1 overflow-y-auto p-4 space-y-4 font-sans text-sm">
                <div class="bg-slate-900/70 border border-slate-800 rounded-xl p-4 text-slate-300 text-xs space-y-2">
                    <div class="flex items-center gap-2 font-semibold text-emerald-400 text-sm">
                        <i data-lucide="sparkles" class="w-4 h-4"></i> Thử Nghiệm Kỹ Thuật Streaming SSE
                    </div>
                    <p class="leading-relaxed">
                        Chọn câu hỏi mẫu bên dưới hoặc tự nhập câu hỏi. Máy chủ PHP sẽ phát luồng dữ liệu chunk-by-chunk qua giao thức <span class="text-emerald-400 font-mono">text/event-stream</span>.
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <button onclick="presetPrompt('Giải thích ob_flush() và flush() trong PHP SSE')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded-md text-[11px] border border-slate-700/60 transition">
                            ⚡ Giải thích ob_flush() và flush()...
                        </button>
                        <button onclick="presetPrompt('Hướng dẫn khắc phục Nginx proxy buffering khi stream SSE')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded-md text-[11px] border border-slate-700/60 transition">
                            ⚡ Hướng dẫn khắc phục Nginx...
                        </button>
                        <button onclick="presetPrompt('So sánh SSE với WebSockets và Long Polling')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded-md text-[11px] border border-slate-700/60 transition">
                            ⚡ So sánh SSE với WebSockets và Long Polling...
                        </button>
                        <button onclick="presetPrompt('Bảo mật kết nối SSE trong PHP với JWT Auth và CORS')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded-md text-[11px] border border-slate-700/60 transition">
                            ⚡ Bảo mật kết nối SSE trong PHP...
                        </button>
                        <button onclick="presetPrompt('Giải pháp xử lý Heartbeat Ping để duy trì kết nối SSE')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded-md text-[11px] border border-slate-700/60 transition">
                            ⚡ Giải pháp xử lý Heartbeat Ping...
                        </button>
                        <button onclick="presetPrompt('Tối ưu hiệu năng PHP-FPM khi mở hàng ngàn kết nối SSE đồng thời')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-2.5 py-1 rounded-md text-[11px] border border-slate-700/60 transition">
                            ⚡ Tối ưu hiệu năng PHP-FPM...
                        </button>
                    </div>
                </div>
            </div>


            <div class="p-3 bg-slate-900/80 border-t border-slate-800/80">
                <form id="chatForm" onsubmit="handleSend(event)" class="relative flex items-center gap-2">
                    <textarea id="promptInput" rows="2" placeholder="Nhập câu hỏi cho AI (VD: Giải thích cơ chế SSE trong PHP)..." class="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500/80 focus:ring-1 focus:ring-emerald-500/80 rounded-xl px-3.5 py-2.5 text-xs text-slate-200 placeholder-slate-500 resize-none outline-none transition"></textarea>

                    <div class="flex flex-col gap-1.5 shrink-0">
                        <button type="submit" id="sendBtn" class="bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-medium px-4 py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-950/40 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i> Send
                        </button>
                        <button type="button" id="stopBtn" onclick="stopStreaming()" disabled class="bg-rose-950/80 hover:bg-rose-900 border border-rose-800/80 text-rose-300 font-medium px-4 py-1.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition opacity-40 cursor-not-allowed">
                            <i data-lucide="square" class="w-3 h-3 fill-current"></i> Stop
                        </button>
                    </div>
                </form>
                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 font-mono">
                    <span>Engine: PHP 8.2 SSE Simulator</span>
                    <span id="metricsLabel">Latency: 0ms | Chunks: 0</span>
                </div>
            </div>
        </section>

        <!-- RIGHT PANEL: Inspector & Docs -->
        <section class="w-full md:w-1/2 flex flex-col bg-slate-900/50">
            <div class="flex items-center border-b border-slate-800 bg-slate-900 px-2 shrink-0">
                <button id="tabBtn-inspector" onclick="switchTab('inspector')" class="px-4 py-3 text-xs font-medium border-b-2 border-emerald-500 text-emerald-400 flex items-center gap-2 transition">
                    <i data-lucide="terminal" class="w-4 h-4"></i> Raw SSE Inspector
                    <span id="packetBadge" class="bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded text-[10px] font-mono">0</span>
                </button>
            </div>

            <!-- Tab 1: Inspector -->
            <div id="tabContent-inspector" class="flex-1 flex flex-col overflow-hidden p-3 space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-400 px-1 font-mono">
                    <span class="flex items-center gap-1.5"><i data-lucide="radio" class="w-3.5 h-3.5 text-sky-400"></i> PACKET TRAFFIC: HTTP 200 OK (text/event-stream)</span>
                    <button onclick="clearPackets()" class="text-[11px] text-slate-400 hover:text-slate-200 underline">Clear</button>
                </div>
                <div id="packetStream" class="flex-1 overflow-y-auto bg-slate-950 border border-slate-800/90 rounded-xl p-3 font-mono text-xs space-y-2 select-text">
                    <div class="text-slate-600 text-[11px] italic">// Các gói tin SSE dạng `event: message\ndata: {...}\n\n` sẽ xuất hiện ở đây theo thời gian thực...</div>
                </div>
            </div>

            <!-- Tab 2: README & Project Files -->
            <div id="tabContent-readme" class="hidden flex-1 overflow-y-auto p-4 space-y-4 text-xs">
                <div id="toastNotification" class="hidden bg-emerald-950 border border-emerald-700/80 text-emerald-200 px-3 py-2 rounded-lg text-xs flex items-center justify-between transition">
                    <span class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> <span id="toastMsg">Đã sao chép!</span></span>
                </div>

                <!-- PHP backend code block -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden">
                    <div class="bg-slate-900 border-b border-slate-800 px-4 py-2 flex items-center justify-between">
                        <span class="font-mono text-emerald-400 flex items-center gap-2"><i data-lucide="file-code" class="w-4 h-4"></i> stream.php</span>
                        <button onclick="copyCode('phpCode')" class="text-slate-400 hover:text-slate-200 flex items-center gap-1"><i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy</button>
                    </div>
                    <pre class="p-3 text-[11px] font-mono text-slate-300 leading-relaxed overflow-x-auto"><code id="phpCode">&lt;?php
// backend/stream.php - Server-Sent Events Handler in PHP
set_time_limit(0);
ini_set('max_execution_time', '0');

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Tắt buffering phía Nginx

while (ob_get_level() > 0) {
    ob_end_clean();
}

function sendEvent($data, $event = 'message') {
    if (is_array($data)) {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    echo "event: {$event}\n";
    echo "data: {$data}\n\n";
    flush();
}

$prompt = $_POST['prompt'] ?? $_GET['prompt'] ?? '';
$response = "Chào bạn! Đây là câu trả lời được stream theo thời gian thực từ PHP Server...";

$words = mb_split(' ', $response);
foreach ($words as $word) {
    if (connection_aborted()) break;
    sendEvent(['chunk' => $word . ' ']);
    usleep(40000); // 40ms delay per chunk
}

sendEvent('[DONE]', 'done');
exit;</code></pre>
                </div>
        </section>

    </main>

    <!-- JS Logic -->
    <script>
        let isStreaming = false;
        let packetCount = 0;
        let activeChunkDelay = 40;
        let currentAiBubble = null;
        let startTime = 0;

        const AI_RESPONSES = {
            "ob_flush": "Kỹ thuật SSE trong PHP đòi hỏi 2 hàm cốt lõi:\n\n1. `ob_end_clean()` / `ob_flush()`: Xóa hoặc đẩy dữ liệu khỏi bộ đệm nội bộ của PHP (Output Buffering).\n2. `flush()`: Đẩy dữ liệu từ Web Server (Nginx/Apache) ra TCP Socket để gửi tới trình duyệt.\n\nNếu thiếu bộ đôi này, toàn bộ văn bản sẽ bị giữ lại ở RAM server và chỉ gửi một lượt khi script kết thúc!",

            "nginx": "Khi dùng Nginx làm Reverse Proxy cho PHP-FPM, mặc định Nginx bật `proxy_buffering`. Điều này khiến gói tin SSE bị tắc nghẽn.\n\nCách khắc phục:\n1. Trong PHP: Khai báo `header('X-Accel-Buffering: no');`\n2. Trong Nginx Config (`nginx.conf`):\n```nginx\nlocation /stream.php {\n    proxy_pass http://127.0.0.1:9000;\n    proxy_buffering off;\n    proxy_cache off;\n    chunked_transfer_encoding on;\n}\n```",

            "compare": "So sánh 3 công nghệ Real-time truyền tải dữ liệu Web:\n\n1. **SSE (Server-Sent Events):**\n- Chiều: Đơn hướng (Server -> Client).\n- Giao thức: HTTP/1.1 hoặc HTTP/2 chuẩn.\n- Phù hợp nhất: Real-time Text Streaming (AI Chatbot), Thông báo (Notification), Bảng giá chứng khoán.\n\n2. **WebSockets:**\n- Chiều: Song hướng (Full-Duplex - Server <-> Client).\n- Giao thức: WS / WSS (Chuyển tiếp qua HTTP Handshake).\n- Phù hợp nhất: Game Online đa người chơi, Ứng dụng chat 2 chiều, Whiteboard cộng tác.\n\n3. **Long Polling:**\n- Chiều: HTTP Request giữ lâu cho đến khi có data.\n- Nhược điểm: Overhead cao, gây quá tải Server khi số người dùng tăng.",

            "security": "Hướng dẫn Bảo mật kết nối SSE trong PHP:\n\n1. **Xác thực với JWT (JSON Web Token):**\n- Khi dùng `Fetch API`, bạn có thể thêm Header: `Authorization: Bearer <TOKEN>`.\n- Trong PHP (`stream.php`), giải mã token trước khi cho phép mở stream: `$token = getallheaders()['Authorization'] ?? '';`.\n\n2. **Cấu hình CORS an toàn:**\n```php\nheader('Access-Control-Allow-Origin: https://yourdomain.com');\nheader('Access-Control-Allow-Credentials: true');\nheader('Access-Control-Allow-Headers: Authorization, Content-Type');\n```\n\n3. **Chống tấn công DoS / Connection Abuse:** Giới hạn số lượng connection đồng thời per IP bằng Redis hoặc Rate Limiter.",

            "heartbeat": "Kỹ thuật Heartbeat (Keep-Alive Ping) trong SSE:\n\nCác Load Balancer (AWS ALB, Cloudflare) hoặc Router thường đóng kết nối `idle` sau 30s-60s nếu không có dữ liệu qua lại.\n\n**Giải pháp:** Gửi gói tin comment chèn giữa các dòng dữ liệu mỗi 15 giây:\n\n```php\n// Gửi comment W3C SSE (Bắt đầu bằng dấu hai chấm ':')\necho \": heartbeat ping\\n\\n\";\nflush();\n```\nTrình duyệt sẽ tự động bỏ qua dòng comment này mà không ảnh hưởng tới giao diện người dùng!",

            "concurrency": "Giải pháp tối ưu PHP khi mở Hàng ngàn kết nối SSE đồng thời (High Concurrency):\n\n1. **Vấn đề của PHP-FPM:** Mỗi kết nối SSE giữ 1 worker process. Nếu có 500 kết nối, 500 worker sẽ tiêu tốn hết RAM!\n\n2. **Giải pháp nâng cao:**\n- **Sử dụng Async PHP (Swoole / Workerman / ReactPHP):** Chạy Event Loop theo cơ chế Non-blocking I/O (tương tự Node.js), một process PHP có thể duy trì 10,000+ kết nối SSE với lượng RAM cực ít.\n- **Sử dụng Message Broker (Redis Pub/Sub / NATS):** PHP xử lý logic đẩy message vào Redis channel, một worker EventLoop chuyên dụng sẽ chịu trách nhiệm SSE Push xuống Client.",

            "default": "Server-Sent Events (SSE) là giao thức truyền dữ liệu một chiều (Server -> Client) qua HTTP tiêu chuẩn với Content-Type `text/event-stream`. Giải pháp này cực kỳ tối ưu cho các ứng dụng AI Chatbot real-time vì độ trễ thấp và triển khai dễ dàng hơn WebSocket."
        };

        window.onload = () => {
            lucide.createIcons();
            document.getElementById('delaySlider').addEventListener('input', (e) => {
                activeChunkDelay = parseInt(e.target.value);
                document.getElementById('delayValue').innerText = `${activeChunkDelay}ms`;
            });
        };

        function switchTab(tabName) {
            ['inspector', 'readme', 'script'].forEach(t => {
                const btn = document.getElementById(`tabBtn-${t}`);
                const content = document.getElementById(`tabContent-${t}`);
                if (t === tabName) {
                    btn.className = "px-4 py-3 text-xs font-medium border-b-2 border-emerald-500 text-emerald-400 flex items-center gap-2 transition";
                    content.classList.remove('hidden');
                } else {
                    btn.className = "px-4 py-3 text-xs font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 transition";
                    content.classList.add('hidden');
                }
            });
        }

        function presetPrompt(text) {
            document.getElementById('promptInput').value = text;
        }

        function updateStatus(state) {
            const led = document.getElementById('statusLed');
            const text = document.getElementById('statusText');
            if (state === 'STREAMING') {
                led.className = "w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse glow-green";
                text.innerText = "SSE STREAMING ACTIVE";
                text.className = "text-xs font-medium font-mono text-emerald-400";
            } else if (state === 'FINISHED') {
                led.className = "w-2.5 h-2.5 rounded-full bg-slate-500";
                text.innerText = "STREAM CLOSED [DONE]";
                text.className = "text-xs font-medium font-mono text-slate-400";
            } else if (state === 'ERROR') {
                led.className = "w-2.5 h-2.5 rounded-full bg-rose-500 glow-red";
                text.innerText = "STREAM INTERRUPTED";
                text.className = "text-xs font-medium font-mono text-rose-400";
            }
        }

        function handleSend(e) {
            e.preventDefault();
            const input = document.getElementById('promptInput');
            const prompt = input.value.trim();
            if (!prompt || isStreaming) return;

            appendUserMessage(prompt);
            input.value = '';
            startSseStreaming(prompt);
        }

        function appendUserMessage(text) {
            const history = document.getElementById('chatHistory');
            history.insertAdjacentHTML('beforeend', `
                <div class="flex justify-end">
                    <div class="bg-emerald-600/90 text-white px-4 py-2.5 rounded-2xl rounded-tr-none max-w-[85%] text-xs shadow-md">
                        <p class="whitespace-pre-wrap">${escapeHtml(text)}</p>
                    </div>
                </div>
            `);
            scrollToBottom();
        }

        function createAiMessageBubble() {
            const history = document.getElementById('chatHistory');
            const bubbleId = `ai-msg-${Date.now()}`;
            history.insertAdjacentHTML('beforeend', `
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0 mt-0.5">
                        <i data-lucide="bot" class="w-4 h-4"></i>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 text-slate-200 px-4 py-3 rounded-2xl rounded-tl-none max-w-[85%] text-xs shadow-sm space-y-2">
                        <p id="${bubbleId}" class="whitespace-pre-wrap leading-relaxed typing-cursor"></p>
                    </div>
                </div>
            `);
            lucide.createIcons();
            scrollToBottom();
            return document.getElementById(bubbleId);
        }

        async function startSseStreaming(prompt) {
            isStreaming = true;
            startTime = performance.now();
            updateControlsState(true);
            updateStatus('STREAMING');

            let responseText = AI_RESPONSES.default;
            const p = prompt.toLowerCase();
            if (p.includes('ob_flush') || p.includes('flush')) {
                responseText = AI_RESPONSES.ob_flush;
            } else if (p.includes('nginx')) {
                responseText = AI_RESPONSES.nginx;
            } else if (p.includes('so sánh') || p.includes('websocket') || p.includes('polling')) {
                responseText = AI_RESPONSES.compare;
            } else if (p.includes('bảo mật') || p.includes('jwt') || p.includes('cors')) {
                responseText = AI_RESPONSES.security;
            } else if (p.includes('heartbeat') || p.includes('ping') || p.includes('duy trì')) {
                responseText = AI_RESPONSES.heartbeat;
            } else if (p.includes('tối ưu') || p.includes('swoole') || p.includes('concurrency')) {
                responseText = AI_RESPONSES.concurrency;
            }

            currentAiBubble = createAiMessageBubble();
            let accumulatedText = "";

            logRawPacket("HTTP/1.1 200 OK\nContent-Type: text/event-stream\nCache-Control: no-cache\nConnection: keep-alive\nX-Accel-Buffering: no\n\n", "header");

            const words = responseText.split(' ');
            for (let i = 0; i < words.length; i++) {
                if (!isStreaming) break;

                const chunk = words[i] + (i === words.length - 1 ? "" : " ");
                accumulatedText += chunk;
                currentAiBubble.innerText = accumulatedText;
                scrollToBottom();

                const ssePayload = {
                    chunk: chunk
                };
                logRawPacket(`event: message\ndata: ${JSON.stringify(ssePayload)}\n\n`, "data");

                packetCount++;
                document.getElementById('packetBadge').innerText = packetCount;
                const elapsed = Math.round(performance.now() - startTime);
                document.getElementById('metricsLabel').innerText = `Latency: ${elapsed}ms | Chunks: ${packetCount}`;

                await new Promise(r => setTimeout(r, activeChunkDelay));
            }

            if (isStreaming) {
                logRawPacket(`event: done\ndata: [DONE]\n\n`, "done");
                finishStreaming();
            }
        }

        function stopStreaming() {
            if (!isStreaming) return;
            isStreaming = false;
            logRawPacket(`// Client closed connection (EventSource.close() / AbortController)`, "error");
            finishStreaming(true);
        }

        function finishStreaming(interrupted = false) {
            isStreaming = false;
            updateControlsState(false);
            if (currentAiBubble) currentAiBubble.classList.remove('typing-cursor');
            updateStatus(interrupted ? 'ERROR' : 'FINISHED');
        }

        function updateControlsState(streaming) {
            document.getElementById('sendBtn').disabled = streaming;
            const stopBtn = document.getElementById('stopBtn');
            stopBtn.disabled = !streaming;
            if (streaming) {
                stopBtn.classList.remove('opacity-40', 'cursor-not-allowed');
            } else {
                stopBtn.classList.add('opacity-40', 'cursor-not-allowed');
            }
            document.getElementById('promptInput').disabled = streaming;
        }

        function logRawPacket(content, type) {
            const container = document.getElementById('packetStream');
            const time = new Date().toLocaleTimeString('vi-VN', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                fractionalSecondDigits: 3
            });
            let badgeStyle = "bg-slate-800 text-slate-300";
            if (type === 'header') badgeStyle = "bg-purple-950 text-purple-300 border border-purple-800/80";
            if (type === 'data') badgeStyle = "bg-emerald-950 text-emerald-300 border border-emerald-800/80";
            if (type === 'done') badgeStyle = "bg-sky-950 text-sky-300 border border-sky-800/80";
            if (type === 'error') badgeStyle = "bg-rose-950 text-rose-300 border border-rose-800/80";

            container.insertAdjacentHTML('beforeend', `
                <div class="border-b border-slate-900 pb-1.5 pt-1 font-mono text-[11px] leading-relaxed">
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mb-1">
                        <span class="px-1.5 py-0.2 rounded ${badgeStyle}">${type.toUpperCase()}</span>
                        <span>${time}</span>
                    </div>
                    <pre class="text-slate-300 whitespace-pre-wrap ${type === 'header' ? 'text-purple-300' : ''}">${escapeHtml(content)}</pre>
                </div>
            `);
            container.scrollTop = container.scrollHeight;
        }

        function clearPackets() {
            document.getElementById('packetStream').innerHTML = `<div class="text-slate-600 text-[11px] italic">// Nhật ký đã xóa...</div>`;
            packetCount = 0;
            document.getElementById('packetBadge').innerText = '0';
        }

        function clearAllLogs() {
            clearPackets();
            document.getElementById('chatHistory').innerHTML = '';
            document.getElementById('metricsLabel').innerText = 'Latency: 0ms | Chunks: 0';
            updateStatus('FINISHED');
        }

        function scrollToBottom() {
            const h = document.getElementById('chatHistory');
            h.scrollTop = h.scrollHeight;
        }

        function escapeHtml(str) {
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }

        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const msg = document.getElementById('toastMsg');
            if (!toast || !msg) return;
            msg.innerText = message;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function copyCode(elementId) {
            const codeText = document.getElementById(elementId).innerText;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(codeText).then(() => showToast("Đã sao chép mã nguồn PHP!"));
            } else {
                showToast("Đã sao chép vào bộ nhớ tạm!");
            }
        }
    </script>
</body>

</html>