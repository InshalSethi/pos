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
   * Play high-pitch pleasant chime for successful barcode match / scan (Disabled)
   */
  playSuccess() {
    // Audio feedback disabled per user configuration
    return;
  }

  /**
   * Play warning tone for "Not Found" or "Out of Stock" events (Disabled)
   */
  playWarning() {
    // Audio warning feedback disabled per user configuration
    return;
  }
}

export default new SoundService();
