<template>
  <div class="scw">
    <Transition name="scw-panel">
      <div
        v-if="open"
        id="scw-panel"
        class="scw__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="scw-title"
      >
        <div class="scw__head">
          <h2 id="scw-title" class="scw__title">Support čats</h2>
          <button type="button" class="scw__close" aria-label="Aizvērt" @click="closePanel">×</button>
        </div>
        <div ref="scrollEl" class="scw__messages">
          <template v-if="loading">
            <p class="scw__empty">Ielādē…</p>
          </template>
          <template v-else>
            <p v-if="!messages.length" class="scw__empty">
              Uzdodiet jautājumu — administrators atbildēs šeit.
            </p>
            <div
              v-for="m in messages"
              :key="m.id"
              :class="['scw__bubble', m.isFromAdmin ? 'scw__bubble--admin' : 'scw__bubble--me']"
            >
              <p class="scw__body">{{ m.body }}</p>
              <time class="scw__time" :datetime="m.createdAt">{{ formatTime(m.createdAt) }}</time>
            </div>
          </template>
        </div>
        <form class="scw__form" @submit.prevent="send">
          <textarea
            v-model="draft"
            rows="3"
            class="scw__input"
            placeholder="Jūsu ziņa…"
            maxlength="5000"
          />
          <button type="submit" class="scw__send" :disabled="sending || !draft.trim()">
            Sūtīt
          </button>
        </form>
        <p v-if="error" class="scw__err">{{ error }}</p>
      </div>
    </Transition>

    <button
      type="button"
      class="scw__fab"
      :aria-expanded="open"
      aria-controls="scw-panel"
      @click="toggle"
    >
      <span class="scw__fab-label">Support</span>
      <svg class="scw__fab-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path
          d="M12 3C7.03 3 3 6.58 3 11c0 2.39 1.08 4.53 2.78 6.12L4 21l4.26-1.47C9.5 20.2 10.73 20.5 12 20.5c4.97 0 9-3.58 9-8s-4.03-8-9-8z"
          stroke="currentColor"
          stroke-width="1.75"
          stroke-linejoin="round"
        />
        <circle cx="9" cy="11" r="1.1" fill="currentColor" />
        <circle cx="12" cy="11" r="1.1" fill="currentColor" />
        <circle cx="15" cy="11" r="1.1" fill="currentColor" />
      </svg>
      <span v-if="unread && !open" class="scw__badge" aria-live="polite">{{ unread > 9 ? '9+' : unread }}</span>
    </button>
  </div>
</template>

<script>
import { firstValidationMessage } from '@/api/http'
import {
  fetchMySupportChat,
  fetchMySupportChatUnread,
  postMySupportChat,
} from '@/api/restApi'

export default {
  name: 'SupportChatWidget',
  data() {
    return {
      open: false,
      messages: [],
      draft: '',
      sending: false,
      loading: false,
      error: '',
      unread: 0,
      pollUnreadId: null,
      pollMessagesId: null,
    }
  },
  mounted() {
    void this.refreshUnread()
    this.pollUnreadId = window.setInterval(() => {
      if (!this.open) void this.refreshUnread()
    }, 45000)
  },
  beforeUnmount() {
    if (this.pollUnreadId) window.clearInterval(this.pollUnreadId)
    this.stopMessagePoll()
  },
  watch: {
    open(val) {
      if (val) {
        void this.loadMessages()
        this.startMessagePoll()
      } else {
        this.stopMessagePoll()
        void this.refreshUnread()
      }
    },
  },
  methods: {
    toggle() {
      this.open = !this.open
      this.error = ''
    },
    closePanel() {
      this.open = false
    },
    startMessagePoll() {
      this.stopMessagePoll()
      this.pollMessagesId = window.setInterval(() => {
        if (this.open) void this.loadMessages({ silent: true })
      }, 12000)
    },
    stopMessagePoll() {
      if (this.pollMessagesId) {
        window.clearInterval(this.pollMessagesId)
        this.pollMessagesId = null
      }
    },
    async refreshUnread() {
      try {
        this.unread = await fetchMySupportChatUnread()
      } catch {
        this.unread = 0
      }
    },
    async loadMessages(opts = {}) {
      if (!opts.silent) this.loading = true
      this.error = ''
      try {
        this.messages = await fetchMySupportChat()
        this.$nextTick(() => this.scrollToBottom())
        if (!opts.silent) this.unread = 0
        else await this.refreshUnread()
      } catch (e) {
        if (!opts.silent) this.error = firstValidationMessage(e)
      } finally {
        if (!opts.silent) this.loading = false
      }
    },
    scrollToBottom() {
      const el = this.$refs.scrollEl
      if (el) el.scrollTop = el.scrollHeight
    },
    formatTime(iso) {
      if (!iso) return ''
      return new Date(iso).toLocaleString('lv-LV', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      })
    },
    async send() {
      const text = this.draft.trim()
      if (!text || this.sending) return
      this.sending = true
      this.error = ''
      try {
        await postMySupportChat({ body: text })
        this.draft = ''
        await this.loadMessages({ silent: true })
      } catch (e) {
        this.error = firstValidationMessage(e)
      } finally {
        this.sending = false
      }
    },
  },
}
</script>

<style scoped>
.scw {
  position: fixed;
  right: 1.25rem;
  bottom: 1.25rem;
  z-index: 9990;
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
}

.scw__fab {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1rem 0.65rem 0.85rem;
  border-radius: 999px;
  border: 1.5px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  background: linear-gradient(
    180deg,
    var(--hp-orange, #ff5722) 0%,
    var(--hp-orange-deep, #e64a19) 100%
  );
  color: #fff;
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  box-shadow: 0 10px 32px rgba(255, 87, 34, 0.38);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.scw__fab:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 40px rgba(255, 87, 34, 0.45);
}

.scw__fab-icon {
  flex-shrink: 0;
}

.scw__fab-label {
  letter-spacing: 0.02em;
}

.scw__badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 1.35rem;
  height: 1.35rem;
  padding: 0 5px;
  border-radius: 999px;
  background: #dc2626;
  color: #fff;
  font-size: 0.68rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--hp-bg, #0a0a0c);
}

.scw__panel {
  position: absolute;
  right: 0;
  bottom: calc(100% + 12px);
  width: min(100vw - 2.5rem, 380px);
  max-height: min(72vh, 520px);
  display: flex;
  flex-direction: column;
  background: var(--hp-bg-mid, #0e1118);
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  border-radius: 20px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.55);
  overflow: hidden;
}

.scw-panel-enter-active,
.scw-panel-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.scw-panel-enter-from,
.scw-panel-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

.scw__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  background: var(--hp-bg-elevated, #141820);
}

.scw__title {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
  color: var(--hp-gold, #f5cc4c);
}

.scw__close {
  width: 2rem;
  height: 2rem;
  border: none;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.08);
  color: var(--hp-text, #f4f4f5);
  font-size: 1.35rem;
  line-height: 1;
  cursor: pointer;
}

.scw__close:hover {
  background: rgba(255, 255, 255, 0.14);
}

.scw__messages {
  flex: 1;
  min-height: 180px;
  max-height: 320px;
  overflow-y: auto;
  padding: 0.75rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.scw__empty {
  margin: 1rem 0;
  font-size: 0.88rem;
  color: var(--hp-muted, rgba(255, 255, 255, 0.7));
  line-height: 1.45;
}

.scw__bubble {
  max-width: 92%;
  padding: 0.55rem 0.75rem;
  border-radius: 14px;
  font-size: 0.88rem;
  line-height: 1.45;
}

.scw__bubble--me {
  align-self: flex-end;
  background: rgba(255, 87, 34, 0.22);
  border: 1px solid rgba(255, 87, 34, 0.35);
  color: var(--hp-text, #f4f4f5);
}

.scw__bubble--admin {
  align-self: flex-start;
  background: var(--hp-surface, rgba(255, 255, 255, 0.055));
  border: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  color: var(--hp-text, #f4f4f5);
}

.scw__body {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-word;
}

.scw__time {
  display: block;
  margin-top: 0.35rem;
  font-size: 0.68rem;
  color: var(--hp-muted, rgba(255, 255, 255, 0.55));
}

.scw__form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0.75rem 1rem 1rem;
  border-top: 1px solid var(--hp-line, rgba(255, 255, 255, 0.1));
  background: rgba(0, 0, 0, 0.2);
}

.scw__input {
  width: 100%;
  box-sizing: border-box;
  padding: 0.6rem 0.75rem;
  border-radius: 12px;
  border: 1.5px solid var(--hp-line-strong, rgba(255, 255, 255, 0.18));
  background: rgba(0, 0, 0, 0.25);
  color: var(--hp-text, #f4f4f5);
  font-family: inherit;
  font-size: 0.9rem;
  resize: vertical;
  min-height: 3.5rem;
}

.scw__input:focus {
  outline: none;
  border-color: var(--hp-gold, #f5cc4c);
}

.scw__send {
  align-self: flex-end;
  padding: 0.45rem 1.1rem;
  border-radius: 999px;
  border: none;
  background: linear-gradient(
    180deg,
    var(--hp-orange, #ff5722) 0%,
    var(--hp-orange-deep, #e64a19) 100%
  );
  color: #fff;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
}

.scw__send:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.scw__err {
  margin: 0 1rem 0.75rem;
  font-size: 0.82rem;
  color: #fca5a5;
}
</style>
