<?php
/**
 * @var array $interview
 * @var array $capabilities
 * @var string $jitsi_domain
 * @var string $jitsi_app_name
 * @var array $jitsi_config
 * @var string $display_name
 * @var bool $can_load_jitsi
 */
$jobTitle = (string)($interview['job_title'] ?? 'Interview');
$candidateName = (string)($interview['candidate_name'] ?? 'Candidate');
$companyName = (string)($interview['company_name'] ?? 'Company');
$status = (string)($interview['status'] ?? 'scheduled');
$companyLogo = (string)($interview['company_logo'] ?? '');
?>

<div
    x-data="window.interviewRoom(<?= htmlspecialchars(json_encode([
        'interview_id' => (int)($interview['id'] ?? 0),
        'domain' => (string)$jitsi_domain,
        'jitsi_config' => $jitsi_config ?? [],
        'display_name' => (string)$display_name,
        'app_name' => (string)$jitsi_app_name,
        'capabilities' => $capabilities,
        'initial_status' => $status,
        'can_load_jitsi' => (bool)$can_load_jitsi,
        'company_name' => $companyName,
    ]), ENT_QUOTES) ?>)"
    x-init="init()"
    class="min-h-screen flex flex-col overflow-hidden bg-slate-950"
>
    <style>
        html, body { height: 100%; overflow: hidden; }
        #jitsi-container {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: calc(100vh - 150px);
            background: #020617;
        }
        #jitsi-container iframe {
            display: block;
            width: 100% !important;
            height: 100% !important;
            border: 0;
            background: #020617;
        }
        @media (max-width: 1023px) {
            html, body { overflow: auto; }
            #jitsi-container { min-height: 58vh; height: 58vh; }
        }
        @media (max-width: 640px) {
            #jitsi-container { min-height: 62vh; height: 62vh; }
        }
    </style>

    <header class="shrink-0 border-b border-white/10 bg-slate-950/95">
        <div class="max-w-[1600px] mx-auto px-3 sm:px-5 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center shrink-0 overflow-hidden">
                    <?php if ($companyLogo): ?>
                        <img src="<?= htmlspecialchars($companyLogo) ?>" alt="<?= htmlspecialchars($companyName) ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <span class="text-sm font-bold text-white/80"><?= htmlspecialchars(strtoupper(substr($companyName, 0, 1))) ?></span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div class="text-xs text-white/60 truncate"><?= htmlspecialchars($companyName) ?></div>
                    <div class="text-sm sm:text-base font-semibold text-white truncate"><?= htmlspecialchars($jobTitle) ?></div>
                    <div class="text-xs text-white/55 truncate"><?= htmlspecialchars($candidateName) ?></div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md border border-white/10 bg-white/5 text-xs text-white/70 capitalize" x-text="capabilities.role"></span>
                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md border border-white/10 bg-white/5 text-xs font-semibold">
                    <span class="h-2 w-2 rounded-full" :class="statusDotClass"></span>
                    <span x-text="statusLabel">Scheduled</span>
                </span>
            </div>
        </div>
    </header>

    <main class="flex-1 min-h-0">
        <div class="h-full grid grid-cols-1 lg:grid-cols-12">
            <section class="lg:col-span-9 min-h-0 relative">
                <div id="jitsi-container" class="h-full w-full"></div>

                <div x-show="!ready" x-cloak class="absolute inset-0 z-20 flex items-center justify-center p-4 bg-slate-950">
                    <div class="w-full max-w-lg rounded-lg border border-white/10 bg-slate-900 p-5 shadow-2xl">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-lg bg-cyan-500/15 border border-cyan-300/20 flex items-center justify-center text-cyan-100 font-bold">MI</div>
                            <div class="min-w-0">
                                <h1 class="text-lg font-semibold text-white">Interview room</h1>
                                <p class="mt-1 text-sm text-white/65" x-text="introText"></p>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            <div class="rounded-md border border-white/10 bg-white/[0.03] px-3 py-2">
                                <div class="text-white/45 text-xs">Camera</div>
                                <div class="text-white/85" x-text="deviceStatus.camera">Checking</div>
                            </div>
                            <div class="rounded-md border border-white/10 bg-white/[0.03] px-3 py-2">
                                <div class="text-white/45 text-xs">Microphone</div>
                                <div class="text-white/85" x-text="deviceStatus.microphone">Checking</div>
                            </div>
                            <div class="rounded-md border border-white/10 bg-white/[0.03] px-3 py-2">
                                <div class="text-white/45 text-xs">Connection</div>
                                <div class="text-white/85" x-text="deviceStatus.connection">Ready</div>
                            </div>
                            <div class="rounded-md border border-white/10 bg-white/[0.03] px-3 py-2">
                                <div class="text-white/45 text-xs">Screen share</div>
                                <div class="text-white/85" x-text="screenShareLabel">Checking</div>
                            </div>
                        </div>

                        <div x-show="blockingMessage" x-cloak class="mt-4 rounded-md border border-amber-300/20 bg-amber-500/10 px-3 py-2 text-sm text-amber-50" x-text="blockingMessage"></div>
                        <div x-show="errorMessage" x-cloak class="mt-4 rounded-md border border-red-300/25 bg-red-500/10 px-3 py-2 text-sm text-red-50" x-text="errorMessage"></div>

                        <div class="mt-5 flex flex-col sm:flex-row gap-2">
                            <button
                                x-show="canLoadJitsi"
                                @click="joinMeeting()"
                                :disabled="joining"
                                class="inline-flex justify-center items-center rounded-lg bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-400 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span x-text="joining ? 'Joining...' : joinButtonText">Join interview</span>
                            </button>
                            <button
                                x-show="!canLoadJitsi"
                                @click="refreshState()"
                                class="inline-flex justify-center items-center rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10"
                            >
                                Check status
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="lg:col-span-3 border-t lg:border-t-0 lg:border-l border-white/10 bg-slate-950 min-h-0">
                <div class="h-full flex flex-col">
                    <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
                        <div class="text-sm font-semibold text-white/90">Session</div>
                        <div class="text-xs text-white/60" x-text="timerText">00:00</div>
                    </div>
                    <div class="p-4 space-y-3 overflow-auto">
                        <div class="rounded-lg border border-white/10 bg-white/[0.03] p-3">
                            <div class="text-xs text-white/50">Participants</div>
                            <div class="mt-1 text-2xl font-semibold text-white" x-text="participantsCount">1</div>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/[0.03] p-3">
                            <div class="text-xs text-white/50">Media status</div>
                            <div class="mt-2 space-y-1 text-sm text-white/80">
                                <div>Mic: <span x-text="audioMuted ? 'Muted' : 'On'"></span></div>
                                <div>Camera: <span x-text="videoMuted ? 'Off' : 'On'"></span></div>
                                <div>Share: <span x-text="sharing ? 'Active' : 'Off'"></span></div>
                            </div>
                        </div>
                        <template x-if="capabilities.can_analytics">
                            <a :href="`/interviews/${interviewId}/analytics`" class="block rounded-lg border border-white/10 bg-white/[0.03] p-3 hover:bg-white/[0.06]">
                                <div class="text-sm font-semibold text-white">Interview analytics</div>
                                <div class="mt-1 text-xs text-white/55">Join, media, and room events</div>
                            </a>
                        </template>
                        <div class="rounded-lg border border-white/10 bg-white/[0.03] p-3 text-xs leading-5 text-white/55">
                            Camera and microphone require HTTPS on production domains. Localhost is allowed for development.
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <footer x-show="ready" x-cloak class="shrink-0 border-t border-white/10 bg-slate-950/95">
        <div class="max-w-[1600px] mx-auto px-3 sm:px-5 py-2.5 flex items-center justify-center gap-2 overflow-x-auto">
            <button @click="toggleAudio()" class="control-btn" :class="audioMuted ? 'control-off' : 'control-on'">
                <span x-text="audioMuted ? 'Unmute' : 'Mute'"></span>
            </button>
            <button @click="toggleVideo()" class="control-btn" :class="videoMuted ? 'control-off' : 'control-on'">
                <span x-text="videoMuted ? 'Camera on' : 'Camera off'"></span>
            </button>
            <button x-show="canScreenShare" @click="toggleShare()" class="control-btn control-neutral">
                <span x-text="sharing ? 'Stop share' : 'Share'"></span>
            </button>
            <button @click="toggleChat()" class="control-btn control-neutral">Chat</button>
            <button @click="toggleParticipants()" class="control-btn control-neutral">People</button>
            <button @click="toggleRaiseHand()" class="control-btn control-neutral">Raise hand</button>
            <button @click="toggleTileView()" class="control-btn control-neutral">Grid</button>
            <template x-if="capabilities.can_end">
                <button @click="endMeeting()" class="control-btn control-danger">End</button>
            </template>
            <template x-if="!capabilities.can_end">
                <button @click="leaveMeeting()" class="control-btn control-neutral">Leave</button>
            </template>
        </div>
    </footer>

    <div
        x-show="toast.visible"
        x-transition
        x-cloak
        class="fixed top-16 right-4 z-50 max-w-sm rounded-lg px-4 py-3 text-sm font-semibold shadow-lg"
        :class="toast.type === 'error' ? 'bg-red-600 text-white' : 'bg-slate-800 text-white border border-white/10'"
        x-text="toast.message"
    ></div>

    <style>
        .control-btn {
            white-space: nowrap;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,.12);
            padding: .55rem .8rem;
            font-size: .875rem;
            font-weight: 700;
            color: white;
        }
        .control-on { background: rgba(16,185,129,.18); border-color: rgba(110,231,183,.28); }
        .control-off { background: rgba(255,255,255,.06); color: rgba(255,255,255,.82); }
        .control-neutral { background: rgba(255,255,255,.08); }
        .control-neutral:hover, .control-off:hover { background: rgba(255,255,255,.13); }
        .control-danger { background: rgba(239,68,68,.2); border-color: rgba(252,165,165,.3); color: rgb(254,226,226); }
    </style>
</div>

<script>
window.interviewRoom = function (cfg) {
    const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);

    return {
        interviewId: cfg.interview_id,
        domain: String(cfg.domain || 'meet.jit.si').replace(/^https?:\/\//i, '').replace(/\/+$/, ''),
        jitsiConfig: cfg.jitsi_config || {},
        displayName: cfg.display_name || 'Participant',
        appName: cfg.app_name || 'Interview',
        capabilities: cfg.capabilities || {},
        status: cfg.initial_status || 'scheduled',
        canLoadJitsi: !!cfg.can_load_jitsi,
        api: null,
        roomName: null,
        roomPassword: null,
        ready: false,
        joining: false,
        joined: false,
        audioMuted: false,
        videoMuted: false,
        sharing: false,
        participantsCount: 1,
        timerText: '00:00',
        startedAtMs: null,
        pollTimer: null,
        timer: null,
        reconnectAttempts: 0,
        maxReconnectAttempts: 3,
        networkOffline: false,
        errorMessage: '',
        blockingMessage: '',
        deviceStatus: {
            camera: 'Not tested',
            microphone: 'Not tested',
            connection: 'Ready'
        },
        toast: { visible: false, message: '', type: 'info' },

        get statusLabel() {
            if (this.status === 'live') return 'Live';
            if (this.status === 'completed') return 'Completed';
            if (this.status === 'cancelled') return 'Cancelled';
            if (this.status === 'rescheduled') return 'Rescheduled';
            return 'Scheduled';
        },
        get statusDotClass() {
            if (this.status === 'live') return 'bg-emerald-400';
            if (this.status === 'completed') return 'bg-sky-400';
            if (this.status === 'cancelled') return 'bg-red-400';
            return 'bg-amber-400';
        },
        get introText() {
            if (!this.canUseMedia()) {
                return 'This browser context cannot access camera or microphone.';
            }
            if (!this.canLoadJitsi && this.capabilities.role === 'candidate') {
                return 'Waiting for the employer to start the meeting. This page checks automatically.';
            }
            return 'Click Join Interview to allow camera and microphone, then enter the live room.';
        },
        get joinButtonText() {
            if (this.capabilities.can_start && this.status !== 'live') return 'Start interview';
            return 'Join interview';
        },
        get canScreenShare() {
            return !!this.capabilities.can_screen_share && !!(navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia);
        },
        get screenShareLabel() {
            return this.canScreenShare ? 'Available' : 'Unavailable on this browser';
        },

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },
        showToast(message, type = 'info') {
            this.toast = { visible: true, message, type };
            window.setTimeout(() => this.toast.visible = false, 4500);
        },
        canUseMedia() {
            return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && (window.isSecureContext || isLocalhost));
        },
        isInsecureProductionOrigin() {
            return !(window.isSecureContext || isLocalhost);
        },
        debugLog(type, data = {}) {
            const payload = {
                type,
                data: {
                    ...data,
                    href: window.location.href,
                    secureContext: window.isSecureContext,
                    userAgent: navigator.userAgent,
                    platform: navigator.platform || '',
                    online: navigator.onLine,
                    jitsiDomain: this.domain,
                    role: this.capabilities.role || ''
                }
            };
            console.info('[InterviewRoom]', payload.type, payload.data);
            if (!this.interviewId) return;
            fetch(`/interviews/${this.interviewId}/events`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': this.csrf()
                },
                body: JSON.stringify(payload)
            }).catch(() => {});
        },

        init() {
            this.debugLog('room_client_init', {
                canLoadJitsi: this.canLoadJitsi,
                mediaSupported: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
                displayCaptureSupported: !!(navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia)
            });
            if (this.jitsiConfig.force_https && this.isInsecureProductionOrigin()) {
                window.location.href = window.location.href.replace(/^http:/i, 'https:');
                return;
            }
            this.refreshState();
            this.runDeviceInventory();
            window.addEventListener('online', () => this.handleNetworkOnline());
            window.addEventListener('offline', () => this.handleNetworkOffline());
            window.addEventListener('beforeunload', () => {
                try {
                    if (this.api && typeof this.api.dispose === 'function') this.api.dispose();
                } catch (_) {}
            });
            if (!this.canLoadJitsi) {
                this.pollTimer = window.setInterval(() => this.refreshState(), 3000);
            }
        },
        stopPolling() {
            if (this.pollTimer) {
                window.clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        async refreshState() {
            try {
                const res = await fetch(`/interviews/${this.interviewId}/state`, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (!data || !data.success) return;

                this.status = data.status || this.status;
                this.canLoadJitsi = !!data.can_join;
                this.roomName = data.room_name || this.roomName;
                this.roomPassword = data.room_password || this.roomPassword;

                if (this.canLoadJitsi) {
                    this.stopPolling();
                    this.blockingMessage = '';
                } else if (this.capabilities.role === 'candidate') {
                    this.blockingMessage = 'The meeting has not started yet. Keep this page open.';
                }
            } catch (_) {
                this.deviceStatus.connection = 'Network issue';
            }
        },

        async runDeviceInventory() {
            if (!this.canUseMedia()) {
                this.deviceStatus.camera = 'HTTPS required';
                this.deviceStatus.microphone = 'HTTPS required';
                this.blockingMessage = 'Use HTTPS in production. Camera and microphone APIs work only on HTTPS or localhost.';
                this.debugLog('media_insecure_origin', { protocol: window.location.protocol, host: window.location.host });
                return;
            }

            try {
                await this.readPermissionState();
                const devices = await navigator.mediaDevices.enumerateDevices();
                const cameras = devices.filter((d) => d.kind === 'videoinput').length;
                const mics = devices.filter((d) => d.kind === 'audioinput').length;
                this.deviceStatus.camera = cameras ? `${cameras} detected` : 'Permission needed';
                this.deviceStatus.microphone = mics ? `${mics} detected` : 'Permission needed';
            } catch (_) {
                this.deviceStatus.camera = 'Permission needed';
                this.deviceStatus.microphone = 'Permission needed';
            }
        },

        async readPermissionState() {
            if (!navigator.permissions || !navigator.permissions.query) return;
            try {
                const camera = await navigator.permissions.query({ name: 'camera' });
                const microphone = await navigator.permissions.query({ name: 'microphone' });
                this.debugLog('permission_state', {
                    camera: camera.state,
                    microphone: microphone.state
                });
            } catch (_) {
                this.debugLog('permission_state_unavailable');
            }
        },

        async runDeviceCheck() {
            this.errorMessage = '';
            if (!this.canUseMedia()) {
                this.errorMessage = 'Camera and microphone require HTTPS, except on localhost development URLs.';
                this.debugLog('media_preflight_failed', { reason: 'insecure_origin' });
                return false;
            }

            let stream = null;
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true },
                    video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }
                });
                this.deviceStatus.camera = stream.getVideoTracks().length ? 'Allowed' : 'No camera track';
                this.deviceStatus.microphone = stream.getAudioTracks().length ? 'Allowed' : 'No mic track';
                this.debugLog('media_preflight_success', {
                    audioTracks: stream.getAudioTracks().length,
                    videoTracks: stream.getVideoTracks().length
                });
                return true;
            } catch (error) {
                const name = error && error.name ? error.name : 'MediaError';
                if (name === 'NotAllowedError' || name === 'SecurityError') {
                    this.errorMessage = 'Camera or microphone permission was blocked. Open browser site settings, allow camera and microphone, then refresh.';
                    this.deviceStatus.camera = 'Blocked';
                    this.deviceStatus.microphone = 'Blocked';
                } else if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
                    this.errorMessage = 'No usable camera or microphone was found on this device.';
                    this.deviceStatus.camera = 'Not found';
                    this.deviceStatus.microphone = 'Not found';
                } else if (name === 'NotReadableError' || name === 'TrackStartError') {
                    this.errorMessage = 'Camera or microphone is already in use by another app. Close other meeting apps and retry.';
                    this.deviceStatus.camera = 'Busy';
                    this.deviceStatus.microphone = 'Busy';
                } else {
                    this.errorMessage = `Could not start media devices (${name}). Try another browser or refresh.`;
                }
                this.debugLog('media_preflight_failed', { name, message: error && error.message ? error.message : '' });
                return false;
            } finally {
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                }
            }
        },

        async joinMeeting() {
            if (this.joining || this.api) return;
            this.joining = true;
            this.errorMessage = '';
            this.debugLog('join_clicked');

            try {
                const devicesOk = await this.runDeviceCheck();
                if (!devicesOk) return;

                if (this.capabilities.can_start) {
                    await this.ensureMeetingStarted();
                } else {
                    await this.refreshState();
                }

                if (!this.roomName) {
                    this.errorMessage = 'Meeting room is not ready yet. Ask the employer to start the interview.';
                    return;
                }

                await this.loadExternalApi();
                this.createJitsiConference();
            } catch (error) {
                this.errorMessage = error && error.message ? error.message : 'Failed to start meeting. Please refresh the page.';
                this.debugLog('join_failed', { message: this.errorMessage });
            } finally {
                this.joining = false;
            }
        },

        async ensureMeetingStarted() {
            if (!this.capabilities.can_start) return;

            const res = await fetch(`/interviews/${this.interviewId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': this.csrf()
                },
                body: JSON.stringify({})
            });
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) {
                throw new Error((data && (data.error || data.message)) || 'Failed to start meeting. Please refresh the page.');
            }

            this.roomName = data.room_name;
            this.roomPassword = data.room_password || null;
            this.status = 'live';
            this.canLoadJitsi = true;
            this.debugLog('meeting_started_from_client', { hasRoomName: !!this.roomName });
        },

        loadExternalApi() {
            return new Promise((resolve, reject) => {
                if (window.JitsiMeetExternalAPI) {
                    resolve();
                    return;
                }

                const script = document.createElement('script');
                script.src = `https://${this.domain}/external_api.js`;
                script.async = true;
                script.crossOrigin = 'anonymous';

                const timeout = window.setTimeout(() => {
                    script.remove();
                    reject(new Error(`Could not load Jitsi from ${this.domain}. Check internet, firewall, CSP, or JITSI_DOMAIN.`));
                }, 25000);

                script.onload = () => {
                    window.clearTimeout(timeout);
                    if (window.JitsiMeetExternalAPI) {
                        resolve();
                    } else {
                        reject(new Error('Jitsi API loaded but did not initialize.'));
                    }
                };
                script.onerror = () => {
                    window.clearTimeout(timeout);
                    script.remove();
                    reject(new Error(`Failed to load Jitsi API from ${this.domain}.`));
                };
                document.head.appendChild(script);
            });
        },

        createJitsiConference() {
            const parentNode = document.getElementById('jitsi-container');
            if (!parentNode) throw new Error('Video container is missing.');
            if (this.api && typeof this.api.dispose === 'function') {
                try { this.api.dispose(); } catch (_) {}
            }
            this.api = null;
            parentNode.innerHTML = '';
            const iceServers = Array.isArray(this.jitsiConfig.ice_servers) ? this.jitsiConfig.ice_servers : [];
            const p2pStunServers = iceServers
                .map((server) => Array.isArray(server.urls) ? server.urls[0] : server.urls)
                .filter((url) => typeof url === 'string' && url.startsWith('stun:'));

            const configOverwrite = {
                prejoinPageEnabled: false,
                disableDeepLinking: true,
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                startSilent: false,
                enableNoisyMicDetection: true,
                disableModeratorIndicator: false,
                enableClosePage: false,
                disableThirdPartyRequests: false,
                enableIceRestart: true,
                channelLastN: -1,
                constraints: {
                    video: {
                        height: { ideal: 720, max: 1080, min: 240 },
                        width: { ideal: 1280, max: 1920, min: 320 }
                    }
                },
                desktopSharingFrameRate: { min: 5, max: 30 },
                p2p: {
                    enabled: true,
                    stunServers: p2pStunServers.length ? p2pStunServers : ['stun:stun.l.google.com:19302', 'stun:stun1.l.google.com:19302']
                },
                testing: {
                    enableFirefoxSimulcast: true
                },
                toolbarConfig: { alwaysVisible: false }
            };

            if (iceServers.length) {
                configOverwrite.iceServers = iceServers;
            }

            this.api = new JitsiMeetExternalAPI(this.domain, {
                roomName: this.roomName,
                parentNode,
                width: '100%',
                height: '100%',
                userInfo: { displayName: this.displayName },
                configOverwrite,
                interfaceConfigOverwrite: {
                    APP_NAME: this.appName,
                    NATIVE_APP_NAME: this.appName,
                    PROVIDER_NAME: cfg.company_name || this.appName,
                    DEFAULT_REMOTE_DISPLAY_NAME: 'Participant',
                    DEFAULT_LOCAL_DISPLAY_NAME: 'You',
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    SHOW_BRAND_WATERMARK: false,
                    MOBILE_APP_PROMO: false,
                    DISABLE_JOIN_LEAVE_NOTIFICATIONS: false,
                    DISABLE_VIDEO_BACKGROUND: false,
                    FILM_STRIP_MAX_HEIGHT: 130,
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                        'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                        'videoquality', 'filmstrip', 'tileview', 'participants-pane',
                        'stats', 'shortcuts'
                    ]
                }
            });

            this.hardenIframePermissions(parentNode);
            this.bindApiEvents();
            this.debugLog('jitsi_api_created', {
                roomName: this.roomName,
                hasIceServers: iceServers.length > 0,
                publicMeetJitsi: !!this.jitsiConfig.is_public_meet_jitsi
            });
            this.ready = true;
            this.startedAtMs = Date.now();
            this.startTimer();
        },

        hardenIframePermissions(parentNode) {
            const applyPermissions = () => {
                const iframe = parentNode.querySelector('iframe');
                if (!iframe) return false;
                iframe.setAttribute('allow', [
                    'camera',
                    'microphone',
                    'display-capture',
                    'fullscreen',
                    'autoplay',
                    'clipboard-read',
                    'clipboard-write'
                ].join('; '));
                iframe.setAttribute('allowfullscreen', 'true');
                iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
                iframe.removeAttribute('sandbox');
                return true;
            };

            if (applyPermissions()) return;

            const observer = new MutationObserver(() => {
                if (applyPermissions()) {
                    observer.disconnect();
                }
            });
            observer.observe(parentNode, { childList: true, subtree: true });
            window.setTimeout(() => observer.disconnect(), 10000);
        },

        bindApiEvents() {
            if (!this.api) return;

            const postEvent = async (type, data = {}) => {
                try {
                    await fetch(`/interviews/${this.interviewId}/events`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-Token': this.csrf()
                        },
                        body: JSON.stringify({ type, data })
                    });
                } catch (_) {}
            };

            this.api.addEventListener('videoConferenceJoined', (event) => {
                this.joined = true;
                this.reconnectAttempts = 0;
                this.updateParticipantCount();
                this.showToast('Joined the interview');
                this.debugLog('video_conference_joined', event || {});
                postEvent('user_joined', event || {});
            });

            this.api.addEventListener('videoConferenceLeft', (event) => {
                this.joined = false;
                this.debugLog('video_conference_left', event || {});
                postEvent('user_left', event || {});
            });

            this.api.addEventListener('participantJoined', (event) => {
                this.updateParticipantCount();
                this.debugLog('participant_joined_client', event || {});
                postEvent('participant_joined', event || {});
            });

            this.api.addEventListener('participantLeft', (event) => {
                this.updateParticipantCount();
                this.debugLog('participant_left_client', event || {});
                postEvent('participant_left', event || {});
            });

            this.api.addEventListener('audioMuteStatusChanged', (event) => {
                this.audioMuted = !!event.muted;
                postEvent('audio_mute_changed', event || {});
            });

            this.api.addEventListener('videoMuteStatusChanged', (event) => {
                this.videoMuted = !!event.muted;
                postEvent('video_mute_changed', event || {});
            });

            this.api.addEventListener('screenSharingStatusChanged', (event) => {
                this.sharing = !!event.on;
                this.debugLog('screen_share_changed', event || {});
                postEvent(this.sharing ? 'screen_share_started' : 'screen_share_stopped', event || {});
            });

            this.api.addEventListener('cameraError', (event) => {
                this.videoMuted = true;
                this.showMediaError('Camera', event);
                postEvent('camera_error', event || {});
            });

            this.api.addEventListener('micError', (event) => {
                this.audioMuted = true;
                this.showMediaError('Microphone', event);
                postEvent('microphone_error', event || {});
            });

            this.api.addEventListener('errorOccurred', (event) => {
                const message = event && event.message ? event.message : 'Video conference error. Refresh and try again.';
                this.showToast(message, 'error');
                this.debugLog('jitsi_error_occurred', event || {});
                postEvent('jitsi_error', event || {});
            });

            this.api.addEventListener('connectionInterrupted', (event) => {
                this.deviceStatus.connection = 'Reconnecting';
                this.showToast('Connection interrupted. Reconnecting...', 'error');
                this.debugLog('jitsi_connection_interrupted', event || {});
            });

            this.api.addEventListener('connectionRestored', (event) => {
                this.deviceStatus.connection = 'Connected';
                this.reconnectAttempts = 0;
                this.showToast('Connection restored');
                this.debugLog('jitsi_connection_restored', event || {});
            });

            this.api.addEventListener('connectionFailed', (event) => {
                this.deviceStatus.connection = 'Failed';
                this.debugLog('jitsi_connection_failed', event || {});
                this.scheduleReconnect('connection_failed');
            });

            this.api.addEventListener('browserSupport', (event) => {
                this.debugLog('jitsi_browser_support', event || {});
            });

            this.api.addEventListener('videoAvailabilityChanged', (event) => {
                this.debugLog('video_availability_changed', event || {});
            });

            this.api.addEventListener('audioAvailabilityChanged', (event) => {
                this.debugLog('audio_availability_changed', event || {});
            });

            this.api.addEventListener('passwordRequired', () => {
                if (!this.roomPassword || !this.api) {
                    this.showToast('The room is asking for a password. Please ask the interviewer to restart the meeting.', 'error');
                    return;
                }
                try {
                    this.api.executeCommand('password', this.roomPassword);
                } catch (_) {
                    this.showToast('Could not submit room password. Refresh and try again.', 'error');
                }
            });

            this.api.addEventListener('readyToClose', () => {
                postEvent('meeting_ready_to_close', {});
                this.cleanupTimers();
                this.redirectAfterLeave();
            });
        },

        showMediaError(label, event) {
            const type = event && (event.type || event.name || event.errorType) ? (event.type || event.name || event.errorType) : 'unknown';
            let message = `${label} failed (${type}). `;
            if (type === 'permission' || type === 'NotAllowedError') {
                message += 'Allow access from the browser address-bar site settings, then refresh.';
            } else if (type === 'notFound' || type === 'NotFoundError') {
                message += `No ${label.toLowerCase()} device was found.`;
            } else if (type === 'NotReadableError' || type === 'TrackStartError') {
                message += `${label} is already used by another app.`;
            } else {
                message += 'Check browser permissions and device settings.';
            }
            this.showToast(message, 'error');
        },

        updateParticipantCount() {
            if (!this.api) {
                this.participantsCount = 1;
                return;
            }
            window.setTimeout(async () => {
                try {
                    if (this.api.getParticipantsInfo) {
                        const participants = await this.api.getParticipantsInfo();
                        this.participantsCount = Math.max(1, Array.isArray(participants) ? participants.length : 1);
                    } else if (this.api.getNumberOfParticipants) {
                        this.participantsCount = Math.max(1, this.api.getNumberOfParticipants());
                    }
                } catch (_) {
                    this.participantsCount = Math.max(1, this.participantsCount);
                }
            }, 300);
        },

        scheduleReconnect(reason) {
            if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                this.showToast('Unable to reconnect. Please refresh the interview room.', 'error');
                this.debugLog('jitsi_reconnect_gave_up', { reason, attempts: this.reconnectAttempts });
                return;
            }
            this.reconnectAttempts += 1;
            this.showToast(`Reconnecting (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`, 'error');
            this.debugLog('jitsi_reconnect_scheduled', { reason, attempts: this.reconnectAttempts });
            window.setTimeout(() => {
                if (!this.roomName || this.networkOffline) return;
                try {
                    if (this.api && typeof this.api.dispose === 'function') this.api.dispose();
                } catch (_) {}
                this.ready = false;
                this.joined = false;
                this.createJitsiConference();
            }, 1500 * this.reconnectAttempts);
        },

        handleNetworkOffline() {
            this.networkOffline = true;
            this.deviceStatus.connection = 'Offline';
            this.showToast('You are offline. The room will reconnect when network returns.', 'error');
            this.debugLog('browser_offline');
        },

        handleNetworkOnline() {
            this.networkOffline = false;
            this.deviceStatus.connection = 'Reconnecting';
            this.debugLog('browser_online');
            if (this.roomName && this.ready) {
                this.scheduleReconnect('browser_online');
            }
        },

        toggleAudio() {
            if (this.api) this.api.executeCommand('toggleAudio');
        },
        toggleVideo() {
            if (this.api) this.api.executeCommand('toggleVideo');
        },
        toggleShare() {
            if (!this.api) return;
            if (!this.canScreenShare) {
                this.showToast('Screen sharing is not supported by this browser.', 'error');
                return;
            }
            this.api.executeCommand('toggleShareScreen');
        },
        toggleChat() {
            if (this.api) this.api.executeCommand('toggleChat');
        },
        toggleParticipants() {
            if (this.api) this.api.executeCommand('toggleParticipantsPane', true);
        },
        toggleRaiseHand() {
            if (this.api) this.api.executeCommand('toggleRaiseHand');
        },
        toggleTileView() {
            if (this.api) this.api.executeCommand('toggleTileView');
        },

        async endMeeting() {
            if (!this.capabilities.can_end) return;
            try {
                if (this.api) this.api.executeCommand('hangup');
                await fetch(`/interviews/${this.interviewId}/end`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': this.csrf()
                    },
                    body: JSON.stringify({})
                });
            } catch (_) {
            } finally {
                this.redirectAfterLeave();
            }
        },

        leaveMeeting() {
            try {
                if (this.api) this.api.executeCommand('hangup');
            } finally {
                this.redirectAfterLeave();
            }
        },

        redirectAfterLeave() {
            const role = this.capabilities.role;
            if (role === 'employer') {
                window.location.href = '/employer/interviews';
            } else if (role === 'candidate') {
                window.location.href = '/candidate/interviews';
            } else {
                window.location.href = '/admin/interviews';
            }
        },

        startTimer() {
            this.cleanupTimers();
            this.timer = window.setInterval(() => {
                const diff = Math.max(0, Date.now() - (this.startedAtMs || Date.now()));
                const sec = Math.floor(diff / 1000);
                const min = Math.floor(sec / 60);
                this.timerText = `${String(min).padStart(2, '0')}:${String(sec % 60).padStart(2, '0')}`;
            }, 1000);
        },
        cleanupTimers() {
            if (this.timer) window.clearInterval(this.timer);
            if (this.pollTimer) window.clearInterval(this.pollTimer);
            this.timer = null;
            this.pollTimer = null;
        }
    };
};
</script>
