import { defineStore } from 'pinia';
import axios from 'axios';

export const useSyncStore = defineStore('sync', {
    state: () => ({
        isOnline: navigator.onLine,
        isSyncing: false,
        pendingCount: 0,
        lastSyncedAt: null,
        terminalId: localStorage.getItem('pos_terminal_id') || 'TERM-01',
        syncInterval: null,
    }),

    actions: {
        init() {
            // Set terminal ID if missing
            if (!localStorage.getItem('pos_terminal_id')) {
                localStorage.setItem('pos_terminal_id', this.terminalId);
            }

            // Window online / offline event listeners
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.triggerSync();
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            // Start periodic heartbeat & sync every 30 seconds
            this.checkStatus();
            this.syncInterval = setInterval(() => {
                this.checkStatus();
            }, 30000);
        },

        async checkStatus() {
            try {
                const res = await axios.get('/api/sync/ping', { timeout: 4000 });
                if (res.data && res.data.status === 'online') {
                    this.isOnline = true;
                    // If online and items pending, push!
                    if (this.pendingCount > 0 && !this.isSyncing) {
                        await this.triggerSync();
                    }
                }
            } catch (err) {
                // Ping failed - internet or backend unreachable
                this.isOnline = false;
            }
        },

        async triggerSync() {
            if (this.isSyncing || !this.isOnline) return;

            this.isSyncing = true;
            try {
                // Fetch pending local sync queue from localStorage / IndexedDB fallback
                const queueJson = localStorage.getItem('pos_offline_sync_queue');
                const queueItems = queueJson ? JSON.parse(queueJson) : [];

                if (queueItems.length > 0) {
                    const response = await axios.post('/api/sync/push', {
                        terminal_id: this.terminalId,
                        items: queueItems,
                    });

                    if (response.data && response.data.success) {
                        const processedIds = response.data.processed_ids || [];
                        const remaining = queueItems.filter(item => !processedIds.includes(item.id));
                        
                        localStorage.setItem('pos_offline_sync_queue', JSON.stringify(remaining));
                        this.pendingCount = remaining.length;
                        this.lastSyncedAt = response.data.synced_at;
                    }
                }

                // Perform pull sync to get updated master records
                const lastPull = localStorage.getItem('pos_last_pull_timestamp');
                const pullUrl = lastPull ? `/api/sync/pull?since=${encodeURIComponent(lastPull)}` : '/api/sync/pull';
                const pullRes = await axios.get(pullUrl);

                if (pullRes.data && pullRes.data.server_timestamp) {
                    localStorage.setItem('pos_last_pull_timestamp', pullRes.data.server_timestamp);
                    this.lastSyncedAt = pullRes.data.server_timestamp;
                }
            } catch (error) {
                console.error('[Sync Engine Error]', error);
            } finally {
                this.isSyncing = false;
            }
        },

        queueOfflineAction(entityType, action, payload) {
            const queueJson = localStorage.getItem('pos_offline_sync_queue');
            const queue = queueJson ? JSON.parse(queueJson) : [];

            const queueItem = {
                id: crypto.randomUUID(),
                entity_type: entityType,
                action: action,
                payload: payload,
                created_at: new Date().toISOString(),
            };

            queue.push(queueItem);
            localStorage.setItem('pos_offline_sync_queue', JSON.stringify(queue));
            this.pendingCount = queue.length;

            // Attempt immediate sync if online
            if (this.isOnline) {
                this.triggerSync();
            }
        }
    }
});
