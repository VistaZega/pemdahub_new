/**
 * PembdaHUB Web Push Notification Client Engine
 * Mengelola pendaftaran Push Manager, izin browser, dan sakelar preferensi
 */
window.PembdaPush = {
    isSupported() {
        return ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);
    },

    async getStatus() {
        if (!this.isSupported()) {
            return { supported: false };
        }
        try {
            const res = await fetch('/push/status', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return { supported: true, authenticated: false };
            const data = await res.json();
            data.supported = true;
            data.permission = Notification.permission;
            return data;
        } catch (e) {
            return { supported: true, error: e.message };
        }
    },

    async enablePush() {
        if (!this.isSupported()) {
            alert('Peramban web ini belum mendukung Web Push Notifications.');
            return false;
        }

        try {
            // 1. Minta izin notifikasi browser
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                alert('Izin notifikasi ditolak oleh peramban HP Anda. Silakan aktifkan izin notifikasi di setelan peramban.');
                return false;
            }

            // 2. Ambil registration service worker
            const reg = await navigator.serviceWorker.ready;
            if (!reg) {
                console.warn('Service Worker belum siap.');
                return false;
            }

            // 3. Ambil VAPID Public Key dari server
            const keyRes = await fetch('/push/vapid-key', { headers: { 'Accept': 'application/json' } });
            const keyData = await keyRes.json();
            const vapidPublicKey = keyData.public_key;

            const convertedKey = this.urlBase64ToUint8Array(vapidPublicKey);

            // 4. Daftarkan PushSubscription di browser
            let subscription = await reg.pushManager.getSubscription();
            if (!subscription) {
                subscription = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedKey
                });
            }

            // 5. Kirim data subscription ke server Laravel
            const subJson = subscription.toJSON();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const saveRes = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    endpoint: subJson.endpoint,
                    public_key: subJson.keys?.p256dh || null,
                    auth_token: subJson.keys?.auth || null,
                    device_name: navigator.userAgent
                })
            });

            const saveResult = await saveRes.json();
            if (saveResult.success) {
                // Aktifkan juga preferensi push_enabled = true
                await this.toggleModule('push_enabled', true);
                return true;
            }

            return false;
        } catch (err) {
            console.error('PembdaPush enable error:', err);
            return false;
        }
    },

    async toggleModule(field, value = null) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/push/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    field: field,
                    value: value
                })
            });
            return await res.json();
        } catch (e) {
            return { success: false, error: e.message };
        }
    },

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
};
