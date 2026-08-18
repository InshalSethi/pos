/**
 * POS Retail Audio Feedback Service using Web Audio API
 * Provides instant zero-latency sound chimes for retail scanning workflows.
 */

class SoundService {
  constructor() {
    this.audioCtx = null;
  }

  getAudioContext() {
    if (!this.audioCtx) {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (AudioContextClass) {
        this.audioCtx = new AudioContextClass();
      }
    }
    if (this.audioCtx && this.audioCtx.state === 'suspended') {
      this.audioCtx.resume();
    }
    return this.audioCtx;
  }

  /**
   * Play high-pitch pleasant chime for successful barcode match / scan
   */
  playSuccess() {
    try {
      const ctx = this.getAudioContext();
      if (!ctx) return;

      const now = ctx.currentTime;

      // Dual-tone high pitch chime (1200Hz -> 1800Hz)
      const osc1 = ctx.createOscillator();
      const osc2 = ctx.createOscillator();
      const gain = ctx.createGain();

      osc1.type = 'sine';
      osc2.type = 'sine';

      // Pitch sequence
      osc1.frequency.setValueAtTime(1200, now);
      osc1.frequency.exponentialRampToValueAtTime(1800, now + 0.08);

      osc2.frequency.setValueAtTime(1800, now + 0.04);
      osc2.frequency.exponentialRampToValueAtTime(2400, now + 0.12);

      // Volume envelope (crisp start, smooth decay)
      gain.gain.setValueAtTime(0.25, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

      osc1.connect(gain);
      osc2.connect(gain);
      gain.connect(ctx.destination);

      osc1.start(now);
      osc2.start(now + 0.04);

      osc1.stop(now + 0.15);
      osc2.stop(now + 0.15);
    } catch (e) {
      console.warn('Audio feedback failed:', e);
    }
  }

  /**
   * Play warning tone for "Not Found" or "Out of Stock" events
   */
  playWarning() {
    try {
      const ctx = this.getAudioContext();
      if (!ctx) return;

      const now = ctx.currentTime;

      // Double low-pitch buzz tone (300Hz / 200Hz)
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = 'sawtooth';

      // Low frequency warning sequence
      osc.frequency.setValueAtTime(280, now);
      osc.frequency.setValueAtTime(200, now + 0.08);
      osc.frequency.setValueAtTime(280, now + 0.16);

      // Volume envelope
      gain.gain.setValueAtTime(0.3, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.28);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(now);
      osc.stop(now + 0.28);
    } catch (e) {
      console.warn('Audio warning feedback failed:', e);
    }
  }
}

export default new SoundService();
