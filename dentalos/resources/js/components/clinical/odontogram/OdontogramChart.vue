<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import {
  toothTypeMap, getToothDimensions, getSurfacePolygons, polygonToSvgPoints,
  getRootPath, calculateToothPositions, getToothLabel, getOcclusalLabel,
  getFacialLabel, isMesialOnRight, isMultiRoot, getAllPermanentTeeth,
  ODONTOGRAM_VIEWBOX, ODONTOGRAM_COMPACT_VIEWBOX,
} from './toothPaths.js';
import {
  computeSurfaceFill, getToothSpecialState, getToothOutlineStatus,
  getStatusOutline, getBridgeGroups, getToothMobility, defaultSurfaceColor,
} from './toothColors.js';

const props = defineProps({
  modelValue: { type: Object, default: () => ({ conditions: [], restorations: [], implants: [] }) },
  showPrimary: { type: Boolean, default: false },
  mode: { type: String, default: 'view', validator: v => ['view', 'chart'].includes(v) },
  readonly: { type: Boolean, default: true },
  notation: { type: String, default: 'universal' },
  selectedTooth: { type: Number, default: null },
  compact: { type: Boolean, default: false },
});

const emit = defineEmits(['tooth-selected', 'surface-selected', 'update:modelValue', 'update:selectedTooth']);

const hoveredTooth = ref(null);
const hoveredSurface = ref(null);
const positions = computed(() => calculateToothPositions());
const teeth = computed(() => getAllPermanentTeeth());
const viewBox = computed(() => props.compact ? ODONTOGRAM_COMPACT_VIEWBOX : ODONTOGRAM_VIEWBOX);
const isEditable = computed(() => props.mode === 'chart' && !props.readonly);

// Compute surface fills per tooth — only recomputes when data changes
const surfaceFills = computed(() => {
  const fills = {};
  const { conditions = [], restorations = [] } = props.modelValue;
  teeth.value.forEach(t => {
    const dims = getToothDimensions(t);
    const surfs = getSurfacePolygons(dims.w, dims.h);
    fills[t] = {};
    Object.keys(surfs).forEach(s => {
      fills[t][s] = computeSurfaceFill(t, s, conditions, restorations);
    });
  });
  return fills;
});

// Compute special states per tooth
const toothStates = computed(() => {
  const states = {};
  const { conditions = [], restorations = [] } = props.modelValue;
  teeth.value.forEach(t => {
    states[t] = getToothSpecialState(t, conditions, restorations);
  });
  return states;
});

// Compute outline statuses per tooth
const toothOutlines = computed(() => {
  const outlines = {};
  const { conditions = [], restorations = [] } = props.modelValue;
  teeth.value.forEach(t => {
    const status = getToothOutlineStatus(t, conditions, restorations);
    outlines[t] = getStatusOutline(status);
  });
  return outlines;
});

// Compute mobility per tooth
const toothMobility = computed(() => {
  const mob = {};
  const { conditions = [] } = props.modelValue;
  teeth.value.forEach(t => {
    mob[t] = getToothMobility(t, conditions);
  });
  return mob;
});

// Bridge arcs
const bridges = computed(() => getBridgeGroups(props.modelValue.restorations || []));

function getSurfaces(toothNum) {
  const dims = getToothDimensions(toothNum);
  const polys = getSurfacePolygons(dims.w, dims.h);
  // Remap M/D based on mesial direction
  if (!isMesialOnRight(toothNum)) {
    const temp = polys.M;
    polys.M = polys.D;
    polys.D = temp;
  }
  return polys;
}

function getSurfaceLabel(toothNum, surfaceKey) {
  if (surfaceKey === 'O') return getOcclusalLabel(toothNum);
  if (surfaceKey === 'B') return getFacialLabel(toothNum);
  return surfaceKey;
}

function onToothClick(toothNum) {
  if (!isEditable.value && props.mode !== 'chart') return;
  emit('tooth-selected', toothNum);
  emit('update:selectedTooth', toothNum);
}

function onSurfaceClick(toothNum, surface, event) {
  event.stopPropagation();
  if (!isEditable.value && props.mode !== 'chart') return;
  emit('surface-selected', { tooth: toothNum, surface });
  emit('tooth-selected', toothNum);
  emit('update:selectedTooth', toothNum);
}

function onToothEnter(toothNum) {
  if (isEditable.value) hoveredTooth.value = toothNum;
}
function onToothLeave() {
  hoveredTooth.value = null;
  hoveredSurface.value = null;
}
function onSurfaceEnter(toothNum, surface) {
  if (isEditable.value) {
    hoveredTooth.value = toothNum;
    hoveredSurface.value = `${toothNum}_${surface}`;
  }
}

function isSelected(toothNum) { return props.selectedTooth === toothNum; }
function isHovered(toothNum) { return hoveredTooth.value === toothNum; }
function isSurfaceHovered(toothNum, surface) { return hoveredSurface.value === `${toothNum}_${surface}`; }

function getBridgeArcPath(bridge) {
  const bridgeTeeth = [...(bridge.bridge_teeth || [])]
    .map(Number)
    .filter((toothNum) => Number.isFinite(toothNum) && positions.value[toothNum]);

  if (bridgeTeeth.length < 2) {
    return '';
  }

  const firstTooth = bridgeTeeth[0];
  const lastTooth = bridgeTeeth[bridgeTeeth.length - 1];
  const firstPosition = positions.value[firstTooth];
  const lastPosition = positions.value[lastTooth];
  const isUpperArch = firstTooth <= 16;

  const startX = firstPosition.x + (firstPosition.w / 2);
  const endX = lastPosition.x + (lastPosition.w / 2);
  const baselineY = isUpperArch
    ? Math.min(firstPosition.y, lastPosition.y) - 12
    : Math.max(firstPosition.y + firstPosition.h, lastPosition.y + lastPosition.h) + 12;
  const controlOffset = isUpperArch ? -18 : 18;

  return `M${startX},${baselineY} C${startX},${baselineY + controlOffset} ${endX},${baselineY + controlOffset} ${endX},${baselineY}`;
}
</script>

<template>
  <div class="odontogram-wrapper" :class="{ compact, editable: isEditable }">
    <svg
      :viewBox="`0 0 ${viewBox.width} ${viewBox.height}`"
      xmlns="http://www.w3.org/2000/svg"
      class="odontogram-svg"
      preserveAspectRatio="xMidYMid meet"
    >
      <defs>
        <!-- Patterns for special fills -->
        <pattern id="pattern-missing" width="8" height="8" patternUnits="userSpaceOnUse">
          <line x1="0" y1="0" x2="8" y2="8" stroke="#ef4444" stroke-width="1.5"/>
          <line x1="8" y1="0" x2="0" y2="8" stroke="#ef4444" stroke-width="1.5"/>
        </pattern>
        <pattern id="pattern-unerupted" width="4" height="4" patternUnits="userSpaceOnUse">
          <circle cx="2" cy="2" r="0.5" fill="#9ca3af"/>
        </pattern>
      </defs>

      <!-- Arch labels -->
      <text x="10" y="82" class="arch-label">Upper</text>
      <text x="10" y="215" class="arch-label">Lower</text>

      <!-- Midline -->
      <line
        :x1="viewBox.width / 2" y1="70" :x2="viewBox.width / 2" y2="290"
        stroke="#e2e8f0" stroke-width="1" stroke-dasharray="4,4"
      />

      <!-- Teeth -->
      <g v-for="t in teeth" :key="t" :data-tooth="t">
        <g
          :transform="`translate(${positions[t].x}, ${positions[t].y})`"
          :class="['tooth-group', { selected: isSelected(t), hovered: isHovered(t) }]"
          @pointerdown="onToothClick(t)"
          @pointerenter="onToothEnter(t)"
          @pointerleave="onToothLeave()"
          :style="{ cursor: isEditable ? 'pointer' : 'default' }"
        >
          <!-- Root -->
          <path
            :d="getRootPath(t, positions[t].w, positions[t].h)"
            fill="none" stroke="#94a3b8" stroke-width="1"
          />

          <!-- Tooth outline -->
          <rect
            x="0" y="0" :width="positions[t].w" :height="positions[t].h"
            rx="3" ry="3"
            :fill="toothStates[t]?.includes('missing') ? 'url(#pattern-missing)' : '#fff'"
            :stroke="toothOutlines[t]?.stroke || '#d1d5db'"
            :stroke-width="toothOutlines[t]?.strokeWidth || 1.5"
            :stroke-dasharray="toothStates[t]?.includes('unerupted') ? '3,3' : (toothOutlines[t]?.dasharray || null)"
          />

          <!-- Surface polygons -->
          <polygon
            v-for="(poly, sKey) in getSurfaces(t)" :key="sKey"
            :points="polygonToSvgPoints(poly)"
            :fill="surfaceFills[t]?.[sKey]?.fill || defaultSurfaceColor.fill"
            :stroke="surfaceFills[t]?.[sKey]?.stroke || '#d1d5db'"
            stroke-width="0.75"
            :class="['surface', { 'surface-hover': isSurfaceHovered(t, sKey) }]"
            @pointerdown="onSurfaceClick(t, sKey, $event)"
            @pointerenter="onSurfaceEnter(t, sKey)"
          />

          <!-- Missing X overlay -->
          <g v-if="toothStates[t]?.includes('missing')">
            <line x1="2" y1="2" :x2="positions[t].w - 2" :y2="positions[t].h - 2" stroke="#ef4444" stroke-width="2"/>
            <line :x1="positions[t].w - 2" y1="2" x2="2" :y2="positions[t].h - 2" stroke="#ef4444" stroke-width="2"/>
          </g>

          <!-- RCT dot -->
          <circle
            v-if="toothStates[t]?.includes('rct')"
            :cx="positions[t].w / 2" :cy="positions[t].h / 2"
            r="3" fill="#374151"
          />

          <!-- Implant icon -->
          <g v-if="toothStates[t]?.includes('implant')">
            <line
              :x1="positions[t].w / 2" :y1="positions[t].h * 0.3"
              :x2="positions[t].w / 2" :y2="positions[t].h * 0.7"
              stroke="#6366f1" stroke-width="2.5" stroke-linecap="round"
            />
            <line
              :x1="positions[t].w * 0.3" :y1="positions[t].h / 2"
              :x2="positions[t].w * 0.7" :y2="positions[t].h / 2"
              stroke="#6366f1" stroke-width="2.5" stroke-linecap="round"
            />
          </g>

          <!-- Selection highlight ring -->
          <rect
            v-if="isSelected(t)"
            x="-2" y="-2" :width="positions[t].w + 4" :height="positions[t].h + 4"
            rx="5" ry="5" fill="none" stroke="#6366f1" stroke-width="2"
            class="selection-ring"
          />

          <!-- Tooth number label -->
          <text
            :x="positions[t].w / 2"
            :y="t <= 16 ? -8 : positions[t].h + 14"
            class="tooth-number"
            text-anchor="middle"
          >{{ getToothLabel(t, notation) }}</text>

          <!-- Mobility number -->
          <text
            v-if="toothMobility[t]"
            :x="positions[t].w / 2"
            :y="t <= 16 ? positions[t].h + 14 : -8"
            class="mobility-label"
            text-anchor="middle"
          >M{{ toothMobility[t] }}</text>
        </g>
      </g>

      <!-- Bridge arcs -->
      <g v-for="(bridge, idx) in bridges" :key="'bridge-' + idx">
        <path
          v-if="bridge.bridge_teeth?.length >= 2"
          :d="getBridgeArcPath(bridge)"
          fill="none" stroke="#8b5cf6" stroke-width="2"
          stroke-dasharray="4,2"
        />
      </g>
    </svg>
  </div>
</template>

<style scoped>
.odontogram-wrapper {
  width: 100%;
  max-width: 100%;
  overflow: hidden;
}
.odontogram-svg {
  width: 100%;
  height: auto;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}
.arch-label {
  font-size: 9px;
  fill: #94a3b8;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.tooth-number {
  font-size: 8px;
  fill: #475569;
  font-weight: 700;
  pointer-events: none;
}
.mobility-label {
  font-size: 7px;
  fill: #f97316;
  font-weight: 700;
  pointer-events: none;
}
.tooth-group {
  transition: filter 0.1s ease;
}
.tooth-group.hovered {
  filter: brightness(0.95);
}
.tooth-group.selected {
  filter: drop-shadow(0 0 3px rgba(99, 102, 241, 0.5));
}
.surface {
  transition: opacity 0.08s ease;
}
.surface-hover {
  opacity: 0.75;
  cursor: pointer;
}
.selection-ring {
  animation: pulse-ring 1.5s ease-in-out infinite;
}
@keyframes pulse-ring {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
