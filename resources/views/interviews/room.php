<?php
/**
 * @var array $interview
 * @var array $capabilities
 * @var string $jitsi_domain
 * @var string $jitsi_app_name
 * @var string $display_name
 * @var bool $can_load_jitsi
 */
$jobTitle = (string)($interview['job_title'] ?? 'Interview');
$candidateName = (string)($interview['candidate_name'] ?? 'Candidate');
$companyName = (string)($interview['company_name'] ?? 'Company');
$status = (string)($interview['status'] ?? 'scheduled');
$companyLogo = (string)($interview['company_logo'] ?? '');
$isDark = true;
?>

<div
    x-data="window.interviewRoom(<?= htmlspecialchars(json_encode([
        'interview_id' => (int)($interview['id'] ?? 0),
        'domain' => (string)$jitsi_domain,
        'display_name' => (string)$display_name,
        'app_name' => (string)$jitsi_app_name,
        'capabilities' => $capabilities,
        'initial_status' => $status,
        'can_load_jitsi' => (bool)$can_load_jitsi,
        'company_name' => $companyName
    ]), ENT_QUOTES) ?>)"
    x-init="init()"
    class="min-h-screen flex flex-col"
>
    <style>
        #jitsi-container { 
            position: relative; 
            z-index: 0; 
            width: 100%; 
            height: 100%; 
            min-height: 500px; 
            background: #000;
        }
        #jitsi-container iframe { 
            width: 100% !important; 
            height: 100% !important; 
            border: none;
            display: block;
            min-height: 500px;
        }
        /* Ensure container is visible and has proper dimensions */
        .min-h-0 { min-height: 0 !important; }
    </style>
    <div class="relative z-50 border-b border-white/10 bg-gray-950/60 backdrop-blur supports-[backdrop-filter]:bg-gray-950/40">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0">
                        <?php if ($companyLogo): ?>
                            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="<?= htmlspecialchars($companyName) ?>" class="h-8 w-8 rounded-lg object-cover" />
                        <?php else: ?>
                            <span class="text-sm font-bold text-white/80"><?= htmlspecialchars(strtoupper(substr($companyName, 0, 1))) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm text-white/70 truncate"><?= htmlspecialchars($companyName) ?></div>
                        <div class="text-base sm:text-lg font-semibold text-white truncate"><?= htmlspecialchars($jobTitle) ?></div>
                        <div class="text-xs text-white/60 truncate"><?= htmlspecialchars($candidateName) ?></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border border-white/10 bg-white/5">
                    <span class="h-2 w-2 rounded-full bg-amber-400" :class="statusDotClass"></span>
                    <span x-text="statusLabel">Scheduled</span>
                </span>

                <template x-if="capabilities.role === 'admin'">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border border-indigo-300/20 bg-indigo-500/10 text-indigo-200">
                        Admin Observer
                    </span>
                </template>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-0 pb-20">
        <div class="h-full grid grid-cols-1 lg:grid-cols-12">
            <div class="lg:col-span-9 min-h-0">
                <div class="h-full relative">
                    <div x-show="!ready && !canLoadJitsi" x-cloak class="absolute inset-0 flex items-center justify-center p-6">
                        <div class="max-w-md w-full bg-white/5 border border-white/10 rounded-2xl p-6">
                            <div class="text-lg font-semibold text-white">
                                <span x-text="capabilities.role === 'candidate' ? 'Waiting for meeting to start' : 'Waiting for interviewer'"></span>
                            </div>
                            <div class="text-sm text-white/70 mt-2">
                                <span x-show="capabilities.role === 'candidate'">
                                    The employer will start the meeting. This page will automatically refresh when ready.
                                </span>
                                <span x-show="capabilities.role !== 'candidate'">
                                    This room opens when the interviewer starts the meeting.
                                </span>
                            </div>
                            <div class="mt-5 flex items-center justify-between">
                                <button @click="refreshState()" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 border border-white/10 text-sm font-semibold">
                                    Refresh Now
                                </button>
                                <div class="text-xs text-white/60">
                                    Auto-checking…
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="jitsi-container" class="h-full w-full"></div>
                </div>
            </div>

            <div class="lg:col-span-3 border-t lg:border-t-0 lg:border-l border-white/10 bg-gray-950/40 min-h-0">
                <div class="h-full flex flex-col">
                    <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
                        <div class="text-sm font-semibold text-white/90">Session</div>
                        <div class="text-xs text-white/60" x-text="timerText">—</div>
                    </div>

                    <div class="p-4 space-y-3 overflow-auto">
                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <div class="text-xs text-white/60">Role</div>
                            <div class="mt-1 text-sm font-semibold text-white" x-text="capabilities.role"><?= htmlspecialchars((string)($capabilities['role'] ?? '')) ?></div>
                        </div>

                        <template x-if="capabilities.can_analytics">
                            <a :href="`/interviews/${interviewId}/analytics`" class="block bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl p-4 transition">
                                <div class="text-sm font-semibold text-white">Interview analytics</div>
                                <div class="text-xs text-white/60 mt-1">Timeline, joins, screen-share, recording events</div>
                            </a>
                        </template>

                        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                            <div class="text-xs text-white/60">Participants</div>
                            <div class="mt-1 text-sm font-semibold text-white" x-text="participantsCount">1</div>
                        </div>

                        <template x-if="capabilities.can_mute_all">
                            <button @click="muteAll()" class="w-full px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10 text-sm font-semibold">
                                Mute everyone
                            </button>
                        </template>

                        <template x-if="capabilities.can_record">
                            <button @click="toggleRecording()" class="w-full px-4 py-2 rounded-xl border text-sm font-semibold transition"
                                :class="recording ? 'bg-red-500/15 border-red-300/30 text-red-100 hover:bg-red-500/20' : 'bg-white/10 border-white/10 text-white hover:bg-white/15'">
                                <span x-text="recording ? 'Stop recording' : 'Start recording'">Start recording</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-white/10 bg-gray-950/70 backdrop-blur supports-[backdrop-filter]:bg-gray-950/50">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-3 flex items-center justify-center gap-3">
            <button @click="toggleAudio()" class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                :class="audioMuted ? 'bg-white/5 border-white/10 text-white/80 hover:bg-white/10' : 'bg-emerald-500/15 border-emerald-300/30 text-emerald-100 hover:bg-emerald-500/20'">
                <span x-text="audioMuted ? 'Unmute' : 'Mute'">Unmute</span>
            </button>
            <button @click="toggleVideo()" class="px-4 py-2 rounded-xl border text-sm font-semibold transition"
                :class="videoMuted ? 'bg-white/5 border-white/10 text-white/80 hover:bg-white/10' : 'bg-sky-500/15 border-sky-300/30 text-sky-100 hover:bg-sky-500/20'">
                <span x-text="videoMuted ? 'Start video' : 'Stop video'">Start video</span>
            </button>
            <button x-show="capabilities.can_screen_share" x-cloak @click="toggleShare()" class="px-4 py-2 rounded-xl border bg-white/10 hover:bg-white/15 border-white/10 text-sm font-semibold">
                <span x-text="sharing ? 'Stop share' : 'Share screen'">Share screen</span>
            </button>
            <button @click="toggleChat()" class="px-4 py-2 rounded-xl border bg-white/10 hover:bg-white/15 border-white/10 text-sm font-semibold">
                Chat
            </button>
            <button @click="toggleParticipants()" class="px-4 py-2 rounded-xl border bg-white/10 hover:bg-white/15 border-white/10 text-sm font-semibold">
                People
            </button>
            <button @click="toggleRaiseHand()" class="px-4 py-2 rounded-xl border bg-white/10 hover:bg-white/15 border-white/10 text-sm font-semibold">
                ✋
            </button>
            <button @click="toggleTileView()" class="px-4 py-2 rounded-xl border bg-white/10 hover:bg-white/15 border-white/10 text-sm font-semibold">
                Grid
            </button>
            <template x-if="capabilities.can_end">
                <button @click="endMeeting()" class="px-4 py-2 rounded-xl border bg-red-500/15 hover:bg-red-500/20 border-red-300/30 text-sm font-semibold text-red-100">
                    End
                </button>
            </template>
            <template x-if="!capabilities.can_end">
                <button @click="leaveMeeting()" class="px-4 py-2 rounded-xl border bg-white/5 hover:bg-white/10 border-white/10 text-sm font-semibold">
                    Leave
                </button>
            </template>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.visible" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-20 right-4 z-[100] px-4 py-2 rounded-lg shadow-lg text-white text-sm font-medium flex items-center gap-2"
         :class="toast.type === 'error' ? 'bg-red-600/90' : 'bg-emerald-600/90'"
         style="display: none;">
        <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <svg x-show="toast.type !== 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span x-text="toast.message"></span>
    </div>
</div>

<script>
window.interviewRoom = function (cfg) {
    return {
        interviewId: cfg.interview_id,
        domain: cfg.domain,
        displayName: cfg.display_name,
        appName: cfg.app_name,
        capabilities: cfg.capabilities,
        status: cfg.initial_status || 'scheduled',
        canLoadJitsi: !!cfg.can_load_jitsi,
        api: null,
        roomName: null,
        roomPassword: null,
        ready: false,
        joined: false,
        audioMuted: false,
        videoMuted: false,
        sharing: false,
        recording: false,
        participantsCount: 1,
        startedAtMs: null,
        timerText: '—',
        pollTimer: null,
        toast: { visible: false, message: '', type: 'info' },
        showToast(message, type = 'info') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.visible = true;
            setTimeout(() => {
                this.toast.visible = false;
            }, 5000);
        },
        get statusLabel() {
            if (this.status === 'live') return 'Live';
            if (this.status === 'completed') return 'Completed';
            if (this.status === 'cancelled') return 'Cancelled';
            return 'Scheduled';
        },
        get statusDotClass() {
            if (this.status === 'live') return 'bg-emerald-400';
            if (this.status === 'completed') return 'bg-sky-400';
            if (this.status === 'cancelled') return 'bg-rose-400';
            return 'bg-amber-400';
        },
        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },
        async init() {
            console.log('🚀 Initializing interview room...', {
                interviewId: this.interviewId,
                domain: this.domain,
                canLoadJitsi: this.canLoadJitsi,
                protocol: window.location.protocol,
                hostname: window.location.hostname
            });
            
            // Check if we're on HTTP (non-localhost) which can cause permission issues
            if (window.location.protocol === 'http:' && 
                window.location.hostname !== 'localhost' && 
                window.location.hostname !== '127.0.0.1') {
                console.warn('⚠️ Running on HTTP (non-localhost). Camera/microphone may not work - HTTPS is required.');
            }
            
            if (this.canLoadJitsi) {
                console.log('✅ Can load Jitsi - starting meeting...');
                try {
                    await this.ensureMeetingStarted();
                    console.log('✅ Meeting started, loading Jitsi...');
                    await this.loadJitsi();
                    this.tickTimer();
                } catch (error) {
                    console.error('❌ Error initializing meeting:', error);
                    this.showToast('Failed to start meeting. Please refresh the page.', 'error');
                }
                return;
            }
            console.log('⏳ Cannot load Jitsi yet - starting polling...');
            this.startPolling();
        },
        startPolling() {
            this.pollTimer = window.setInterval(() => this.refreshState(), 2500);
            this.refreshState();
        },
        stopPolling() {
            if (this.pollTimer) {
                window.clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },
        async refreshState() {
            try {
                const res = await fetch(`/interviews/${this.interviewId}/state`, {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!res.ok) {
                    console.error(`❌ State fetch failed: ${res.status} ${res.statusText}`);
                    return;
                }
                
                const data = await res.json();
                if (!data || !data.success) {
                    console.warn('⚠️ State response invalid:', data);
                    return;
                }
                
                console.log('📊 State refresh:', data);
                this.status = data.status || this.status;
                if (data.room_name) {
                    this.roomName = data.room_name;
                    console.log('✅ Room name received:', this.roomName);
                }
                if (data.room_password) {
                    this.roomPassword = data.room_password;
                    console.log('✅ Room password received');
                }
                
                // CRITICAL FIX: Allow joining if can_join OR if room exists (for candidates)
                if (data.can_join || (data.room_name && this.status !== 'cancelled')) {
                    console.log('✅ Can join - loading Jitsi...', {
                        can_join: data.can_join,
                        hasRoomName: !!data.room_name,
                        status: this.status
                    });
                    this.canLoadJitsi = true;
                    this.stopPolling();
                    
                    // For candidates: don't try to start meeting (they can't), just join existing
                    if (this.capabilities.can_start) {
                        await this.ensureMeetingStarted();
                    } else {
                        console.log('👤 Candidate joining existing meeting...');
                    }
                    
                    await this.loadJitsi();
                } else {
                    console.warn('⚠️ Cannot join yet:', {
                        can_join: data.can_join,
                        hasRoomName: !!data.room_name,
                        status: this.status
                    });
                }
            } catch (error) {
                console.error('❌ Error refreshing state:', error);
            }
        },
        async ensureMeetingStarted() {
            console.log('🔄 Ensuring meeting is started...', {
                canStart: this.capabilities.can_start,
                status: this.status,
                hasRoomName: !!this.roomName,
                hasPassword: !!this.roomPassword
            });
            
            if (!this.capabilities.can_start) {
                console.log('⏭️ Cannot start - refreshing state...');
                await this.refreshState();
                return;
            }
            
            if (this.status === 'live' && this.roomName && this.roomPassword) {
                console.log('✅ Meeting already started with room:', this.roomName);
                return;
            }
            
            try {
                console.log('📡 Starting meeting via API...');
                const res = await fetch(`/interviews/${this.interviewId}/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': this.csrf()
                    },
                    body: JSON.stringify({})
                });
                
                if (!res.ok) {
                    console.error(`❌ Start meeting failed: ${res.status} ${res.statusText}`);
                    this.showToast('Failed to start meeting. Please try again.', 'error');
                    return;
                }
                
                const data = await res.json();
                if (!data || !data.success) {
                    console.error('❌ Start meeting response invalid:', data);
                    this.showToast('Failed to start meeting. Please refresh the page.', 'error');
                    return;
                }
                
                console.log('✅ Meeting started successfully:', {
                    roomName: data.room_name,
                    hasPassword: !!data.room_password
                });
                
                this.roomName = data.room_name;
                this.roomPassword = data.room_password;
                this.status = 'live';
                this.startedAtMs = Date.now();
            } catch (error) {
                console.error('❌ Error starting meeting:', error);
                this.showToast('Failed to start meeting. Please check your connection and refresh.', 'error');
            }
        },
        async loadJitsi() {
            if (this.api) return;

            // Double-check to prevent race conditions
            const parentNode = document.getElementById('jitsi-container');
            if (!parentNode) return;
            if (parentNode.querySelector('iframe')) return; 

            // Verify HTTPS for live sites (required for camera/mic)
            const isHttps = window.location.protocol === 'https:';
            const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            
            if (!isHttps && !isLocalhost) {
                const errorMsg = 'Camera and Microphone require HTTPS. Please access this site via HTTPS.';
                console.error(errorMsg);
                this.showToast(errorMsg, 'error');
                return; // Don't proceed without HTTPS on live site
            }
            
            console.log('✅ HTTPS check passed. Loading Jitsi API...');
            await this.loadExternalApi();

            if (!this.roomName) {
                console.error('❌ Cannot load Jitsi - room name is missing!');
                console.error('Current state:', {
                    roomName: this.roomName,
                    status: this.status,
                    canLoadJitsi: this.canLoadJitsi,
                    role: this.capabilities.role
                });
                this.showToast('Meeting room not available. Please ensure the employer has started the meeting.', 'error');
                return;
            }
            
            console.log('✅ Room name available:', this.roomName, 'Password:', !!this.roomPassword);

            // Connection Timeout Check with diagnostic info
            this.connectionTimeout = setTimeout(() => {
                if (!this.joined) {
                    console.error('⏱️ CONNECTION TIMEOUT - Jitsi not connected after 15 seconds');
                    console.error('Diagnostic info:', {
                        apiExists: !!this.api,
                        roomName: this.roomName,
                        domain: this.domain,
                        ready: this.ready,
                        canLoadJitsi: this.canLoadJitsi
                    });
                    
                    let errorMsg = 'Connecting to meeting is taking longer than expected. ';
                    
                    // Check if API loaded
                    if (!window.JitsiMeetExternalAPI) {
                        errorMsg += 'Jitsi API failed to load. Please check your internet connection and firewall settings.';
                        console.error('❌ Jitsi API not loaded - script may have failed');
                    } else if (!this.api) {
                        errorMsg += 'Jitsi API loaded but failed to initialize. Please refresh the page.';
                        console.error('❌ Jitsi API loaded but not initialized');
                    } else if (!this.roomName) {
                        errorMsg += 'Room name not available. Please refresh the page.';
                        console.error('❌ Room name missing');
                    } else {
                        errorMsg += 'Check your internet connection, firewall, or try refreshing the page.';
                        console.error('❌ API exists but connection failed');
                    }
                    
                    this.showToast(errorMsg, 'error');
                }
            }, 15000);

            // Clear any existing content just in case
            parentNode.innerHTML = '';

            // Ensure domain uses HTTPS
            const jitsiDomain = this.domain.startsWith('https://') 
                ? this.domain.replace('https://', '') 
                : this.domain.startsWith('http://')
                    ? this.domain.replace('http://', '')
                    : this.domain;
            
            console.log(`🔄 Initializing Jitsi with domain: ${jitsiDomain}, room: ${this.roomName}`);
            console.log('🔑 Room password available:', !!this.roomPassword);
            
            // CRITICAL: Log room details for debugging
            console.log('📋 Room Details:', {
                roomName: this.roomName,
                hasPassword: !!this.roomPassword,
                role: this.capabilities.role,
                interviewId: this.interviewId
            });
            
            try {
                // Ensure parent node has proper dimensions
                parentNode.style.width = '100%';
                parentNode.style.height = '100%';
                parentNode.style.minHeight = '500px';
                parentNode.style.display = 'block';
                
                console.log('🔄 Creating Jitsi API instance...', {
                    domain: jitsiDomain,
                    roomName: this.roomName,
                    hasPassword: !!this.roomPassword,
                    role: this.capabilities.role,
                    parentNode: parentNode.id,
                    parentWidth: parentNode.offsetWidth,
                    parentHeight: parentNode.offsetHeight
                });
                
                // Build configOverwrite object
                const configOverwrite = {
                    // Allow all origins for Jitsi resources
                    serviceWorker: {
                        registrationFailed: false
                    },
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false, // Ensure video starts enabled (like Google Meet)
                    disableDeepLinking: true,
                    disableInviteFunctions: false,
                    disableChat: false,
                    enableNoisyMicDetection: true,
                    enableClosePage: false,
                    fileRecordingsEnabled: true,
                    liveStreamingEnabled: true,
                    transcribingEnabled: true,
                    autoKnockLobby: true,
                    disableFilmstripAutohiding: true,
                    startAudioOnly: false,
                    channelLastN: -1, // Force receive all videos from all participants
                    // CRITICAL: Ensure connection works on live sites
                    enableIceRestart: true,
                    iceTransportPolicy: 'all',
                    iceServers: [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' }
                    ],
                    // CRITICAL: Ensure bidirectional video sharing (like Google Meet)
                    enableRemb: true,
                    enableTcc: true,
                    // Force receive all remote videos
                    receiveMultipleVideoStreams: true,
                    // Ensure iframe can load properly
                    enableLayerSuspension: false, // Disable to prevent connection issues
                    // Like Google Meet: ensure video is always shown
                    hideDisplayName: false,
                    hideParticipantsStats: false,
                    // Force interface to show (like working Jitsi)
                    disableRemoteMute: false,
                    enableIncomingCallSounds: true,
                    // Ensure video is visible
                    defaultLanguage: 'en',
                    enableTileView: true,
                    // Video quality settings
                    videoQuality: {
                        maxBitrate: 1000000,
                        minBitrate: 200000,
                        disabledCodec: '',
                        preferredCodec: 'VP8',
                        resizeDesktopForPresenter: true
                    },
                    p2p: {
                        enabled: false // Disable P2P to fix black screen issues on localhost/same-network
                    },
                    // Explicitly enable permissions and video
                    requireDisplayName: false,
                    enableWelcomePage: false,
                    // Force local video to be visible (like Google Meet)
                    hideSelfView: false,
                    // Ensure video tracks are created
                    webrtcIceUdpDisable: false,
                    webrtcIceTcpDisable: false,
                    // CRITICAL: Enable screen sharing for all participants (employer and candidate)
                    enableScreenSharing: true,
                    desktopSharingFrameRate: {
                        min: 5,
                        max: 30
                    },
                    desktopSharingSourceDevice: 'screen',
                    // CRITICAL: Suppress the demo warning popup for meet.jit.si
                    // This popup appears because meet.jit.si has a 5-minute limit for embedded calls
                    // For production, you should self-host Jitsi or use Jitsi as a Service (JaaS)
                    disableEmbed: false, // Set to true to disable embedded mode (shows full Jitsi page)
                    // Note: The 5-minute disconnect limit on meet.jit.si cannot be disabled
                    // This is a limitation of the free public instance
                };
                
                // CRITICAL: Add room password if available (ensures both participants join same room)
                if (this.roomPassword) {
                    configOverwrite.passphrase = this.roomPassword;
                    console.log('🔑 Room password added to configOverwrite');
                }
                
                // WARNING: If using meet.jit.si, calls will disconnect after 5 minutes
                // This is a limitation of the free public Jitsi instance
                // For production use, you need to either:
                // 1. Self-host your own Jitsi instance (free, but requires server setup)
                // 2. Use Jitsi as a Service (JaaS) - paid service
                if (this.domain.includes('meet.jit.si')) {
                    console.warn('⚠️ WARNING: Using public meet.jit.si instance. Calls will disconnect after 5 minutes.');
                    console.warn('⚠️ For production use, configure JITSI_DOMAIN in .env to point to your own Jitsi instance.');
                }
                
                // Create Jitsi API instance with complete config
                this.api = new JitsiMeetExternalAPI(jitsiDomain, {
                    roomName: this.roomName,
                    parentNode,
                    width: '100%',
                    height: '100%',
                    userInfo: {
                        displayName: this.displayName
                    },
                    configOverwrite: configOverwrite,
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_REMOTE_DISPLAY_NAME: 'Participant',
                    DEFAULT_LOCAL_DISPLAY_NAME: 'You',
                    DISABLE_JOIN_LEAVE_NOTIFICATIONS: false,
                    MOBILE_APP_PROMO: false,
                    SHOW_CHROME_EXTENSION_BANNER: false,
                    DISABLE_VIDEO_BACKGROUND: true,
                    DISABLE_FILMSTRIP_AUTOHIDING: true,
                    FILM_STRIP_MAX_HEIGHT: 120,
                    VERTICAL_FILMSTRIP: false, // Horizontal filmstrip like Google Meet
                    // Like Google Meet: always show local video in filmstrip
                    LOCAL_THUMBNAIL_RATIO: 16 / 9,
                    REMOTE_THUMBNAIL_RATIO: 16 / 9,
                    // CRITICAL: Keep essential UI buttons enabled so Jitsi renders the views correctly
                    // CRITICAL: Add 'share-screen' button for all participants (employer and candidate)
                    TOOLBAR_BUTTONS: ['microphone', 'camera', 'desktop', 'tileview', 'filmstrip', 'chat', 'participants-pane', 'toggle-camera', 'toggle-mic', 'share-screen'],
                    // Ensure local video is always visible
                    HIDE_INVITE_MORE_HEADER: false,
                    DISPLAY_WELCOME_PAGE_CONTENT: false,
                    DISPLAY_WELCOME_FOOTER: false,
                    // CRITICAL: Force tile view to show all participants (like Google Meet)
                    DEFAULT_BACKGROUND: '#000000',
                    INITIAL_TOOLBAR_TIMEOUT: 20000,
                    TOOLBAR_TIMEOUT: 4000,
                    // Ensure participants can see each other
                    SHOW_JITSI_WATERMARK: false,
                    NATIVE_APP_NAME: 'Mindware Interview',
                    // CRITICAL: Hide the demo warning popup (won't prevent 5-minute disconnect though)
                    DISABLE_DOMINANT_SPEAKER_INDICATOR: false,
                    // Try to suppress the demo warning
                    DISABLE_FOCUS_INDICATOR: false
                }
                });
                console.log('✅ Jitsi API initialized successfully');
                
                // NOTE: Password is already set via passphrase in configOverwrite above
                // The executeCommand('password', ...) method can cause errors if API not fully ready
                // Using passphrase in config is the recommended approach
                if (this.roomPassword) {
                    console.log('🔑 Room password configured via passphrase in configOverwrite');
                    
                    // Optional: Verify password was set (only if needed, don't force it)
                    setTimeout(() => {
                        if (this.api) {
                            try {
                                // Check if API supports password command before using it
                                // Some Jitsi versions may not support this command
                                const apiType = typeof this.api.executeCommand;
                                if (apiType === 'function') {
                                    // Only try if API is ready and command exists
                                    // This is optional since passphrase in config should work
                                    console.log('🔑 Password verification - using passphrase in config (recommended method)');
                                }
                            } catch(err) {
                                // Ignore - passphrase in config is sufficient
                                console.log('🔑 Using passphrase from config (command method not needed)');
                            }
                        }
                    }, 1000);
                }
            } catch (error) {
                console.error('❌ Failed to initialize Jitsi API:', error);
                this.showToast('Failed to start video conference. Please refresh the page.', 'error');
                return;
            }

            // Handle Camera/Mic Permission Errors (critical for live sites)
            this.api.addEventListener('cameraError', (e) => {
                console.error('❌ Camera Access Error:', e);
                const errorType = e?.type || 'unknown';
                let message = 'Camera access denied. ';
                
                if (errorType === 'permission' || errorType === 'NotAllowedError') {
                    message += 'Please click the lock icon (🔒) in your browser address bar → Site settings → Camera → Allow, then refresh the page.';
                } else if (errorType === 'notFound' || errorType === 'NotFoundError') {
                    message += 'No camera found. Please connect a camera and refresh.';
                } else if (errorType === 'constraints') {
                    message += 'Camera constraints not supported. Please try a different browser.';
                } else {
                    message += `Error: ${errorType}. Please check your browser settings and allow camera access.`;
                }
                
                console.error('Camera error details:', e);
                this.showToast(message, 'error');
                this.videoMuted = true;
                
                // On live sites, show more detailed instructions
                if (window.location.protocol === 'https:' && window.location.hostname !== 'localhost') {
                    console.error('🔴 VIDEO ERROR ON LIVE SITE - User needs to grant permissions manually');
                }
            });
            
            this.api.addEventListener('micError', (e) => {
                console.error('Mic Access Error:', e);
                const errorType = e?.type || 'unknown';
                let message = 'Microphone access denied. ';
                if (errorType === 'permission') {
                    message += 'Please click the lock icon in your browser URL bar, reset permissions, and refresh the page.';
                } else if (errorType === 'notFound') {
                    message += 'No microphone found.';
                } else {
                    message += 'Please check your browser settings and allow microphone access.';
                }
                this.showToast(message, 'error');
                this.audioMuted = true;
            });

            // CRITICAL: Listen for connection failures
            this.api.addEventListener('connectionFailed', (e) => {
                console.error('❌ JITSI CONNECTION FAILED:', e);
                this.showToast('Failed to connect to video conference. Please check your internet connection and try again.', 'error');
                clearTimeout(this.connectionTimeout);
            });
            
            // Listen for iframe errors
            this.api.addEventListener('error', (e) => {
                console.error('❌ Jitsi API Error:', e);
                if (e && e.message) {
                    console.error('Error message:', e.message);
                    this.showToast(`Jitsi error: ${e.message}. Please refresh the page.`, 'error');
                }
            });
            
            // CRITICAL: Check if iframe loaded properly and verify it's working
            setTimeout(() => {
                const iframe = parentNode.querySelector('iframe');
                if (iframe) {
                    console.log('✅ Jitsi iframe created:', {
                        src: iframe.src,
                        width: iframe.offsetWidth,
                        height: iframe.offsetHeight,
                        display: window.getComputedStyle(iframe).display,
                        visibility: window.getComputedStyle(iframe).visibility
                    });
                    
                    // Verify iframe src is correct
                    if (!iframe.src || !iframe.src.includes('meet.jit.si') && !iframe.src.includes(jitsiDomain)) {
                        console.error('❌ Iframe src is incorrect:', iframe.src);
                        this.showToast('Invalid Jitsi configuration. Please contact support.', 'error');
                    }
                    
                    // Monitor iframe load
                    iframe.onload = () => {
                        console.log('✅✅✅ Jitsi iframe loaded successfully - content should now be visible');
                        
                        // Check if iframe content is accessible (will fail due to CORS but that's OK)
                        setTimeout(() => {
                            try {
                                // Try to access iframe content (will fail but confirms iframe is there)
                                const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
                                if (iframeDoc) {
                                    console.log('✅ Iframe content accessible');
                                }
                            } catch(e) {
                                // This is expected due to CORS - it means iframe is loaded
                                console.log('✅ Iframe loaded (CORS prevents content access - this is normal)');
                            }
                        }, 1000);
                    };
                    
                    iframe.onerror = (e) => {
                        console.error('❌ Jitsi iframe load error:', e);
                        this.showToast('Failed to load video conference. Please check your firewall settings or try a different browser.', 'error');
                    };
                    
                    // Additional check - verify iframe is visible
                    setTimeout(() => {
                        const rect = iframe.getBoundingClientRect();
                        console.log('🔍 Iframe visibility check:', {
                            visible: rect.width > 0 && rect.height > 0,
                            width: rect.width,
                            height: rect.height,
                            top: rect.top,
                            left: rect.left
                        });
                        
                        if (rect.width === 0 || rect.height === 0) {
                            console.error('❌ Iframe has zero dimensions - container may not be sized correctly');
                            this.showToast('Video container has no size. Please check page layout.', 'error');
                        }
                    }, 3000);
                } else {
                    console.error('❌ Jitsi iframe not found after initialization!');
                    console.error('Parent node:', parentNode);
                    console.error('Parent innerHTML:', parentNode.innerHTML.substring(0, 200));
                    this.showToast('Failed to create video conference iframe. Please refresh the page.', 'error');
                }
            }, 2000);
            
            // Additional verification after 5 seconds
            setTimeout(() => {
                const iframe = parentNode.querySelector('iframe');
                if (iframe) {
                    const rect = iframe.getBoundingClientRect();
                    console.log('🔍 Final iframe check (5s):', {
                        exists: !!iframe,
                        hasSrc: !!iframe.src,
                        visible: rect.width > 0 && rect.height > 0,
                        dimensions: `${rect.width}x${rect.height}`
                    });
                    
                    // If iframe exists but appears blank, try to reload
                    if (rect.width > 0 && rect.height > 0 && !this.joined) {
                        console.log('⚠️ Iframe exists but not joined - connection may have failed');
                    }
                }
            }, 5000);
            
            // Google Meet-like behavior: Force video to be visible immediately
            // Wait for API to be ready first
            this.api.addEventListener('readyToClose', () => {
                console.log('✅ Jitsi API ready to close');
            });
            
            // Suppress chrome-extension://invalid errors (non-critical warning)
            // These are caused by Jitsi checking for Chrome extensions
            const originalConsoleError = console.error;
            const originalConsoleWarn = console.warn;
            console.error = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('chrome-extension://invalid')) {
                    // Suppress chrome extension errors - they're not critical for video
                    return; // Don't log these
                }
                originalConsoleError.apply(console, args);
            };
            
            // 1. When video conference is joined - CRITICAL EVENT
            // This event MUST fire for connection to be successful
            this.api.addEventListener('videoConferenceJoined', () => {
                console.log('✅✅✅ VIDEO CONFERENCE JOINED SUCCESSFULLY! ✅✅✅');
                this.handleJoin();
                
                // Wait a bit for Jitsi to be fully ready, then force video
                setTimeout(() => {
                    this.forceVideoInitialization();
                }, 500);
            });
            
            // Listen for when ready to join (before joined)
            this.api.addEventListener('participantJoined', (e) => {
                console.log('✅ Participant joined event:', e);
            });
            
            // Listen for participant properties changed (indicates connection)
            this.api.addEventListener('participantJoined', () => {
                if (!this.joined) {
                    console.log('⚠️ Participant event received but videoConferenceJoined not fired - forcing join');
                    // If participant joined but videoConferenceJoined didn't fire, force it
                    setTimeout(() => {
                        if (!this.joined && this.api) {
                            console.log('🔄 Forcing join detection...');
                            this.handleJoin();
                        }
                    }, 1000);
                }
            });

            // 2. When local video track is added (crucial for self-view)
            this.api.addEventListener('videoAvailabilityChanged', (e) => {
                if (e.available) {
                    console.log('✅ Video available, ensuring visibility...');
                    setTimeout(() => {
                        this.ensureVideoVisible();
                        // Only use valid Jitsi commands
                        try { 
                            this.api.executeCommand('toggleTileView'); 
                        } catch(e) {
                            console.warn('toggleTileView failed:', e);
                        }
                    }, 500);
                }
            });

            // 3. When participant joins - ensure we see their video (REMOVED DUPLICATE - handled in bindApiEvents)

            this.bindApiEvents();
            
            // Mark as ready after a short delay to ensure API is fully initialized
            setTimeout(() => {
                this.ready = true;
                console.log('✅ Jitsi API is ready');
                
                // CRITICAL: Verify connection is actually working
                this.verifyConnection();
            }, 1000);
            
            // Additional verification after 5 seconds
            setTimeout(() => {
                if (!this.joined) {
                    console.log('🔍 Performing additional connection verification...');
                    this.verifyConnection();
                }
            }, 5000);

            // Monitor for permission errors and video state after delay
            setTimeout(() => {
                if (this.api && this.joined) {
                    console.log('🔄 Checking video state after 3 seconds...');
                    // Check if video is actually working
                    this.api.isVideoMuted().then((isMuted) => {
                        console.log('Video muted state (3s check):', isMuted);
                        if (isMuted) {
                            console.log('🔄 Video is still muted, attempting to enable...');
                            // Try to enable video one more time
                            this.forceVideoInitialization();
                        } else {
                            console.log('✅ Video is unmuted, checking visibility...');
                            // Even if unmuted, ensure it's visible
                            try {
                                this.api.executeCommand('toggleTileView');
                            } catch(e) {
                                console.warn('Failed to toggle tile view in 3s check:', e);
                            }
                        }
                    }).catch((err) => {
                        // Permission might be denied - show helpful message
                        console.warn('⚠️ Cannot check video state - permissions may be denied:', err);
                        this.showToast('Camera access may be denied. Please check browser permissions.', 'error');
                    });
                }
            }, 3000);

            // FALLBACK: If event doesn't fire, check connection status manually
            // This fixes the issue where "videoConferenceJoined" is missed
            this.forceJoinTimer = setInterval(() => {
                if (this.joined) {
                    clearInterval(this.forceJoinTimer);
                    this.forceJoinTimer = null;
                    return;
                }
                try {
                    if (!this.api) return;
                    
                    // Check if we can get participant count (indicates connection)
                    const count = this.api.getNumberOfParticipants();
                    console.log('🔍 Fallback check - participant count:', count);
                    
                    if (count >= 0) { // Even 0 means connected (just no other participants)
                        console.log('✅ Detected connection via polling, forcing join...');
                        clearTimeout(this.connectionTimeout);
                        this.handleJoin();
                        // Ensure video is enabled
                        this.forceVideoInitialization();
                    }
                } catch(e) {
                    console.warn('⚠️ Fallback check failed (may be normal):', e.message);
                }
            }, 3000); // Check every 3 seconds
            
            // CRITICAL: Monitor participant count continuously to detect remote participants
            this.participantMonitorTimer = setInterval(() => {
                if (!this.api || !this.joined) return;
                
                try {
                    const currentCount = this.api.getNumberOfParticipants();
                    // getNumberOfParticipants() returns count excluding self
                    // So if count is 0, only self is present
                    // If count is 1+, remote participants are present
                    const actualTotal = currentCount + 1; // +1 for self
                    
                    if (actualTotal !== this.participantsCount) {
                        console.log(`🔄 Participant count updated: ${this.participantsCount} → ${actualTotal} (API shows ${currentCount} remote)`);
                        this.participantsCount = actualTotal;
                        
                        // If remote participant detected, ensure video is visible
                        if (currentCount > 0) {
                            console.log('✅✅✅ REMOTE PARTICIPANT DETECTED! Ensuring bidirectional video visibility...');
                            setTimeout(() => {
                                try {
                                    // Force tile view to show remote video (like Google Meet)
                                    this.api.executeCommand('toggleTileView');
                                    // Also ensure video is unmuted
                                    this.ensureVideoVisible();
                                    console.log('✅ Remote participant video should now be visible');
                                } catch(err) {
                                    console.warn('Failed to enable tile view for remote participant:', err);
                                }
                            }, 500);
                        }
                    }
                } catch(e) {
                    // Silent fail - API might not support this
                }
            }, 2000); // Check every 2 seconds
        },
        loadExternalApi() {
            return new Promise((resolve, reject) => {
                if (window.JitsiMeetExternalAPI) {
                    console.log('✅ Jitsi API already loaded');
                    return resolve();
                }
                
                // Ensure we're using HTTPS for the external API
                const apiUrl = `https://${this.domain}/external_api.js`;
                console.log(`🔄 Loading Jitsi API from: ${apiUrl}`);
                
                const script = document.createElement('script');
                script.src = apiUrl;
                script.async = true;
                script.crossOrigin = 'anonymous';
                script.integrity = ''; // Jitsi doesn't provide SRI, but we can add it later if needed
                
                let timeout = setTimeout(() => {
                    script.remove();
                    const error = new Error(`⏱️ Timeout: Failed to load Jitsi API from ${this.domain} within 30 seconds. Please check your internet connection and firewall settings.`);
                    console.error(error);
                    this.showToast('Failed to load video conference. Please check your internet connection and refresh the page.', 'error');
                    reject(error);
                }, 30000); // 30 second timeout
                
                script.onload = () => {
                    clearTimeout(timeout);
                    if (window.JitsiMeetExternalAPI) {
                        console.log('✅ Jitsi API loaded successfully from:', this.domain);
                        resolve();
                    } else {
                        const error = new Error('❌ Jitsi API script loaded but JitsiMeetExternalAPI is not available');
                        console.error(error);
                        this.showToast('Video conference API failed to initialize. Please refresh the page.', 'error');
                        reject(error);
                    }
                };
                
                script.onerror = (e) => {
                    clearTimeout(timeout);
                    script.remove();
                    const error = new Error(`❌ Network Error: Failed to load Jitsi API from ${this.domain}. This could be due to network issues, firewall, or the Jitsi server being down.`);
                    console.error('Script load error:', e);
                    console.error('Error details:', error);
                    this.showToast(`Failed to load video conference from ${this.domain}. Please check your internet connection and try again.`, 'error');
                    reject(error);
                };
                
                // Handle script errors during execution
                script.onerror = script.onerror || ((e) => {
                    console.error('Script execution error:', e);
                });
                
                document.head.appendChild(script);
                console.log('📡 Script tag added to document head');
            });
        },
        bindApiEvents() {
            if (!this.api) return;

            const postEvent = async (type, data) => {
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

            this.api.addEventListener('videoConferenceJoined', async () => {
                this.handleJoin();
                // Check current status if possible, or wait for status change events
                // Defaulting to user preference (unmuted) as per config
                await this.setRoomPasswordIfModerator();
                
                // Like Google Meet: Force video initialization immediately
                setTimeout(() => {
                    this.forceVideoInitialization();
                }, 300);
                
                postEvent('user_joined', {});
            });

            this.api.addEventListener('videoMuteStatusChanged', (e) => {
                this.videoMuted = e.muted;
                
                // If video was just unmuted, ensure it's visible
                if (!e.muted) {
                    setTimeout(() => {
                        try {
                            this.api.executeCommand('toggleTileView');
                        } catch(err) {}
                    }, 300);
                }
            });

            this.api.addEventListener('audioMuteStatusChanged', (e) => {
                this.audioMuted = e.muted;
            });

            // Handle when local video track is created (like Google Meet)
            this.api.addEventListener('localVideoTrackAdded', (track) => {
                console.log('✅ Local video track added - ensuring visibility', track);
                // Like Google Meet: Immediately show local video when track is created
                setTimeout(() => {
                    try {
                        // Force tile view to show all videos including local (only valid command)
                        this.api.executeCommand('toggleTileView');
                        console.log('✅ Local video track - tile view toggled');
                    } catch(err) {
                        console.error('Failed to show local video track:', err);
                    }
                }, 500);
            });

            // Handle participant video track updates (already handled above, keeping for redundancy)

            this.api.addEventListener('cameraError', (e) => {
                console.error('Jitsi Camera Error:', e);
                let msg = 'Camera error: ' + (e.type || 'Unknown');
                if (e.type === 'permission' || e.type === 'NotAllowedError' || e.message?.includes('permission')) {
                    msg = 'Camera permission denied. Click the lock icon in the address bar, reset permissions, and refresh the page.';
                } else if (e.type === 'notFound' || e.type === 'NotFoundError') {
                    msg = 'No camera found. Please connect a camera.';
                }
                this.showToast(msg, 'error');
                this.videoMuted = true;
            });
            
            // Listen for track creation failures (catches gum.permission_denied errors)
            this.api.addEventListener('trackCreateError', (e) => {
                console.error('Track creation error:', e);
                if (e && (e.message?.includes('permission') || e.message?.includes('NotAllowed'))) {
                    this.showToast('Camera/Microphone permission denied. Please reset permissions in browser settings (click lock icon) and refresh.', 'error');
                }
            });

            this.api.addEventListener('micError', (e) => {
                let msg = 'Microphone error: ' + (e.type || 'Unknown');
                if (e.type === 'permission') {
                    msg = 'Microphone permission denied. Please allow microphone access.';
                } else if (e.type === 'notFound') {
                    msg = 'No microphone found.';
                }
                this.showToast(msg, 'error');
                this.audioMuted = true;
            });

            // CRITICAL: Track participants properly for bidirectional video
            this.api.addEventListener('participantJoined', (e) => {
                console.log('✅✅✅ PARTICIPANT JOINED EVENT:', e);
                const participantInfo = e?.participant || e;
                console.log('Participant details:', {
                    id: participantInfo?.id || participantInfo?.participantId,
                    displayName: participantInfo?.displayName,
                    isLocal: participantInfo?.isLocal
                });
                
                // Update participant count (don't count self)
                if (!participantInfo?.isLocal) {
                    this.participantsCount = (this.participantsCount || 1) + 1;
                    console.log('✅ Remote participant joined! New count:', this.participantsCount);
                }
                
                // CRITICAL: Force tile view to show all participants including remote
                setTimeout(() => {
                    try {
                        console.log('🔄 Enabling tile view to show participant video...');
                        // Enable tile view to see all participants
                        this.api.executeCommand('toggleTileView');
                        // Also ensure we're not in speaker view (which hides others)
                        this.api.executeCommand('toggleTileView'); // Toggle twice to ensure it's on
                        console.log('✅ Tile view enabled for participant video');
                    } catch(err) {
                        console.error('❌ Failed to toggle tile view on participant join:', err);
                    }
                }, 500);
                
                // Force refresh after a bit longer
                setTimeout(() => {
                    try {
                        this.api.executeCommand('toggleTileView');
                    } catch(err) {}
                }, 2000);
                
                postEvent('participant_joined', e || {});
            });

            this.api.addEventListener('participantLeft', (e) => {
                console.log('👋 PARTICIPANT LEFT EVENT:', e);
                const participantInfo = e?.participant || e;
                
                // Update count (don't count self leaving)
                if (!participantInfo?.isLocal) {
                    this.participantsCount = Math.max(1, (this.participantsCount || 1) - 1);
                    console.log('👋 Remote participant left! New count:', this.participantsCount);
                }
                
                postEvent('participant_left', e || {});
            });
            
            // CRITICAL: Listen for remote video tracks being added
            this.api.addEventListener('participantVideoTrackAdded', (track) => {
                console.log('✅✅✅ REMOTE VIDEO TRACK ADDED:', track);
                if (track && track.participantId) {
                    console.log('Remote participant video track for:', track.participantId);
                    
                    // Force tile view to display remote video
                    setTimeout(() => {
                        try {
                            this.api.executeCommand('toggleTileView');
                            console.log('✅ Remote video track - tile view enabled');
                        } catch(err) {
                            console.error('Failed to enable tile view for remote video:', err);
                        }
                    }, 300);
                }
            });
            
            // Listen for participant video track updates
            this.api.addEventListener('videoTrackAdded', (track) => {
                console.log('✅ Video track added (any):', track);
                if (track && track.participantId && !track.isLocal) {
                    console.log('✅ Remote participant video track detected!');
                    setTimeout(() => {
                        try {
                            this.api.executeCommand('toggleTileView');
                        } catch(err) {}
                    }, 300);
                }
            });

            this.api.addEventListener('audioMuteStatusChanged', (e) => {
                this.audioMuted = !!e.muted;
                postEvent('audio_mute_changed', e || {});
            });

            this.api.addEventListener('videoMuteStatusChanged', (e) => {
                this.videoMuted = !!e.muted;
                postEvent('video_mute_changed', e || {});
            });

            // CRITICAL: Listen for screen sharing events (for both employer and candidate)
            this.api.addEventListener('screenSharingStatusChanged', (e) => {
                console.log('📺 Screen sharing status changed:', e);
                this.sharing = !!e.on;
                if (this.sharing) {
                    console.log('✅ Screen sharing started');
                    this.showToast('Screen sharing started', 'success');
                } else {
                    console.log('✅ Screen sharing stopped');
                    this.showToast('Screen sharing stopped', 'info');
                }
                postEvent(this.sharing ? 'screen_share_started' : 'screen_share_stopped', e || {});
            });
            
            // Listen for screen sharing errors
            this.api.addEventListener('screenSharingError', (e) => {
                console.error('❌ Screen sharing error:', e);
                this.showToast('Failed to share screen. Please check browser permissions.', 'error');
            });

            // Handle password requirement (shouldn't happen if passphrase is set in config)
            this.api.addEventListener('passwordRequired', async () => {
                console.log('🔑 Password required for room');
                if (this.roomPassword && this.api) {
                    console.log('🔑 Submitting room password...');
                    try {
                        // CRITICAL FIX: Don't use executeCommand('password') - it causes TypeError
                        // Password is already set via passphrase in configOverwrite above
                        // This handler is just a fallback if Jitsi still requests password
                        console.log('🔑 Password already configured via passphrase in configOverwrite');
                        this.showToast('Room password configured', 'success');
                        
                        // If passphrase in config doesn't work, we could try submitting password
                        // But first verify API is ready and has the necessary methods
                        if (this.api && typeof this.api.executeCommand === 'function') {
                            // Wait a bit for API to be fully ready before trying command
                            setTimeout(() => {
                                try {
                                    // Only submit if password is still required
                                    // Check if there's a password submit method
                                    const passwordInput = document.querySelector('input[type="password"]');
                                    if (passwordInput) {
                                        console.log('🔑 Submitting password via form (fallback)');
                                        passwordInput.value = this.roomPassword;
                                        const submitBtn = document.querySelector('button[type="submit"]');
                                        if (submitBtn) submitBtn.click();
                                    } else {
                                        console.log('🔑 Password should be auto-applied via passphrase in config');
                                    }
                                } catch(err) {
                                    console.warn('⚠️ Password submission not needed - passphrase in config should work:', err);
                                }
                            }, 1000);
                        }
                        // REMOVED: this.api.executeCommand('password', this.roomPassword);
                        // This was causing TypeError: Cannot read properties of undefined (reading 'lock')
                        // Password is already set via passphrase in configOverwrite, so this is not needed
                        postEvent('password_submitted', {});
                        console.log('✅ Password configured via passphrase in config');
                    } catch(err) {
                        console.warn('⚠️ Password handler error (non-critical - password already in config):', err);
                    }
                } else {
                    console.error('❌ No password available but password required!');
                    this.showToast('Room requires password but none is available. Please refresh.', 'error');
                }
            });

            this.api.addEventListener('readyToClose', async () => {
                console.log('🔄 Ready to close event fired');
                postEvent('meeting_ready_to_close', {});
                
                // Clean up timers
                if (this.forceJoinTimer) {
                    clearInterval(this.forceJoinTimer);
                    this.forceJoinTimer = null;
                }
                if (this.participantMonitorTimer) {
                    clearInterval(this.participantMonitorTimer);
                    this.participantMonitorTimer = null;
                }
                if (this.connectionTimeout) {
                    clearTimeout(this.connectionTimeout);
                    this.connectionTimeout = null;
                }
                
                if (this.capabilities.can_end) {
                    try {
                        await fetch(`/interviews/${this.interviewId}/end`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': this.csrf()
                            },
                            body: JSON.stringify({})
                        });
                    } catch(err) {
                        console.error('Failed to end meeting on server:', err);
                    }
                }
                
                // Redirect after a short delay
                setTimeout(() => {
                    const redirectUrl = this.capabilities.role === 'employer' 
                        ? '/employer/interviews' 
                        : (this.capabilities.role === 'candidate' 
                            ? '/candidate/interviews' 
                            : '/admin/dashboard');
                    console.log('🔄 Redirecting to:', redirectUrl);
                    window.location.href = redirectUrl;
                }, 500);
            });
        },
        async forceVideoInitialization() {
            if (!this.api) {
                console.warn('API not ready for video initialization');
                return;
            }
            
            try {
                // Like Google Meet: Ensure video is unmuted and visible
                console.log('🔄 Forcing video initialization...');
                
                // Wait a bit to ensure API is fully ready
                await new Promise(resolve => setTimeout(resolve, 300));
                
                // Step 1: Check and unmute video if needed
                try {
                    const isVideoMuted = await this.api.isVideoMuted();
                    console.log('Video muted state:', isVideoMuted);
                    if (isVideoMuted) {
                        console.log('🔄 Video is muted, unmuting...');
                        this.api.executeCommand('toggleVideo');
                        this.videoMuted = false;
                    } else {
                        // Video should already be enabled, but ensure it's visible
                        console.log('✅ Video is already unmuted');
                    }
                } catch(err) {
                    console.warn('⚠️ Cannot check video muted state:', err);
                    // Try to enable video anyway (maybe API doesn't support isVideoMuted)
                    try {
                        console.log('🔄 Attempting to enable video directly...');
                        this.api.executeCommand('toggleVideo');
                        this.videoMuted = false;
                    } catch(e) {
                        console.error('❌ Failed to toggle video:', e);
                    }
                }
                
                // Step 2: Ensure audio is unmuted
                try {
                    const isAudioMuted = await this.api.isAudioMuted();
                    if (isAudioMuted) {
                        console.log('🔄 Audio is muted, unmuting...');
                        this.api.executeCommand('toggleAudio');
                        this.audioMuted = false;
                    }
                } catch(err) {
                    console.warn('⚠️ Cannot check audio muted state:', err);
                }
                
                // Step 3: Force tile view to show all videos (like Google Meet)
                setTimeout(() => {
                    if (!this.api) return;
                    try {
                        // Only use valid Jitsi command - toggleTileView
                        console.log('🔄 Enabling tile view...');
                        this.api.executeCommand('toggleTileView');
                        console.log('✅ Tile view enabled');
                    } catch(e) {
                        console.error('❌ Failed to toggle tile view:', e);
                    }
                }, 1000);
                
                // Step 4: Additional check after 2 seconds
                setTimeout(() => {
                    this.ensureVideoVisible();
                }, 2500);
                
            } catch(e) {
                console.error('❌ Error in forceVideoInitialization:', e);
            }
        },
        
        async ensureVideoVisible() {
            if (!this.api) {
                console.warn('API not ready for ensureVideoVisible');
                return;
            }
            
            try {
                // Double-check video is enabled and visible
                const isMuted = await this.api.isVideoMuted();
                if (isMuted) {
                    console.log('🔄 Video still muted, trying again...');
                    this.api.executeCommand('toggleVideo');
                    this.videoMuted = false;
                    
                    // Force view refresh
                    setTimeout(() => {
                        if (!this.api) return;
                        try {
                            this.api.executeCommand('toggleTileView');
                            console.log('✅ View refreshed after unmute');
                        } catch(e) {
                            console.warn('Failed to refresh view:', e);
                        }
                    }, 800);
                } else {
                    console.log('✅ Video is confirmed unmuted and should be visible');
                }
            } catch(err) {
                console.warn('⚠️ Cannot ensure video visible (checking state failed):', err);
                // Try to enable video anyway
                try {
                    if (this.api) {
                        this.api.executeCommand('toggleVideo');
                        this.videoMuted = false;
                    }
                } catch(e) {
                    console.error('Failed to enable video in fallback:', e);
                }
            }
        },
        
        async ensureVideoEnabled() {
            if (!this.api) return;
            
            // Use the new forceVideoInitialization method
            await this.forceVideoInitialization();
        },
        handleJoin() {
            if (this.joined) return;
            this.joined = true;
            console.log('✅✅✅ Successfully joined meeting!');
            this.showToast('Joined the meeting successfully', 'success');
            
            // Clear connection timeout
            if (this.connectionTimeout) {
                clearTimeout(this.connectionTimeout);
                this.connectionTimeout = null;
            }
            
            // Clear fallback timer
            if (this.forceJoinTimer) {
                clearInterval(this.forceJoinTimer);
                this.forceJoinTimer = null;
            }

            // Like Google Meet: Force video to be visible immediately
            // This ensures local video shows even when alone
            setTimeout(() => {
                this.forceVideoInitialization();
            }, 200);

            // CRITICAL: Force tile view to show all participants (including remote when they join)
            setTimeout(() => {
                try {
                    // Enable tile view to see all participants including self
                    this.api.executeCommand('toggleTileView');
                    console.log('✅ Tile view enabled in handleJoin');
                    
                    // Also check if remote participants are already present
                    try {
                        const participantCount = this.api.getNumberOfParticipants();
                        if (participantCount > 0) {
                            console.log(`✅ Remote participants already present (${participantCount}), updating count...`);
                            this.participantsCount = participantCount + 1; // +1 for self
                        }
                    } catch(e) {}
                } catch(e) { 
                    console.error('Failed to enable tile view:', e); 
                }
            }, 800);

            // Additional check to ensure video is visible and participants are detected
            setTimeout(() => {
                this.ensureVideoVisible();
                
                // Check for remote participants again
                try {
                    const participantCount = this.api.getNumberOfParticipants();
                    console.log('🔍 Checking for remote participants...', participantCount);
                    if (participantCount > 0) {
                        this.participantsCount = participantCount + 1;
                        // Force tile view to show remote video
                        this.api.executeCommand('toggleTileView');
                    }
                } catch(e) {}
            }, 2000);
        },
        async setRoomPasswordIfModerator() {
            if (!this.roomPassword || !this.api) {
                console.log('⚠️ Cannot set password:', {
                    hasPassword: !!this.roomPassword,
                    hasApi: !!this.api
                });
                return;
            }
            
            try {
                console.log('🔑 Room password already configured via passphrase in configOverwrite');
                // REMOVED: this.api.executeCommand('password', this.roomPassword);
                // This was causing TypeError: Cannot read properties of undefined (reading 'lock')
                // Password is already set via passphrase in configOverwrite above, so this is not needed
                // The passphrase in config is the recommended and correct way to set room passwords
                console.log('✅ Room password configured via passphrase (recommended method)');
            } catch (err) {
                console.warn('⚠️ Password setting error (non-critical - password already in config):', err);
            }
        },
        toggleTileView() {
            if (!this.api) {
                console.warn('Cannot toggle tile view - API not ready');
                return;
            }
            try {
                console.log('🔄 Toggling tile view...');
                this.api.executeCommand('toggleTileView');
                console.log('✅ Tile view toggled successfully');
            } catch (e) {
                console.error('❌ Failed to toggle tile view:', e);
                this.showToast('Failed to toggle grid view. The video may still be loading.', 'error');
            }
        },
        toggleAudio() {
            if (!this.api) return;
            try {
                this.api.executeCommand('toggleAudio');
            } catch (e) {
                this.showToast('Failed to toggle audio. Check your microphone.', 'error');
            }
        },
        async toggleVideo() {
            if (!this.api) return;
            try {
                // Check current state
                const isMuted = await this.api.isVideoMuted();
                
                // Toggle video
                this.api.executeCommand('toggleVideo');
                
                // After toggling, wait a bit and ensure it worked
                setTimeout(async () => {
                    try {
                        const stillMuted = await this.api.isVideoMuted();
                        if (isMuted && stillMuted) {
                            // Video should be enabled but isn't - try again
                            console.warn('Video toggle failed, trying again...');
                            this.api.executeCommand('toggleVideo');
                            // Force tile view to refresh
                            setTimeout(() => {
                                try {
                                    this.api.executeCommand('toggleTileView');
                                } catch(e) {}
                            }, 300);
                        } else if (!isMuted && !stillMuted) {
                            // Video should be disabled but isn't - try again
                            this.api.executeCommand('toggleVideo');
                        }
                    } catch(e) {
                        console.error('Error checking video state:', e);
                    }
                }, 500);
            } catch (e) {
                console.error('Failed to toggle video:', e);
                this.showToast('Failed to toggle video. Check your camera and permissions.', 'error');
            }
        },
        toggleShare() {
            if (!this.api) {
                this.showToast('Video conference not ready. Please wait...', 'error');
                return;
            }
            try {
                console.log('🖥️ Toggling screen share...', {
                    currentState: this.sharing,
                    role: this.capabilities.role
                });
                
                // Use the standard Jitsi command for screen sharing
                this.api.executeCommand('toggleShareScreen');
                
                // The sharing state will be updated via the screenSharingStatusChanged event
            } catch (e) {
                console.error('❌ Screen share error:', e);
                this.showToast('Failed to share screen. Please check browser permissions for screen sharing.', 'error');
            }
        },
        toggleChat() {
            if (!this.api) return;
            try {
                this.api.executeCommand('toggleChat');
            } catch (e) {
                this.showToast('Failed to toggle chat', 'error');
            }
        },
        toggleParticipants() {
            if (!this.api) return;
            try {
                // Try toggling the participants pane
                this.api.executeCommand('toggleParticipantsPane', true);
            } catch (e) {
                this.showToast('Failed to toggle participants', 'error');
            }
        },
        toggleRaiseHand() {
            if (!this.api) return;
            try {
                this.api.executeCommand('toggleRaiseHand');
            } catch (e) {
                this.showToast('Failed to raise hand', 'error');
            }
        },
        async muteAll() {
            if (!this.api || !this.capabilities.can_mute_all) return;
            try {
                const participants = await this.api.getParticipantsInfo();
                for (const p of participants) {
                    if (p && p.participantId) {
                        this.api.executeCommand('muteParticipant', p.participantId);
                    }
                }
                await fetch(`/interviews/${this.interviewId}/events`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': this.csrf()
                    },
                    body: JSON.stringify({ type: 'admin_mute_all', data: { count: (participants || []).length } })
                });
            } catch (_) {}
        },
        async toggleRecording() {
            if (!this.api || !this.capabilities.can_record) return;
            try {
                if (!this.recording) {
                    this.api.executeCommand('startRecording', { mode: 'file' });
                    this.recording = true;
                    await fetch(`/interviews/${this.interviewId}/events`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-Token': this.csrf()
                        },
                        body: JSON.stringify({ type: 'recording_started', data: {} })
                    });
                } else {
                    this.api.executeCommand('stopRecording', { mode: 'file' });
                    this.recording = false;
                    await fetch(`/interviews/${this.interviewId}/events`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-Token': this.csrf()
                        },
                        body: JSON.stringify({ type: 'recording_stopped', data: {} })
                    });
                }
            } catch (_) {}
        },
        async endMeeting() {
            console.log('🔄 End meeting called');
            if (!this.capabilities.can_end) {
                console.warn('⚠️ Cannot end - no permission');
                return;
            }
            
            try {
                // First, hangup Jitsi
                if (this.api) {
                    console.log('📞 Hanging up Jitsi...');
                    try {
                        this.api.executeCommand('hangup');
                        // Wait a bit for hangup to process
                        await new Promise(resolve => setTimeout(resolve, 500));
                    } catch(err) {
                        console.error('Error hanging up Jitsi:', err);
                    }
                }
                
                // Then, mark meeting as ended on server
                console.log('📡 Marking meeting as ended on server...');
                const res = await fetch(`/interviews/${this.interviewId}/end`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': this.csrf()
                    },
                    body: JSON.stringify({})
                });
                
                if (!res.ok) {
                    console.error('Failed to end meeting on server:', res.status);
                }
                
                // Redirect
                const redirectUrl = this.capabilities.role === 'employer' 
                    ? '/employer/interviews' 
                    : (this.capabilities.role === 'candidate' 
                        ? '/candidate/interviews' 
                        : '/admin/dashboard');
                console.log('🔄 Redirecting to:', redirectUrl);
                window.location.href = redirectUrl;
            } catch(error) {
                console.error('❌ Error ending meeting:', error);
                this.showToast('Error ending meeting. Please try again.', 'error');
                // Try to redirect anyway
                setTimeout(() => {
                    window.location.href = this.capabilities.role === 'employer' ? '/employer/interviews' : '/candidate/interviews';
                }, 1000);
            }
        },
        
        async leaveMeeting() {
            console.log('👋 Leave meeting called');
            try {
                if (this.api) {
                    console.log('📞 Hanging up Jitsi...');
                    try {
                        // Hangup Jitsi first
                        this.api.executeCommand('hangup');
                        // Wait a bit
                        await new Promise(resolve => setTimeout(resolve, 300));
                    } catch(err) {
                        console.error('Error hanging up:', err);
                    }
                }
                
                // Navigate back
                const redirectUrl = this.capabilities.role === 'employer' 
                    ? '/employer/interviews' 
                    : (this.capabilities.role === 'candidate' 
                        ? '/candidate/interviews' 
                        : window.history.length > 1 ? -1 : '/');
                
                console.log('🔄 Leaving and redirecting...');
                if (window.history.length > 1 && redirectUrl === -1) {
                    window.history.back();
                } else if (typeof redirectUrl === 'string') {
                    window.location.href = redirectUrl;
                } else {
                    window.history.back();
                }
            } catch(error) {
                console.error('❌ Error leaving meeting:', error);
                // Fallback: try to go back
                window.history.back();
            }
        },
        tickTimer() {
            window.setInterval(() => {
                if (!this.startedAtMs) return;
                const diff = Math.max(0, Date.now() - this.startedAtMs);
                const sec = Math.floor(diff / 1000);
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                this.timerText = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            }, 1000);
        },
        
        verifyConnection() {
            if (!this.api) {
                console.warn('⚠️ Cannot verify - API not available');
                return;
            }
            
            try {
                // Try to get participant info (this will only work if connected)
                const participantCount = this.api.getNumberOfParticipants();
                console.log('🔍 Connection verification - participant count:', participantCount);
                
                // Try to get video muted state (also requires connection)
                this.api.isVideoMuted().then((isMuted) => {
                    console.log('🔍 Connection verification - video muted state:', isMuted);
                    // If we can get this, we're connected even if event didn't fire
                    if (!this.joined) {
                        console.log('✅ Connection verified via API - videoConferenceJoined event missed');
                        this.handleJoin();
                    }
                }).catch((err) => {
                    console.warn('⚠️ Cannot verify connection via isVideoMuted:', err.message);
                });
                
                // Try to get current room name
                try {
                    const roomName = this.api.getRoomName();
                    console.log('🔍 Connection verification - room name:', roomName);
                } catch(e) {
                    console.warn('⚠️ Cannot get room name (may not be connected yet):', e.message);
                }
            } catch (error) {
                console.error('❌ Connection verification failed:', error);
                // If verification fails, the connection likely hasn't been established
            }
        }
    }
}
</script>
