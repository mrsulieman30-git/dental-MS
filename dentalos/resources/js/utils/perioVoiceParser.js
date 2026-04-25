/**
 * Voice input parser for periodontal charting.
 * Parses spoken input like: "14 buccal 3 3 4 5 3 4 bleeding 2"
 * Maps to the correct tooth, surface, and probe/recession positions.
 */

const TOOTH_WORDS = {};
for (let i = 1; i <= 32; i++) TOOTH_WORDS[String(i)] = i;
// Common speech-to-text variants
const NUMBER_WORDS = { one:1, two:2, three:3, four:4, five:5, six:6, seven:7, eight:8, nine:9, ten:10, eleven:11, twelve:12 };

const SURFACE_WORDS = {
  buccal:'buccal', buckle:'buccal', buckol:'buccal',
  lingual:'lingual', linkle:'lingual', linguall:'lingual',
  facial:'buccal', labial:'buccal',
};

const KEYWORDS = {
  bleeding: 'bleeding',
  bleed: 'bleeding',
  suppuration: 'suppuration',
  pus: 'suppuration',
  recession: 'recession',
  furcation: 'furcation',
  fork: 'furcation',
  mobility: 'mobility',
  mobile: 'mobility',
  plaque: 'plaque',
  calculus: 'calculus',
};

/**
 * Parse a voice transcript into perio measurement data.
 * @param {string} transcript - The raw speech-to-text output
 * @returns {object} Parsed data: { toothNumber, surface, probes: [n,n,n], bleeding: [pos], recession: [n,n,n], ... }
 */
export function parsePerioVoiceInput(transcript) {
  const words = transcript.toLowerCase().replace(/[.,!?]/g, '').split(/\s+/);
  const result = {
    toothNumber: null,
    surface: null,
    probes: [],
    recession: [],
    bleeding: [],
    suppuration: [],
    furcation: null,
    mobility: null,
    plaque: false,
    calculus: false,
  };

  let mode = 'probe'; // Current parsing mode
  let bleedingPositions = [];

  for (let i = 0; i < words.length; i++) {
    const word = words[i];
    const num = parseNumber(word);

    // Check for tooth number (first number found, or after "tooth"/"number")
    if (result.toothNumber === null && num !== null && num >= 1 && num <= 32) {
      result.toothNumber = num;
      continue;
    }

    // Check for surface
    if (SURFACE_WORDS[word]) {
      result.surface = SURFACE_WORDS[word];
      continue;
    }

    // Check for keywords that change mode
    if (KEYWORDS[word]) {
      mode = KEYWORDS[word];
      if (mode === 'plaque') { result.plaque = true; mode = 'probe'; }
      if (mode === 'calculus') { result.calculus = true; mode = 'probe'; }
      continue;
    }

    // Parse numbers based on current mode
    if (num !== null) {
      switch (mode) {
        case 'probe':
          if (result.probes.length < 6) result.probes.push(num);
          break;
        case 'recession':
          if (result.recession.length < 6) result.recession.push(num);
          break;
        case 'bleeding':
          bleedingPositions.push(num);
          break;
        case 'furcation':
          result.furcation = num;
          mode = 'probe';
          break;
        case 'mobility':
          result.mobility = Math.min(num, 3);
          mode = 'probe';
          break;
      }
    }
  }

  // Convert bleeding positions to boolean array
  result.bleeding = bleedingPositions;

  return result;
}

function parseNumber(word) {
  if (NUMBER_WORDS[word]) return NUMBER_WORDS[word];
  const n = parseInt(word, 10);
  return isNaN(n) ? null : n;
}

/**
 * Check if Web Speech API is available
 */
export function isSpeechRecognitionAvailable() {
  return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
}

/**
 * Create and configure a SpeechRecognition instance
 */
export function createSpeechRecognition(onResult, onEnd, onError) {
  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SR) return null;

  const recognition = new SR();
  recognition.continuous = true;
  recognition.interimResults = true;
  recognition.lang = 'en-US';

  recognition.onresult = (event) => {
    let transcript = '';
    for (let i = event.resultIndex; i < event.results.length; i++) {
      transcript += event.results[i][0].transcript;
    }
    const isFinal = event.results[event.results.length - 1].isFinal;
    onResult(transcript.trim(), isFinal);
  };

  recognition.onend = onEnd;
  recognition.onerror = (event) => onError(event.error);

  return recognition;
}
