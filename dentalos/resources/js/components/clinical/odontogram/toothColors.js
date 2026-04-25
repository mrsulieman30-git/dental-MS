/**
 * Color mappings for dental chart materials, conditions, and treatment statuses.
 */

// Material fill colors
export const materialColors = {
  amalgam:    { fill: '#9ca3af', stroke: '#6b7280', label: 'Amalgam' },
  composite:  { fill: '#93c5fd', stroke: '#3b82f6', label: 'Composite' },
  gold:       { fill: '#fbbf24', stroke: '#d97706', label: 'Gold' },
  porcelain:  { fill: '#ffffff', stroke: '#3b82f6', label: 'Porcelain/Ceramic' },
  zirconia:   { fill: '#f0f0f0', stroke: '#a78bfa', label: 'Zirconia' },
  pfm:        { fill: '#e5e7eb', stroke: '#6366f1', label: 'PFM' },
  acrylic:    { fill: '#fbcfe8', stroke: '#ec4899', label: 'Acrylic' },
  sealant:    { fill: '#a7f3d0', stroke: '#10b981', label: 'Sealant' },
  other:      { fill: '#d1d5db', stroke: '#9ca3af', label: 'Other' },
};

// Condition indicator styles
export const conditionColors = {
  caries:       { fill: '#991b1b', stroke: '#7f1d1d', label: 'Caries' },
  fracture:     { fill: 'none', stroke: '#dc2626', strokeDasharray: '3,2', label: 'Fracture' },
  wear:         { fill: '#fef3c7', stroke: '#f59e0b', label: 'Wear' },
  sensitivity:  { fill: '#dbeafe', stroke: '#60a5fa', label: 'Sensitivity' },
  watch:        { fill: 'none', stroke: '#eab308', strokeDasharray: '4,3', label: 'Watch' },
  mobility:     { fill: 'none', stroke: '#f97316', label: 'Mobility' },
  perio:        { fill: '#fecaca', stroke: '#ef4444', label: 'Perio' },
  other:        { fill: '#e5e7eb', stroke: '#9ca3af', label: 'Other' },
};

// Treatment status outline styles
export const statusStyles = {
  existing:   { stroke: '#374151', strokeWidth: 1.5, dasharray: null, label: 'Existing' },
  proposed:   { stroke: '#3b82f6', strokeWidth: 2, dasharray: null, label: 'Proposed' },
  accepted:   { stroke: '#22c55e', strokeWidth: 2, dasharray: null, label: 'Accepted' },
  in_progress:{ stroke: '#f59e0b', strokeWidth: 2, dasharray: '5,3', label: 'In Progress' },
  completed:  { stroke: '#6b7280', strokeWidth: 1.5, dasharray: null, label: 'Completed' },
  declined:   { stroke: '#ef4444', strokeWidth: 1, dasharray: '2,2', label: 'Declined' },
  new:        { stroke: '#8b5cf6', strokeWidth: 2, dasharray: null, label: 'New' },
  needs_replacement: { stroke: '#f97316', strokeWidth: 2, dasharray: '4,2', label: 'Needs Replacement' },
};

// Special tooth states
export const specialStates = {
  missing:    { overlay: 'x', color: '#ef4444', label: 'Missing' },
  unerupted:  { strokeDasharray: '3,3', color: '#9ca3af', label: 'Unerupted' },
  implant:    { icon: true, color: '#6366f1', label: 'Implant' },
  rct:        { dot: true, color: '#374151', label: 'RCT' },
};

// Default surface color
export const defaultSurfaceColor = { fill: '#ffffff', stroke: '#d1d5db' };

/**
 * Get fill style for a surface based on restoration data
 */
export function getRestorationFill(restoration) {
  if (!restoration) return null;
  const mat = restoration.material || 'other';
  return materialColors[mat] || materialColors.other;
}

/**
 * Get condition fill for a surface
 */
export function getConditionFill(condition) {
  if (!condition) return null;
  return conditionColors[condition.condition_type] || conditionColors.other;
}

/**
 * Get outline style for a tooth or restoration based on status
 */
export function getStatusOutline(status) {
  return statusStyles[status] || statusStyles.existing;
}

/**
 * Compute surface fill color given arrays of conditions and restorations for a specific tooth and surface.
 * Restorations take visual priority over conditions.
 */
export function computeSurfaceFill(toothNum, surface, conditions = [], restorations = []) {
  // Check restorations first (they visually overlay conditions)
  const restoration = restorations.find(r =>
    r.tooth_number === toothNum && r.surfaces && r.surfaces.includes(surface)
  );
  if (restoration) return getRestorationFill(restoration);

  // Then conditions
  const condition = conditions.find(c =>
    c.tooth_number === toothNum && c.surfaces && c.surfaces.includes(surface)
  );
  if (condition) return getConditionFill(condition);

  return defaultSurfaceColor;
}

/**
 * Check special state for a tooth
 */
export function getToothSpecialState(toothNum, conditions = [], restorations = []) {
  const states = [];
  // Missing tooth
  const missing = conditions.find(c => c.tooth_number === toothNum && c.condition_type === 'missing');
  if (missing) states.push('missing');
  // Unerupted
  const unerupted = conditions.find(c => c.tooth_number === toothNum && c.condition_type === 'unerupted');
  if (unerupted) states.push('unerupted');
  // RCT
  const rct = restorations.find(r => r.tooth_number === toothNum && r.restoration_type === 'rct');
  if (rct) states.push('rct');
  // Implant
  const implant = restorations.find(r => r.tooth_number === toothNum && r.restoration_type === 'implant');
  if (implant) states.push('implant');
  return states;
}

/**
 * Get tooth outline status (overall status for the tooth)
 */
export function getToothOutlineStatus(toothNum, conditions = [], restorations = []) {
  // Priority: proposed > accepted > in_progress > new > existing
  const all = [...conditions.filter(c=>c.tooth_number===toothNum), ...restorations.filter(r=>r.tooth_number===toothNum)];
  const priority = ['proposed','accepted','in_progress','new','needs_replacement','declined','completed','existing'];
  for (const s of priority) {
    if (all.some(item => item.status === s)) return s;
  }
  return 'existing';
}

/**
 * Get bridge data for rendering arcs
 */
export function getBridgeGroups(restorations = []) {
  return restorations.filter(r => r.restoration_type === 'bridge' && r.bridge_teeth);
}

/**
 * Get mobility value for a tooth from conditions
 */
export function getToothMobility(toothNum, conditions = []) {
  const mob = conditions.find(c => c.tooth_number === toothNum && c.condition_type === 'mobility');
  return mob ? mob.severity : null;
}

// Legend data for rendering the chart legend
export const legendItems = [
  { type: 'material', key: 'amalgam', ...materialColors.amalgam },
  { type: 'material', key: 'composite', ...materialColors.composite },
  { type: 'material', key: 'gold', ...materialColors.gold },
  { type: 'material', key: 'porcelain', ...materialColors.porcelain },
  { type: 'condition', key: 'caries', ...conditionColors.caries },
  { type: 'condition', key: 'watch', fill:'#fef9c3', stroke:'#eab308', label:'Watch' },
  { type: 'special', key: 'missing', fill:'none', stroke:'#ef4444', label:'Missing' },
  { type: 'special', key: 'rct', fill:'#374151', stroke:'#374151', label:'RCT' },
  { type: 'special', key: 'implant', fill:'#6366f1', stroke:'#6366f1', label:'Implant' },
  { type: 'status', key: 'proposed', fill:'none', stroke:'#3b82f6', label:'Proposed' },
  { type: 'status', key: 'accepted', fill:'none', stroke:'#22c55e', label:'Accepted' },
  { type: 'status', key: 'completed', fill:'none', stroke:'#6b7280', label:'Completed' },
];
