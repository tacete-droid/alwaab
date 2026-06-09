import { ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

export const unreadCount = ref(0);
export const permissionGranted = ref(false);
export const permissionRequested = ref(false);

let pollTimer = null;
let lastPollAt = null;
let knownIds = new Set();
let audioCtx = null;
let isAuthenticated = false;
let pollerActive = false;

function getAudioContext() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioCtx;
}

function playTone(freq, duration, delay = 0) {
    try {
        const ctx = getAudioContext();
        if (ctx.state === 'suspended') ctx.resume();

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.value = 0.25;
        osc.connect(gain);
        gain.connect(ctx.destination);

        const start = ctx.currentTime + delay;
        osc.start(start);
        gain.gain.exponentialRampToValueAtTime(0.001, start + duration);
        osc.stop(start + duration);
    } catch {
        // Audio not available
    }
}

export function playNotificationSound(sound = 'default') {
    if (sound === 'chat') {
        playTone(880, 0.12, 0);
        playTone(1100, 0.12, 0.15);
    } else if (sound === 'alert') {
        playTone(660, 0.15, 0);
        playTone(880, 0.15, 0.18);
        playTone(1100, 0.2, 0.36);
    } else {
        playTone(740, 0.18, 0);
        playTone(980, 0.18, 0.2);
    }
}

function shouldSuppress(notif) {
    if (notif.category !== 'chat') return false;
    if (window.location.pathname !== '/chat') return false;

    const params = new URLSearchParams(window.location.search);
    const activeId = params.get('conversation_id');
    return activeId && notif.conversation_id === activeId;
}

function showBrowserNotification(notif) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    if (shouldSuppress(notif)) return;

    const n = new Notification(notif.title, {
        body: notif.body,
        icon: '/favicon.ico',
        tag: notif.id,
        requireInteraction: notif.priority === 'urgent',
        silent: false,
    });

    n.onclick = () => {
        window.focus();
        if (notif.url) router.visit(notif.url);
        n.close();
    };
}

export async function requestNotificationPermission() {
    if (!('Notification' in window)) return false;
    if (Notification.permission === 'granted') {
        permissionGranted.value = true;
        return true;
    }
    if (Notification.permission === 'denied') return false;

    permissionRequested.value = true;
    const result = await Notification.requestPermission();
    permissionGranted.value = result === 'granted';
    return permissionGranted.value;
}

export async function pollNotifications() {
    if (!isAuthenticated) return;

    try {
        const params = lastPollAt ? `?since=${encodeURIComponent(lastPollAt)}` : '';
        const res = await fetch(`/notifications/poll${params}`, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!res.ok) return;

        const data = await res.json();
        unreadCount.value = data.unread_count ?? 0;

        const incoming = data.notifications || [];

        for (const notif of incoming) {
            if (knownIds.has(notif.id)) continue;
            knownIds.add(notif.id);

            if (lastPollAt && !notif.read_at) {
                if (!shouldSuppress(notif)) {
                    playNotificationSound(notif.sound);
                    showBrowserNotification(notif);
                }
            }
        }

        if (incoming.length > 0) {
            const latest = incoming[0]?.created_at;
            if (latest) lastPollAt = latest;
        } else if (!lastPollAt) {
            lastPollAt = new Date().toISOString();
        }
    } catch {
        // Polling failed silently
    }
}

export function startNotificationPoller() {
    if (pollerActive) return;
    pollerActive = true;

    const page = usePage();
    isAuthenticated = !!page.props.auth?.user;
    unreadCount.value = page.props.unread_notifications ?? 0;
    permissionGranted.value = 'Notification' in window && Notification.permission === 'granted';

    if (!isAuthenticated) return;

    pollNotifications();
    pollTimer = setInterval(pollNotifications, 8000);
}

export function stopNotificationPoller() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = null;
    pollerActive = false;
}
