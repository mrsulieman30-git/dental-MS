/**
 * Tooth SVG Path Definitions & Position Data for Odontogram
 */

const TOOTH_TYPES = {
  INCISOR_CENTRAL: 'ic', INCISOR_LATERAL: 'il',
  CANINE: 'c', PREMOLAR: 'pm', MOLAR: 'm', MOLAR_THIRD: 'm3',
};

export const toothTypeMap = {
  1:'m3',2:'m',3:'m',4:'pm',5:'pm',6:'c',7:'il',8:'ic',
  9:'ic',10:'il',11:'c',12:'pm',13:'pm',14:'m',15:'m',16:'m3',
  17:'m3',18:'m',19:'m',20:'pm',21:'pm',22:'c',23:'il',24:'ic',
  25:'ic',26:'il',27:'c',28:'pm',29:'pm',30:'m',31:'m',32:'m3',
};

const DIMS = { m:{w:44,h:44}, m3:{w:40,h:40}, pm:{w:36,h:36}, c:{w:32,h:38}, ic:{w:30,h:36}, il:{w:28,h:34} };
export function getToothDimensions(n) { return DIMS[toothTypeMap[n]] || DIMS.pm; }

export function getSurfacePolygons(w, h) {
  const i = Math.min(w,h)*0.25;
  return {
    B:[[0,0],[w,0],[w-i,i],[i,i]],
    L:[[i,h-i],[w-i,h-i],[w,h],[0,h]],
    M:[[0,0],[i,i],[i,h-i],[0,h]],
    D:[[w-i,i],[w,0],[w,h],[w-i,h-i]],
    O:[[i,i],[w-i,i],[w-i,h-i],[i,h-i]],
  };
}

export function polygonToSvgPoints(p) { return p.map(([x,y])=>`${x},${y}`).join(' '); }

export function getRootPath(n, w, h) {
  const type = toothTypeMap[n], isUpper = n<=16, rH = h*0.6;
  if (isUpper) {
    if(type==='m'||type==='m3') return `M${w*.15},0 L${w*.2},-${rH} L${w*.25},0 M${w*.4},0 L${w*.5},-${rH*.9} L${w*.6},0 M${w*.75},0 L${w*.8},-${rH} L${w*.85},0`;
    if(type==='pm') return `M${w*.2},0 L${w*.3},-${rH*.85} L${w*.4},0 M${w*.6},0 L${w*.7},-${rH*.85} L${w*.8},0`;
    return `M${w*.3},0 L${w*.5},-${rH} L${w*.7},0`;
  } else {
    if(type==='m'||type==='m3') return `M${w*.15},${h} L${w*.2},${h+rH} L${w*.25},${h} M${w*.4},${h} L${w*.5},${h+rH*.9} L${w*.6},${h} M${w*.75},${h} L${w*.8},${h+rH} L${w*.85},${h}`;
    if(type==='pm') return `M${w*.2},${h} L${w*.3},${h+rH*.85} L${w*.4},${h} M${w*.6},${h} L${w*.7},${h+rH*.85} L${w*.8},${h}`;
    return `M${w*.3},${h} L${w*.5},${h+rH} L${w*.7},${h}`;
  }
}

export function calculateToothPositions() {
  const pos = {};
  let x = 30;
  for(let t=1;t<=16;t++){const d=getToothDimensions(t);pos[t]={x,y:90,w:d.w,h:d.h};x+=d.w+4;}
  x = 30;
  for(let t=32;t>=17;t--){const d=getToothDimensions(t);pos[t]={x,y:220,w:d.w,h:d.h};x+=d.w+4;}
  return pos;
}

// Notation mappings
export const universalToFdi = {1:18,2:17,3:16,4:15,5:14,6:13,7:12,8:11,9:21,10:22,11:23,12:24,13:25,14:26,15:27,16:28,17:38,18:37,19:36,20:35,21:34,22:33,23:32,24:31,25:41,26:42,27:43,28:44,29:45,30:46,31:47,32:48};
export const universalToPalmer = {1:'8┘',2:'7┘',3:'6┘',4:'5┘',5:'4┘',6:'3┘',7:'2┘',8:'1┘',9:'└1',10:'└2',11:'└3',12:'└4',13:'└5',14:'└6',15:'└7',16:'└8',17:'┌8',18:'┌7',19:'┌6',20:'┌5',21:'┌4',22:'┌3',23:'┌2',24:'┌1',25:'1┐',26:'2┐',27:'3┐',28:'4┐',29:'5┐',30:'6┐',31:'7┐',32:'8┐'};

export function getToothLabel(n, notation='universal') {
  if(notation==='fdi') return String(universalToFdi[n]||n);
  if(notation==='palmer') return universalToPalmer[n]||String(n);
  return String(n);
}

// Primary teeth
export const primaryToPermPosition = {A:4,B:5,C:6,D:7,E:8,F:9,G:10,H:11,I:12,J:13,K:20,L:21,M:22,N:23,O:24,P:25,Q:26,R:27,S:28,T:29};
export const primaryToFdi = {A:55,B:54,C:53,D:52,E:51,F:61,G:62,H:63,I:64,J:65,K:75,L:74,M:73,N:72,O:71,P:81,Q:82,R:83,S:84,T:85};
export function getPrimaryToothLabel(letter, notation='universal') {
  if(notation==='fdi') return String(primaryToFdi[letter]||letter);
  return letter;
}

export const multiRootTeeth = [1,2,3,14,15,16,17,18,19,30,31,32];
export function isMultiRoot(n) { return multiRootTeeth.includes(n); }
export function getOcclusalLabel(n) { const t=toothTypeMap[n]; return (t==='ic'||t==='il'||t==='c')?'I':'O'; }
export function getFacialLabel(n) { const t=toothTypeMap[n]; return (t==='ic'||t==='il'||t==='c')?'F':'B'; }
export function isMesialOnRight(n) { return (n>=1&&n<=8)||(n>=25&&n<=32); }
export function getAllPermanentTeeth() { return Array.from({length:32},(_,i)=>i+1); }
export function getUpperTeeth() { return Array.from({length:16},(_,i)=>i+1); }
export function getLowerTeeth() { return Array.from({length:16},(_,i)=>i+17); }
export const ODONTOGRAM_VIEWBOX = { width:600, height:340 };
export const ODONTOGRAM_COMPACT_VIEWBOX = { width:600, height:300 };
