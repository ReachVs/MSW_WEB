<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAD APE COMMAND — SECURE ACCESS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface": "#04080f",
                        "background": "#020408",
                        "primary": "#ffffff",
                        "on-primary": "#020408",
                        "secondary": "#ef4444",
                        "on-secondary": "#ffffff",
                        "on-surface": "#ffffff",
                        "on-surface-variant": "#94a3b8",
                        "outline": "#45474c",
                        "outline-variant": "#1e293b",
                        "surface-container-low": "#080c14",
                        "surface-container-high": "#111827",
                        "primary-container": "#1e293b",
                        "surface-variant": "#0f172a",
                    },
                    borderRadius: { DEFAULT: "0px", lg: "0px", xl: "0px", full: "9999px" },
                    spacing: {
                        unit: "4px", md: "24px", margin: "48px", xs: "4px",
                        sm: "12px", lg: "48px", xl: "80px", gutter: "24px",
                    },
                    fontFamily: {
                        "headline-lg": ["Space Grotesk"],
                        "headline-md": ["Space Grotesk"],
                        "body-md": ["Space Grotesk"],
                        "label-sm": ["Space Grotesk"],
                    },
                    fontSize: {
                        "headline-lg": ["40px", { lineHeight: "1.2", letterSpacing: "-0.02em", fontWeight: "600" }],
                        "headline-md": ["32px", { lineHeight: "1.3", fontWeight: "600" }],
                        "body-md": ["16px", { lineHeight: "1.6", fontWeight: "400" }],
                        "label-sm": ["11px", { lineHeight: "1.0", letterSpacing: "0.18em", fontWeight: "700" }],
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #020408;
            color: #ffffff;
            -webkit-font-smoothing: antialiased;
        }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }
        .scanline {
            width: 100%; height: 4px;
            background: linear-gradient(to bottom, transparent, rgba(239,68,68,0.1), transparent);
            position: absolute; top: 0; z-index: 50; pointer-events: none;
            animation: scan 10s linear infinite;
        }
        @keyframes scan { 0% { top: -5%; } 100% { top: 105%; } }
        .grid-bg {
            background-image:
                linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        .mechanical-field {
            background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0) 100%);
            box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
        }
        .glass-overlay {
            backdrop-filter: blur(12px);
            background: rgba(8,12,20,0.8);
        }
    </style>
</head>
<body class="font-body-md overflow-hidden h-screen flex flex-col items-center justify-center relative">

<div class="fixed inset-0 grid-bg pointer-events-none" id="gridBg"></div>
<div class="fixed inset-0 bg-[radial-gradient(circle_at_center,transparent_0%,#020408_100%)] pointer-events-none"></div>
<div class="scanline"></div>

{{-- HEADER --}}
<header class="fixed top-0 left-0 w-full flex justify-between items-center px-margin py-md border-b border-outline-variant z-40 bg-[#020408]/90 backdrop-blur-md">
    <div class="flex items-center space-x-md">
        <div class="flex flex-col">
            <span class="font-headline-md text-headline-md font-bold tracking-tighter text-primary leading-none">MAD APE</span>
            <span class="font-label-sm text-[10px] text-secondary tracking-[0.4em]">ADMIN PROTOCOL</span>
        </div>
        <div class="h-8 w-px bg-outline-variant"></div>
        <div class="hidden md:flex flex-col">
            <span class="font-label-sm text-[9px] text-on-surface-variant uppercase">AUTH_SERVICE</span>
            <span class="font-label-sm text-[9px] text-primary">ENCRYPTED_TUNNEL_ESTABLISHED</span>
        </div>
    </div>
    <div class="flex items-center space-x-lg">
        <div class="flex flex-col items-end">
            <span class="font-label-sm text-[10px] uppercase text-on-surface-variant">Uptime</span>
            <span class="font-label-sm text-[10px] text-primary" id="uptime">428:12:09:55</span>
        </div>
        <div class="flex items-center space-x-sm bg-surface-container-high px-sm py-xs border border-outline-variant">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
            <span class="font-label-sm text-[10px] text-on-surface uppercase tracking-widest">Live</span>
        </div>
    </div>
</header>

{{-- LOGIN FORM --}}
<main class="relative z-20 w-full max-w-2xl px-md">
    <div class="glass-overlay border border-outline-variant p-xl shadow-[0_0_50px_rgba(0,0,0,0.8)] relative overflow-hidden group">
        {{-- Corner metadata --}}
        <div class="absolute top-4 left-4 flex flex-col opacity-30 group-hover:opacity-60 transition-opacity">
            <span class="font-label-sm text-[8px]">SEQ: 88-X91</span>
            <span class="font-label-sm text-[8px]">MOD: LOGIN_V4</span>
        </div>
        <div class="absolute top-4 right-4 flex flex-col items-end opacity-30 group-hover:opacity-60 transition-opacity">
            <span class="font-label-sm text-[8px]">LOG_ATTEMPTS: 0</span>
            <span class="font-label-sm text-[8px]">IP: 192.168.x.x</span>
        </div>
        {{-- Corner brackets --}}
        <div class="absolute top-0 left-0 w-8 h-8 border-t border-l border-secondary/50"></div>
        <div class="absolute top-0 right-0 w-8 h-8 border-t border-r border-outline"></div>
        <div class="absolute bottom-0 left-0 w-8 h-8 border-b border-l border-outline"></div>
        <div class="absolute bottom-0 right-0 w-8 h-8 border-b border-r border-secondary/50"></div>

        <div class="mb-xl text-center relative">
            <div class="inline-block mb-sm">
                <span class="font-label-sm text-[10px] bg-secondary text-on-secondary px-2 py-0.5">RESTRICTED AREA</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg uppercase tracking-tight mb-xs">SECURE AUTHORIZATION</h1>
            <div class="flex items-center justify-center space-x-sm opacity-60">
                <div class="h-px w-8 bg-outline-variant"></div>
                <p class="font-label-sm text-label-sm uppercase tracking-[0.2em]">Protocol: MADAPE</p>
                <div class="h-px w-8 bg-outline-variant"></div>
            </div>
        </div>

        <form class="space-y-lg relative z-10" id="loginForm" action="{{ route('admin.login.post') }}" method="POST">
            @csrf
            {{-- Admin ID --}}
            <div class="space-y-sm">
                <div class="flex justify-between items-end">
                    <label class="font-label-sm text-[10px] text-on-surface-variant uppercase" for="adminId">Administrator Identifier</label>
                    <span class="font-label-sm text-[9px] text-outline">HEX_VAL: 0x41444D</span>
                </div>
                <div class="relative mechanical-field border {{ $errors->has('email') ? 'border-secondary' : 'border-outline-variant' }} focus-within:border-primary transition-all group/input">
                    <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within/input:text-primary transition-colors text-lg">fingerprint</span>
                    <input autocomplete="off"
                           class="w-full bg-transparent border-none focus:ring-0 text-primary py-md pl-14 pr-md font-body-md tracking-widest placeholder:text-outline uppercase"
                           id="adminId" name="email" placeholder="admin@example.com" type="email"
                           value="{{ old('email') }}" required>
                    <div class="absolute right-0 top-0 bottom-0 w-1 bg-outline-variant group-focus-within/input:bg-primary transition-colors"></div>
                </div>
                @error('email')
                    <p class="font-label-sm text-[10px] text-secondary uppercase tracking-widest">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="space-y-sm">
                <div class="flex justify-between items-end">
                    <label class="font-label-sm text-[10px] text-on-surface-variant uppercase" for="accessKey">PASSWORD</label>
                    <span class="font-label-sm text-[9px] text-outline">AES-256 BIT</span>
                </div>
                <div class="relative mechanical-field border border-outline-variant focus-within:border-secondary transition-all group/input">
                    <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within/input:text-secondary transition-colors text-lg">vpn_key</span>
                    <input autocomplete="current-password"
                           class="w-full bg-transparent border-none focus:ring-0 text-primary py-md pl-14 pr-14 font-body-md tracking-[0.5em] placeholder:tracking-normal placeholder:text-outline"
                           id="accessKey" name="password" placeholder="••••••••" type="password" required>
                    <button class="absolute right-md top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors" onclick="toggleVisibility()" type="button">
                        <span class="material-symbols-outlined text-lg" id="visibilityIcon">visibility</span>
                    </button>
                    <div class="absolute right-0 top-0 bottom-0 w-1 bg-outline-variant group-focus-within/input:bg-secondary transition-colors"></div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="pt-md grid grid-cols-1 md:grid-cols-2 gap-md">
                <button class="w-full py-md bg-secondary text-on-secondary font-label-sm text-label-sm uppercase tracking-widest hover:bg-white hover:text-[#020408] transition-all active:scale-[0.98] flex justify-center items-center group/btn" type="submit" id="submitBtn">
                    <span>Initiate Session</span>
                    <span class="material-symbols-outlined ml-sm text-lg group-hover/btn:translate-x-1 transition-transform">terminal</span>
                </button>
                <button class="w-full py-md border border-outline-variant text-on-surface-variant font-label-sm text-label-sm uppercase tracking-widest hover:border-primary hover:text-primary transition-all active:scale-[0.98] flex justify-center items-center" type="button">
                    <span>Recover Protocol</span>
                    <span class="material-symbols-outlined ml-sm text-lg">settings_backup_restore</span>
                </button>
            </div>
        </form>

        {{-- Status Footer --}}
        <div class="mt-xl pt-md border-t border-outline-variant/20 grid grid-cols-3 gap-md">
            <div class="flex flex-col">
                <span class="font-label-sm text-[8px] uppercase text-outline">Node</span>
                <span class="font-label-sm text-[9px] text-primary truncate">MA-COMMAND-01</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="font-label-sm text-[8px] uppercase text-outline">Encryption</span>
                <span class="font-label-sm text-[9px] text-primary">GCM-MOD-9</span>
            </div>
            <div class="flex flex-col items-end">
                <span class="font-label-sm text-[8px] uppercase text-outline">Security</span>
                <span class="font-label-sm text-[9px] text-secondary">MAX_SHIELD</span>
            </div>
        </div>
    </div>
</main>

{{-- FOOTER --}}
<footer class="fixed bottom-0 left-0 w-full flex justify-between items-center px-margin py-md border-t border-outline-variant z-40 bg-[#020408]/90 backdrop-blur-md">
    <div class="flex items-center space-x-md">
        <span class="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant">© {{ date('Y') }} MAD APE ENGINEERING</span>
        <div class="h-3 w-px bg-outline-variant"></div>
        <span class="font-label-sm text-[10px] text-green-500/80 uppercase">All Systems Nominal</span>
    </div>
    <div class="flex space-x-lg">
        <a class="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant hover:text-secondary transition-colors" href="#">Legal_Notice</a>
        <a class="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant hover:text-secondary transition-colors" href="#">Support_Ticket</a>
    </div>
</footer>

<script>
    function toggleVisibility() {
        const input = document.getElementById('accessKey');
        const icon = document.getElementById('visibilityIcon');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.innerText = input.type === 'password' ? 'visibility' : 'visibility_off';
    }

    // Removed the problematic JavaScript that prevented form submission.
    // The form will now submit naturally to the Laravel backend.

    document.addEventListener('mousemove', (e) => {
        const bg = document.getElementById('gridBg');
        if (!bg) return;
        const x = (e.clientX - window.innerWidth / 2) * 0.01;
        const y = (e.clientY - window.innerHeight / 2) * 0.01;
        bg.style.transform = `translate(${x}px, ${y}px)`;
    });
</script>
</body>
</html>
